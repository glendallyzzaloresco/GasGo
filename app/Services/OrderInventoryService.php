<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\StockMovement;

class OrderInventoryService
{
    /**
     * Apply inventory updates when an order is completed.
     *
     * exchange: full_out + empty_in
     * new_cylinder/refill: full_out only
     */
    public static function applyOnCompletion(Order $order, ?int $userId = null): void
    {
        $order->loadMissing('orderItems.product');

        $transactionType = $order->transaction_type ?: 'exchange';

        foreach ($order->orderItems as $item) {
            if ($item->is_reward || !$item->product_id) {
                continue;
            }

            $quantity = (int) $item->quantity;
            if ($quantity <= 0) {
                continue;
            }

            $inventory = Inventory::where('product_id', $item->product_id)
                ->lockForUpdate()
                ->first();

            if (!$inventory) {
                throw new \RuntimeException("Inventory record missing for product ID {$item->product_id}.");
            }

            if ((int) $inventory->quantity_on_hand < $quantity) {
                throw new \RuntimeException(
                    "Insufficient stock for {$item->product_name}. Available: {$inventory->quantity_on_hand}, required: {$quantity}."
                );
            }

            $isCylinderProduct = $inventory->product?->isCylinder() ? true : false;

            $inventory->decrement('quantity_on_hand', $quantity);

            $emptyIn = 0;
            if ($isCylinderProduct && $transactionType === 'exchange') {
                $inventory->increment('empty_on_hand', $quantity);
                $emptyIn = $quantity;
            }

            StockMovement::create([
                'inventory_id' => $inventory->id,
                'full_in' => 0,
                'full_out' => $quantity,
                'empty_in' => $emptyIn,
                'empty_out' => 0,
                'type' => 'sale',
                'reference' => $order->order_number ?? ('ORD-' . $order->id),
                'notes' => 'Order completed (' . $transactionType . ')',
                'movement_date' => now(),
                'created_by' => $userId,
            ]);
        }
    }
}
