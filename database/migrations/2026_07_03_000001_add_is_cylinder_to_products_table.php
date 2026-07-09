<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_cylinder')->default(false)->after('image');
        });

        // Backfill existing tank products as cylinder products.
        \Illuminate\Support\Facades\DB::table('products')
            ->whereRaw('LOWER(category) = ?', ['tank'])
            ->update(['is_cylinder' => true]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_cylinder');
        });
    }
};
