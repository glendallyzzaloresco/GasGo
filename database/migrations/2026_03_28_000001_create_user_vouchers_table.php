<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('voucher_name');
            $table->decimal('discount_amount', 8, 2);
            $table->text('description')->nullable();
            $table->timestamp('unlocked_at')->useCurrent();
            $table->timestamp('expires_at')->useCurrent();
            $table->timestamp('applied_at')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('is_used')->default(false);
            $table->timestamps();

            $table->index('user_id');
            $table->index('expires_at');
            $table->index('is_used');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_vouchers');
    }
};
