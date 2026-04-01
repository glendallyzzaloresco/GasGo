<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'category')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('category')->default('tank')->after('name');
            });
        }

        // Normalize existing values to the three supported categories.
        DB::table('products')
            ->whereNull('category')
            ->orWhere('category', '')
            ->update(['category' => 'tank']);

        DB::table('products')
            ->whereRaw("LOWER(category) IN ('tanks', 'tank')")
            ->update(['category' => 'tank']);

        DB::table('products')
            ->whereRaw("LOWER(category) IN ('regulators', 'regulator', 'hoses', 'hose', 'accessories', 'accessory')")
            ->update(['category' => 'accessory']);

        DB::table('products')
            ->whereRaw("LOWER(category) IN ('freebie', 'freebies', 'reward', 'rewards')")
            ->update(['category' => 'freebie']);

        // Safety catch: force any remaining non-supported value to tank.
        DB::table('products')
            ->whereNotIn('category', ['tank', 'accessory', 'freebie'])
            ->update(['category' => 'tank']);
    }

    public function down(): void
    {
        // Keep data as-is on rollback to avoid destructive category loss.
    }
};
