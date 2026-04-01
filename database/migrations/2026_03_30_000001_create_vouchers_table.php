<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('discount_amount', 8, 2)->default(0);
            $table->integer('reward_points_required')->default(0)->comment('Points needed to unlock this voucher');
            $table->integer('stock')->default(0)->comment('How many times this voucher can be claimed');
            $table->integer('claimed_count')->default(0)->comment('How many times claimed');
            $table->integer('used_count')->default(0)->comment('How many times used');
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('is_active');
            $table->index('reward_points_required');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
