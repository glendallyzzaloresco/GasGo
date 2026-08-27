<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('products', 'requires_exchange')) {
            // Find non-cylinder products (stoves, burners, regulators, hoses, accessories, appliances, freebies)
            $nonCylinderProductIds = DB::table('products')
                ->where(function ($query) {
                    $query->where('name', 'LIKE', '%Stove%')
                        ->orWhere('name', 'LIKE', '%Burner%')
                        ->orWhere('name', 'LIKE', '%Regulator%')
                        ->orWhere('name', 'LIKE', '%Hose%')
                        ->orWhere('name', 'LIKE', '%Clamp%')
                        ->orWhere('name', 'LIKE', '%Hanger%')
                        ->orWhere('name', 'LIKE', '%Paste%');
                })
                ->orWhere(function ($query) {
                    if (Schema::hasColumn('products', 'category_id')) {
                        $nonCylinderCatIds = DB::table('categories')
                            ->whereIn('slug', ['appliances', 'accessories', 'parts', 'freebie', 'kitchen', 'dispensers', 'meals', 'snacks', 'beverages', 'bilao'])
                            ->pluck('id');
                        $query->whereIn('category_id', $nonCylinderCatIds);
                    }
                })
                ->pluck('id');

            if ($nonCylinderProductIds->isNotEmpty()) {
                // Set requires_exchange = false
                DB::table('products')
                    ->whereIn('id', $nonCylinderProductIds)
                    ->update(['requires_exchange' => false]);

                // Reset empty_on_hand to 0 for these products in inventory
                if (Schema::hasTable('inventories')) {
                    DB::table('inventories')
                        ->whereIn('product_id', $nonCylinderProductIds)
                        ->update(['empty_on_hand' => 0]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reverse needed
    }
};
