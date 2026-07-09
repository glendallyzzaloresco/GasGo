<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('order_type', ['online', 'walk_in'])
                ->default('online')
                ->after('order_number');

            $table->enum('transaction_type', ['exchange', 'new_cylinder'])
                ->default('exchange')
                ->after('order_type');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['order_type', 'transaction_type']);
        });
    }
};
