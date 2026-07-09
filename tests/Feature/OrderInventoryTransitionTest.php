<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderInventoryTransitionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_delivered_exchange_decreases_full_and_increases_empty_inventory(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->markTestSkipped('Skipped on sqlite: project includes MySQL-specific enum ALTER migration for deliveries status.');
        }

        [$admin, $inventory, $order] = $this->createApprovedOrderWithInventory('exchange', 20, 2, 3);

        $response = $this->actingAs($admin)
            ->put(route('admin.orders.status', $order), [
                'status' => 'delivered',
                'transaction_type' => 'exchange',
            ]);

        $response->assertStatus(302);

        $inventory->refresh();
        $this->assertSame(17, (int) $inventory->quantity_on_hand);
        $this->assertSame(5, (int) $inventory->empty_on_hand);

        $movement = StockMovement::query()
            ->where('inventory_id', $inventory->id)
            ->where('reference', $order->order_number)
            ->latest('id')
            ->first();

        $this->assertNotNull($movement);
        $this->assertSame(0, (int) $movement->full_in);
        $this->assertSame(3, (int) $movement->full_out);
        $this->assertSame(3, (int) $movement->empty_in);
        $this->assertSame(0, (int) $movement->empty_out);
    }

    public function test_delivered_new_cylinder_decreases_full_only(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            $this->markTestSkipped('Skipped on sqlite: project includes MySQL-specific enum ALTER migration for deliveries status.');
        }

        [$admin, $inventory, $order] = $this->createApprovedOrderWithInventory('new_cylinder', 25, 4, 5);

        $response = $this->actingAs($admin)
            ->put(route('admin.orders.status', $order), [
                'status' => 'delivered',
                'transaction_type' => 'new_cylinder',
            ]);

        $response->assertStatus(302);

        $inventory->refresh();
        $this->assertSame(20, (int) $inventory->quantity_on_hand);
        $this->assertSame(4, (int) $inventory->empty_on_hand);

        $movement = StockMovement::query()
            ->where('inventory_id', $inventory->id)
            ->where('reference', $order->order_number)
            ->latest('id')
            ->first();

        $this->assertNotNull($movement);
        $this->assertSame(0, (int) $movement->full_in);
        $this->assertSame(5, (int) $movement->full_out);
        $this->assertSame(0, (int) $movement->empty_in);
        $this->assertSame(0, (int) $movement->empty_out);
    }

    private function createApprovedOrderWithInventory(
        string $transactionType,
        int $fullOnHand,
        int $emptyOnHand,
        int $orderQuantity
    ): array {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'email_verified_at' => now(),
        ]);

        $product = Product::factory()->create([
            'name' => 'LPG Tank 11kg',
            'category' => 'tank',
            'stock' => $fullOnHand,
            'price' => 1000,
            'cost_price' => 700,
            'selling_price' => 1000,
        ]);

        $inventory = Inventory::create([
            'product_id' => $product->id,
            'quantity_on_hand' => $fullOnHand,
            'empty_on_hand' => $emptyOnHand,
            'reorder_level' => 5,
            'status' => 'active',
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'GG-TEST' . strtoupper(substr(md5((string) microtime(true)), 0, 6)),
            'order_type' => 'online',
            'transaction_type' => $transactionType,
            'subtotal' => 1000 * $orderQuantity,
            'discount' => 0,
            'delivery_fee' => 50,
            'total_amount' => (1000 * $orderQuantity) + 50,
            'customer_name' => $customer->name,
            'delivery_address' => 'Test Address',
            'contact_number' => '09123456789',
            'payment_method' => 'cash',
            'status' => 'approved',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity' => $orderQuantity,
            'price' => 1000,
            'subtotal' => 1000 * $orderQuantity,
            'is_reward' => false,
        ]);

        return [$admin, $inventory, $order];
    }
}
