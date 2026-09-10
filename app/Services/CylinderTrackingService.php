<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class CylinderTrackingService
{
    /**
     * Get unreturned cylinder records for a specific customer.
     */
    public static function getUnreturnedCylindersForUser(int $userId)
    {
        $orders = Order::with(['orderItems.product', 'delivery.rider'])
            ->where('user_id', $userId)
            ->where('transaction_type', 'new_cylinder')
            ->where('status', 'delivered')
            ->whereNull('cylinder_returned_at')
            ->orderBy('delivered_at', 'asc')
            ->get();

        // Filter out any where isCylinderReturned() is true via fallback check
        return $orders->filter(function ($order) {
            return $order->isNewCylinderTransaction() && !$order->isCylinderReturned();
        })->values();
    }

    /**
     * Get cylinder summary stats for a customer.
     */
    public static function getUserCylinderStats(int $userId): array
    {
        $unreturnedOrders = self::getUnreturnedCylindersForUser($userId);

        $totalUnreturnedQty = $unreturnedOrders->sum(function ($order) {
            return $order->total_cylinder_quantity;
        });

        $returnedOrders = Order::with(['orderItems.product'])
            ->where('user_id', $userId)
            ->where('transaction_type', 'new_cylinder')
            ->where(function ($q) {
                $q->whereNotNull('cylinder_returned_at')
                  ->orWhere('status', 'delivered');
            })
            ->get()
            ->filter(function ($order) {
                return $order->isNewCylinderTransaction() && $order->isCylinderReturned();
            });

        $totalReturnedQty = $returnedOrders->sum(function ($order) {
            return $order->total_cylinder_quantity;
        });

        return [
            'unreturned_orders' => $unreturnedOrders,
            'unreturned_count' => (int) $totalUnreturnedQty,
            'returned_count' => (int) $totalReturnedQty,
            'has_unreturned' => $unreturnedOrders->isNotEmpty(),
        ];
    }

    /**
     * Get high-level cylinder tracking KPIs for Admin.
     */
    public static function getAdminTrackingStats(): array
    {
        $newCylinderOrders = Order::with(['orderItems.product'])
            ->where('transaction_type', 'new_cylinder')
            ->whereIn('status', ['delivered', 'out_for_delivery', 'assigned', 'approved'])
            ->get()
            ->filter(function ($order) {
                return $order->isNewCylinderTransaction();
            });

        $totalIssuedTanks = 0;
        $pendingReturnTanks = 0;
        $returnedTanks = 0;
        $overdueTanks = 0;

        foreach ($newCylinderOrders as $order) {
            $qty = $order->total_cylinder_quantity;
            $totalIssuedTanks += $qty;

            if ($order->isCylinderReturned()) {
                $returnedTanks += $qty;
            } elseif ($order->status === 'delivered') {
                $pendingReturnTanks += $qty;
                $daysHeld = $order->days_held ?? 0;
                if ($daysHeld >= 14) {
                    $overdueTanks += $qty;
                }
            }
        }

        return [
            'total_issued' => $totalIssuedTanks,
            'pending_return' => $pendingReturnTanks,
            'returned' => $returnedTanks,
            'overdue' => $overdueTanks,
        ];
    }

    /**
     * Fetch, filter, and sort cylinder tracking records for Admin.
     */
    public static function getAdminTrackingList(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Order::with(['user', 'orderItems.product', 'delivery.rider'])
            ->where('transaction_type', 'new_cylinder')
            ->whereIn('status', ['delivered', 'out_for_delivery', 'assigned', 'approved']);

        // Search filter: customer name, phone, or order number
        if (!empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(function ($q) use ($term) {
                $q->where('order_number', 'like', "%{$term}%")
                  ->orWhere('customer_name', 'like', "%{$term}%")
                  ->orWhere('contact_number', 'like', "%{$term}%")
                  ->orWhere('delivery_address', 'like', "%{$term}%")
                  ->orWhereHas('user', function ($uq) use ($term) {
                      $uq->where('name', 'like', "%{$term}%")
                         ->orWhere('email', 'like', "%{$term}%")
                         ->orWhere('phone', 'like', "%{$term}%");
                  });
            });
        }

        // Product filter
        if (!empty($filters['product_id'])) {
            $productId = (int) $filters['product_id'];
            $query->whereHas('orderItems', function ($iq) use ($productId) {
                $iq->where('product_id', $productId);
            });
        }

        $allOrders = $query->get()->filter(function ($order) {
            return $order->isNewCylinderTransaction();
        });

        // Status filter: pending_return, returned, all
        $statusFilter = $filters['status'] ?? 'pending_return';
        if ($statusFilter === 'pending_return') {
            $allOrders = $allOrders->filter(function ($order) {
                return $order->status === 'delivered' && !$order->isCylinderReturned();
            });
        } elseif ($statusFilter === 'returned') {
            $allOrders = $allOrders->filter(function ($order) {
                return $order->isCylinderReturned();
            });
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'days_held_desc';

        $sortedOrders = $allOrders->sortBy(function ($order) use ($sortBy) {
            return match ($sortBy) {
                'days_held_desc' => 999999 - ($order->days_held ?? 0),
                'days_held_asc' => $order->days_held ?? 0,
                'date_desc' => chr(255) . ($order->delivered_at ?? $order->created_at)?->format('Y-m-d H:i:s'),
                'date_asc' => ($order->delivered_at ?? $order->created_at)?->format('Y-m-d H:i:s'),
                'customer_asc' => strtolower($order->customer_name ?: ($order->user?->name ?? '')),
                'customer_desc' => chr(255) . strtolower($order->customer_name ?: ($order->user?->name ?? '')),
                'qty_desc' => 999999 - $order->total_cylinder_quantity,
                'status' => $order->isCylinderReturned() ? 'B_returned' : 'A_pending',
                default => 999999 - ($order->days_held ?? 0),
            };
        })->values();

        // Paginate results
        $page = (int) request('tracking_page', 1);
        return new LengthAwarePaginator(
            $sortedOrders->forPage($page, $perPage),
            $sortedOrders->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
                'pageName' => 'tracking_page',
            ]
        );
    }

    /**
     * Mark an order's cylinders as returned.
     */
    public static function markReturned(Order $order, ?int $adminId = null): array
    {
        if ($order->isCylinderReturned()) {
            return [
                'success' => false,
                'message' => "Order #{$order->order_number} has already been marked as returned.",
            ];
        }

        $cylinderItems = $order->getCylinderItems();
        if ($cylinderItems->isEmpty()) {
            return [
                'success' => false,
                'message' => "No cylinder items found in order #{$order->order_number}.",
            ];
        }

        $totalReturned = 0;

        DB::transaction(function () use ($order, $cylinderItems, $adminId, &$totalReturned) {
            $now = now();
            $adminId = $adminId ?? Auth::id();

            // 1. Update order cylinder_returned_at
            $order->update([
                'cylinder_returned_at' => $now,
            ]);

            // 2. Increment empty_on_hand for each cylinder product and record StockMovement
            foreach ($cylinderItems as $item) {
                $quantity = (int) $item->quantity;
                if ($quantity <= 0 || !$item->product_id) {
                    continue;
                }

                $inventory = Inventory::where('product_id', $item->product_id)
                    ->lockForUpdate()
                    ->first();

                if ($inventory) {
                    $inventory->increment('empty_on_hand', $quantity);

                    StockMovement::create([
                        'inventory_id' => $inventory->id,
                        'full_in' => 0,
                        'full_out' => 0,
                        'empty_in' => $quantity,
                        'empty_out' => 0,
                        'type' => 'return',
                        'reference' => $order->order_number ?? ('ORD-' . $order->id),
                        'notes' => 'Empty returned for ' . ($order->order_number ?? ('ORD-' . $order->id)) . ' (' . $item->product_name . ')',
                        'movement_date' => $now,
                        'created_by' => $adminId,
                    ]);
                }

                $totalReturned += $quantity;
            }

            ActivityLogger::log(
                'inventory',
                'cylinder_return',
                "Marked {$totalReturned} empty cylinder(s) returned for Order #{$order->order_number}",
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'quantity_returned' => $totalReturned,
                    'customer_name' => $order->customer_name,
                ]
            );
        });

        return [
            'success' => true,
            'message' => "Successfully marked {$totalReturned} cylinder(s) as returned for Order #{$order->order_number}.",
            'total_returned' => $totalReturned,
        ];
    }
}
