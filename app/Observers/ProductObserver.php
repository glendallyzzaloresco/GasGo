<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    /**
     * Handle the Product "updating" event - Sync inventory.quantity_on_hand with product.stock
     */
    public function updating(Product $product): void
    {
        // If stock is being changed directly, sync it to inventory.quantity_on_hand
        if ($product->isDirty('stock')) {
            $product->inventory?->update([
                'quantity_on_hand' => $product->stock,
            ]);
        }
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        // Ensure inventory.quantity_on_hand matches product.stock after any update
        if ($product->inventory && $product->inventory->quantity_on_hand !== $product->stock) {
            $product->inventory->update([
                'quantity_on_hand' => $product->stock,
            ]);
        }

        // Keep inventory status in sync with product is_active state
        if ($product->isDirty('is_active') && $product->inventory) {
            $product->inventory->update([
                'status' => $product->is_active ? 'active' : 'discontinued',
            ]);
        }
    }
}
