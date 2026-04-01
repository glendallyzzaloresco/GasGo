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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->dateTime('movement_date');
            $table->enum('type', ['IN', 'OUT', 'ADJUSTMENT'])->default('ADJUSTMENT');
            $table->unsignedInteger('quantity');
            $table->string('reference_type')->nullable(); // 'order', 'restock', 'manual', 'return'
            $table->unsignedBigInteger('reference_id')->nullable(); // order_id, restock_id, etc
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes for performance
            $table->index('movement_date');
            $table->index(['product_id', 'movement_date']);
            
            // Idempotency: prevent duplicate movements for the same reference
            $table->unique(['reference_type', 'reference_id', 'product_id', 'type'], 'unique_movement_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
