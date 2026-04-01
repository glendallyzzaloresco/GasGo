<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['stock', 'claimed_count', 'used_count']);
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->integer('stock')->default(0)->comment('How many times this voucher can be claimed');
            $table->integer('claimed_count')->default(0)->comment('How many times claimed');
            $table->integer('used_count')->default(0)->comment('How many times used');
        });
    }
};
