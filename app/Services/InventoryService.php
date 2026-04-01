<?php

namespace App\Services;

use App\Models\Product;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class InventoryService
{
    /**
     * Record a stock IN movement (supplier refill, restock).
     *
     * @param int $productId
     * @param int $quantity
     * @param string|null $movementDate (default: now)
     * @param string|null $referenceType (e.g., 'restock', 'manual')
     * @param int|null $referenceId (e.g., restock_id)
     * @param string|null $notes
     * @param int|null $userId
     * @return InventoryMovement
     * @throws \Exception
     */
    public static function stockIn(
        int $productId,
        int $quantity,
        ?string $movementDate = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null
    ): InventoryMovement {
        return static::recordMovement(
            'IN',
            $productId,
            $quantity,
            $movementDate,
            $referenceType,
            $referenceId,
            $notes,
            $userId
        );
    }

    /**
     * Record a stock OUT movement (order delivery, sale).
     *
     * @param int $productId
     * @param int $quantity
     * @param string|null $movementDate (default: now)
     * @param string|null $referenceType (e.g., 'order', 'manual')
     * @param int|null $referenceId (e.g., order_id)
     * @param string|null $notes
     * @param int|null $userId
     * @return InventoryMovement
     * @throws \Exception
     */
    public static function stockOut(
        int $productId,
        int $quantity,
        ?string $movementDate = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null
    ): InventoryMovement {
        return static::recordMovement(
            'OUT',
            $productId,
            $quantity,
            $movementDate,
            $referenceType,
            $referenceId,
            $notes,
            $userId
        );
    }

    /**
     * Record a stock ADJUSTMENT movement (manual corrections, audits).
     *
     * @param int $productId
     * @param int $quantity (can be positive or negative)
     * @param string|null $movementDate (default: now)
     * @param string|null $referenceType (e.g., 'manual', 'audit')
     * @param int|null $referenceId
     * @param string|null $notes
     * @param int|null $userId
     * @return InventoryMovement
     * @throws \Exception
     */
    public static function adjustment(
        int $productId,
        int $quantity,
        ?string $movementDate = null,
        ?string $referenceType = 'manual',
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null
    ): InventoryMovement {
        return static::recordMovement(
            'ADJUSTMENT',
            $productId,
            $quantity > 0 ? $quantity : abs($quantity),
            $movementDate,
            $referenceType,
            $referenceId,
            $notes,
            $userId,
            $quantity < 0  // If negative, it's actually a deduction
        );
    }

    /**
     * Record a movement (internal method).
     *
     * @param string $type IN, OUT, ADJUSTMENT
     * @param int $productId
     * @param int $quantity (absolute value, always positive)
     * @param string|null $movementDate
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param string|null $notes
     * @param int|null $userId
     * @param bool $isNegative (for adjustments: true means stock decrease)
     * @return InventoryMovement
     * @throws \Exception
     */
    private static function recordMovement(
        string $type,
        int $productId,
        int $quantity,
        ?string $movementDate = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
        ?int $userId = null,
        bool $isNegative = false
    ): InventoryMovement {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than 0');
        }

        $movementDate = $movementDate ? \Carbon\Carbon::parse($movementDate) : now();

        return DB::transaction(function () use (
            $type,
            $productId,
            $quantity,
            $movementDate,
            $referenceType,
            $referenceId,
            $notes,
            $userId,
            $isNegative
        ) {
            // Lock the product row for UPDATE
            $product = Product::whereKey($productId)->lockForUpdate()->first();

            if (!$product) {
                throw new \Exception("Product with ID {$productId} not found");
            }

            // Check for idempotency: prevent duplicate movements
            if ($referenceType && $referenceId) {
                $existing = InventoryMovement::where('reference_type', $referenceType)
                    ->where('reference_id', $referenceId)
                    ->where('product_id', $productId)
                    ->where('type', $type)
                    ->first();

                if ($existing) {
                    // Already recorded, return existing to prevent duplicates
                    return $existing;
                }
            }

            // Validate stock doesn't go negative for OUT movements
            if ($type === 'OUT') {
                if ($product->stock < $quantity) {
                    throw new \Exception(
                        "Insufficient stock for product {$product->name}. " .
                        "Available: {$product->stock}, Required: {$quantity}"
                    );
                }
            }

            // Create the movement record
            $movement = InventoryMovement::create([
                'product_id' => $productId,
                'movement_date' => $movementDate,
                'type' => $type,
                'quantity' => $quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'created_by' => $userId ?? auth()?->id(),
            ]);

            // Update product stock
            if ($type === 'IN') {
                $product->increment('stock', $quantity);
            } elseif ($type === 'OUT') {
                $product->decrement('stock', $quantity);
            } elseif ($type === 'ADJUSTMENT') {
                if ($isNegative) {
                    $product->decrement('stock', $quantity);
                } else {
                    $product->increment('stock', $quantity);
                }
            }

            return $movement;
        });
    }

    /**
     * Get inventory movement history for a product.
     *
     * @param int $productId
     * @param int|null $limit
     * @return \Illuminate\Support\Collection
     */
    public static function getMovementHistory(int $productId, ?int $limit = null)
    {
        $query = InventoryMovement::where('product_id', $productId)
            ->orderByDesc('movement_date');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get current stock level for a product.
     *
     * @param int $productId
     * @return int
     */
    public static function getCurrentStock(int $productId): int
    {
        return Product::findOrFail($productId)->stock;
    }

    /**
     * Get stock movements within a date range.
     *
     * @param \Carbon\Carbon $from
     * @param \Carbon\Carbon $to
     * @param string|null $type (IN, OUT, ADJUSTMENT, or null for all)
     * @param int|null $productId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getMovementsByDateRange(
        \Carbon\Carbon $from,
        \Carbon\Carbon $to,
        ?string $type = null,
        ?int $productId = null
    ) {
        $query = InventoryMovement::whereBetween('movement_date', [$from, $to]);

        if ($type) {
            $query->where('type', $type);
        }

        if ($productId) {
            $query->where('product_id', $productId);
        }

        return $query->orderByDesc('movement_date')->get();
    }

    /**
     * Get total movements for a product by type.
     *
     * @param int $productId
     * @return array
     */
    public static function getMovementSummary(int $productId): array
    {
        $movements = InventoryMovement::where('product_id', $productId)
            ->selectRaw('type, SUM(quantity) as total')
            ->groupBy('type')
            ->get();

        $summary = [
            'IN' => 0,
            'OUT' => 0,
            'ADJUSTMENT' => 0,
        ];

        foreach ($movements as $movement) {
            $summary[$movement->type] = $movement->total;
        }

        return $summary;
    }
}
