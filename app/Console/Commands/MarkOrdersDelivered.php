<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class MarkOrdersDelivered extends Command
{
    protected $signature = 'orders:mark-delivered {user_id?}';
    protected $description = 'Mark all pending orders as delivered for testing';

    public function handle()
    {
        $userId = $this->argument('user_id');
        
        // If no user_id provided, show summary of all users and their orders
        if (!$userId) {
            $this->info("Orders by user:");
            $orders = Order::select('user_id')->distinct()->get(['user_id']);
            foreach ($orders as $user) {
                $count = Order::where('user_id', $user->user_id)->count();
                $deliveredCount = Order::where('user_id', $user->user_id)->where('status', 'delivered')->count();
                $this->line("  User {$user->user_id}: $count orders ($deliveredCount delivered)");
            }
            $this->info("\nUsage: php artisan orders:mark-delivered {user_id}");
            return;
        }
        
        // Show all orders for this user
        $allOrders = Order::where('user_id', $userId)->get(['id', 'order_number', 'status']);
        $this->info("\nAll orders for user $userId:");
        if ($allOrders->isEmpty()) {
            $this->warn("No orders found for user $userId");
            return;
        } else {
            foreach ($allOrders as $order) {
                $this->line("  - {$order->order_number}: {$order->status}");
            }
        }
        
        $updated = Order::where('user_id', $userId)
            ->whereIn('status', ['pending', 'approved', 'assigned', 'out_for_delivery'])
            ->update([
                'status' => 'delivered',
                'delivered_at' => now()
            ]);

        $this->info("\n✓ Updated $updated orders to delivered status for user $userId");

        $deliveredCount = Order::where('user_id', $userId)
            ->where('status', 'delivered')
            ->count();
        
        $this->info("Total delivered orders: $deliveredCount\n");
    }
}
