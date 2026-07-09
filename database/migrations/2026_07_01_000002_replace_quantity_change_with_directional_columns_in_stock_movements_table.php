<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->unsignedInteger('full_in')->default(0)->after('inventory_id');
            $table->unsignedInteger('full_out')->default(0)->after('full_in');
            $table->unsignedInteger('empty_in')->default(0)->after('full_out');
            $table->unsignedInteger('empty_out')->default(0)->after('empty_in');
        });

        DB::table('stock_movements')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                $change = (int) ($row->quantity_change ?? 0);

                DB::table('stock_movements')
                    ->where('id', $row->id)
                    ->update([
                        'full_in' => $change > 0 ? $change : 0,
                        'full_out' => $change < 0 ? abs($change) : 0,
                        'empty_in' => 0,
                        'empty_out' => 0,
                    ]);
            }
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('quantity_change');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->integer('quantity_change')->default(0)->after('inventory_id');
        });

        DB::table('stock_movements')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                $net = (int) $row->full_in - (int) $row->full_out + (int) $row->empty_in - (int) $row->empty_out;

                DB::table('stock_movements')
                    ->where('id', $row->id)
                    ->update(['quantity_change' => $net]);
            }
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['full_in', 'full_out', 'empty_in', 'empty_out']);
        });
    }
};
