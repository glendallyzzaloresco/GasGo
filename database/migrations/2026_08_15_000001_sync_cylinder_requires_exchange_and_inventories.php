<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Inventory;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Sync requires_exchange flag for tank/cylinder products
        if (Schema::hasColumn('products', 'requires_exchange')) {
            DB::table('products')
                ->where(function ($query) {
                    $query->whereIn(DB::raw('LOWER(category)'), ['tank', 'tanks', 'cylinder', 'cylinders', 'lpg', 'lpg-tanks'])
                        ->orWhere('name', 'LIKE', '%Tank%')
                        ->orWhere('name', 'LIKE', '%Cylinder%');
                })
                ->where(function ($query) {
                    $query->whereRaw('LOWER(COALESCE(category, "")) NOT IN (?, ?, ?)', ['accessories', 'appliances', 'freebie'])
                        ->where('name', 'NOT LIKE', '%Regulator%')
                        ->where('name', 'NOT LIKE', '%Hose%')
                        ->where('name', 'NOT LIKE', '%Clamp%')
                        ->where('name', 'NOT LIKE', '%Stove%')
                        ->where('name', 'NOT LIKE', '%Burner%')
                        ->where('name', 'NOT LIKE', '%Hanger%')
                        ->where('name', 'NOT LIKE', '%Paste%');
                })
                ->update(['requires_exchange' => true]);
        }

        // 2. Ensure all products have an inventory record
        $products = DB::table('products')
            ->where(function ($q) {
                $q->whereNull('category')
                    ->orWhereRaw('LOWER(category) != ?', ['freebie']);
            })
            ->get();

        foreach ($products as $product) {
            $exists = DB::table('inventory')->where('product_id', $product->id)->exists();
            if (!$exists) {
                DB::table('inventory')->insert([
                    'product_id' => $product->id,
                    'quantity_on_hand' => (int) ($product->stock ?? 0),
                    'empty_on_hand' => 0,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 3. Ensure all rider users have a corresponding rider record
        $riderUsers = DB::table('users')->where('role', 'rider')->get();
        foreach ($riderUsers as $ru) {
            $exists = DB::table('riders')->where('user_id', $ru->id)->exists();
            if (!$exists) {
                DB::table('riders')->insert([
                    'user_id' => $ru->id,
                    'availability' => 'available',
                    'vehicle_type' => 'Motorcycle',
                    'plate_number' => 'N/A',
                    'license_number' => 'N/A',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4. Sync empty_on_hand for cylinder inventories based on stock movements
        $cylinderInventories = DB::table('inventory')
            ->join('products', 'inventory.product_id', '=', 'products.id')
            ->where('products.requires_exchange', true)
            ->select('inventory.id', 'inventory.product_id', 'inventory.empty_on_hand')
            ->get();

        foreach ($cylinderInventories as $inv) {
            $sumEmptyIn = (int) DB::table('stock_movements')->where('inventory_id', $inv->id)->sum('empty_in');
            $sumEmptyOut = (int) DB::table('stock_movements')->where('inventory_id', $inv->id)->sum('empty_out');
            $calculatedEmpty = max(0, $sumEmptyIn - $sumEmptyOut);

            if ($calculatedEmpty > 0 && $inv->empty_on_hand == 0) {
                DB::table('inventory')->where('id', $inv->id)->update(['empty_on_hand' => $calculatedEmpty]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
