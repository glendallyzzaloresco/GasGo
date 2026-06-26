<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPaymentStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:payment-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync payment status with order status: delivered orders get "paid", others get "failed"';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting payment status synchronization...');

        $orders = Order::all();
        $deliveredCount = 0;
        $failedCount = 0;
        $errors = 0;

        foreach ($orders as $order) {
            try {
                if ($order->status === 'delivered') {
                    DB::table('payments')
                        ->where('order_id', $order->id)
                        ->update([
                            'status' => 'paid',
                            'paid_at' => $order->delivered_at ?? now(),
                            'updated_at' => now(),
                        ]);
                    $deliveredCount++;
                } else {
                    // All non-delivered orders (cancelled, failed, pending, etc.) get failed status
                    DB::table('payments')
                        ->where('order_id', $order->id)
                        ->update([
                            'status' => 'failed',
                            'updated_at' => now(),
                        ]);
                    $failedCount++;
                }
            } catch (\Exception $e) {
                $this->error("Failed to sync payment for order {$order->id}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->info('✓ Payment status synchronization completed!');
        $this->line("  Delivered orders marked as PAID: {$deliveredCount}");
        $this->line("  Other orders marked as FAILED: {$failedCount}");
        
        if ($errors > 0) {
            $this->warn("  ✗ Errors encountered: {$errors}");
        } else {
            $this->info("  ✓ All records synced successfully!");
        }
    }
}
