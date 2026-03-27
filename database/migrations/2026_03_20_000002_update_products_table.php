<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Add new columns
            $table->decimal('cost_price', 10, 2)->nullable()->after('price');
            $table->string('sku')->nullable()->unique()->after('cost_price');
            $table->string('barcode')->nullable()->unique()->after('sku');
            
            // Drop stock column if it exists (it will be managed by inventory table)
            // Note: Only drop if you've migrated all data to inventory table
            // $table->dropColumn('stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'sku', 'barcode']);
            // Add back stock column if you dropped it
            // $table->integer('stock')->default(0);
        });
    }
};
