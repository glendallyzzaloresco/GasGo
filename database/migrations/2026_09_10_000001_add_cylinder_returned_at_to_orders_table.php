<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'cylinder_returned_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->timestamp('cylinder_returned_at')->nullable()->after('delivered_at');
            });
        }

        // Backfill cylinder_returned_at for existing orders that already have an empty cylinder return movement
        try {
            $returnedMovements = DB::table('stock_movements')
                ->where('type', 'return')
                ->where('empty_in', '>', 0)
                ->whereNotNull('reference')
                ->get();

            foreach ($returnedMovements as $movement) {
                DB::table('orders')
                    ->where('order_number', $movement->reference)
                    ->orWhere('id', (int) filter_var($movement->reference, FILTER_SANITIZE_NUMBER_INT))
                    ->update([
                        'cylinder_returned_at' => $movement->movement_date ?? $movement->created_at ?? now()
                    ]);
            }
        } catch (\Throwable $e) {
            // Ignore backfill errors during migration if stock_movements table is absent or empty
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'cylinder_returned_at')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('cylinder_returned_at');
            });
        }
    }
};
