<?php

namespace App\Observers;

use App\Models\Inventory;
use Illuminate\Support\Facades\DB;

class InventoryObserver
{
    /**
     * Handle the Inventory "saved" event - Always sync product.stock with inventory.quantity_on_hand
     */
    public function saved(Inventory $inventory): void
    {
        // Always ensure product.stock matches inventory.quantity_on_hand
        if ($inventory->product_id && $inventory->quantity_on_hand !== null) {
            DB::table('products')
                ->where('id', $inventory->product_id)
                ->update(['stock' => $inventory->quantity_on_hand]);
        }
    }

    /**
     * Handle the Inventory "created" event.
     */
    public function created(Inventory $inventory): void
    {
        // Sync product.stock with the newly created inventory quantity_on_hand
        if ($inventory->product_id && $inventory->quantity_on_hand !== null) {
            DB::table('products')
                ->where('id', $inventory->product_id)
                ->update(['stock' => $inventory->quantity_on_hand]);
        }
    }
}

