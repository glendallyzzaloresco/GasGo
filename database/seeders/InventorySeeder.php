<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Database\Seeder;

class InventorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::whereNull('id', 'and')->orWhereNotNull('id')->get();

        foreach ($products as $product) {
            // Create inventory record if it doesn't exist
            Inventory::firstOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity_on_hand' => $product->stock ?? 0,
                    'status' => $product->is_active ? 'active' : 'discontinued',
                    'supplier' => null,
                    'last_restocked' => now(),
                ]
            );
        }
    }
}
