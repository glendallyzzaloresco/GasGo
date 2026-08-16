<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('category')
                    ->constrained('categories')
                    ->nullOnDelete();
            });
        }

        // Map existing products to categories based on current category string
        $categories = DB::table('categories')->get()->keyBy(function ($item) {
            return strtolower($item->slug ?: $item->name);
        });

        $lpgTankCategory = DB::table('categories')
            ->where('slug', 'lpg-tanks')
            ->orWhere('name', 'LPG Tanks')
            ->orWhere('name', 'like', '%tank%')
            ->first();

        $accessoriesCategory = DB::table('categories')
            ->where('slug', 'accessories')
            ->orWhere('name', 'Accessories')
            ->orWhere('name', 'like', '%accessor%')
            ->first();

        $appliancesCategory = DB::table('categories')
            ->where('slug', 'appliances')
            ->orWhere('name', 'Appliances')
            ->orWhere('name', 'like', '%appliance%')
            ->first();

        $products = DB::table('products')->get();

        foreach ($products as $product) {
            $catLower = strtolower(trim((string) $product->category));
            $categoryId = null;

            if (in_array($catLower, ['tank', 'tanks', 'cylinder', 'cylinders', 'lpg-tanks'])) {
                $categoryId = $lpgTankCategory?->id;
            } elseif (in_array($catLower, ['accessories', 'accessory'])) {
                $categoryId = $accessoriesCategory?->id;
            } elseif (in_array($catLower, ['appliances', 'appliance'])) {
                $categoryId = $appliancesCategory?->id;
            } elseif (isset($categories[$catLower])) {
                $categoryId = $categories[$catLower]->id;
            }

            if ($categoryId) {
                DB::table('products')
                    ->where('id', $product->id)
                    ->update(['category_id' => $categoryId]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('products', 'category_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            });
        }
    }
};
