<?php

namespace Tests\Unit;

use App\Http\Controllers\LoyaltyController;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Tests\TestCase;

class LoyaltyPointsCalculationTest extends TestCase
{
    public function test_points_calculation_only_counts_tank_products_at_gross_rate(): void
    {
        $controller = new LoyaltyController();

        $reflection = new \ReflectionClass(LoyaltyController::class);
        $method = $reflection->getMethod('calculateSpendFromOrder');
        $method->setAccessible(true);

        // Scenario 1: Order with ₱3,300 worth of tank products and voucher discount
        $order1 = new Order([
            'discount_amount' => 30.00,
            'delivery_fee' => 50.00,
            'total_amount' => 3320.00,
        ]);
        
        $tankProduct = new Product(['name' => 'Solane 11kg Tank', 'category' => 'tank']);
        $tankItem = new OrderItem([
            'product_id' => 1,
            'price' => 1650.00,
            'quantity' => 2,
            'subtotal' => 3300.00,
            'is_reward' => false,
        ]);
        $tankItem->setRelation('product', $tankProduct);
        $order1->setRelation('orderItems', collect([$tankItem]));

        $spend1 = $method->invoke($controller, $order1);
        $this->assertEquals(3300.00, $spend1);
        $this->assertEquals(33, (int) floor($spend1 / 100));

        // Scenario 2: Order with ₱1,800 worth of tank products
        $order2 = new Order([
            'discount_amount' => 30.00,
            'delivery_fee' => 0.00,
            'total_amount' => 1770.00,
        ]);
        
        $tankItem2 = new OrderItem([
            'product_id' => 1,
            'price' => 1800.00,
            'quantity' => 1,
            'subtotal' => 1800.00,
            'is_reward' => false,
        ]);
        $tankItem2->setRelation('product', $tankProduct);
        $order2->setRelation('orderItems', collect([$tankItem2]));

        $spend2 = $method->invoke($controller, $order2);
        $this->assertEquals(1800.00, $spend2);
        $this->assertEquals(18, (int) floor($spend2 / 100));

        // Scenario 3: Order with paid items vs free reward items (reward items should earn 0 points)
        $order3 = new Order([
            'discount_amount' => 0.00,
            'delivery_fee' => 0.00,
            'total_amount' => 2500.00,
        ]);

        $applianceProduct = new Product(['name' => 'Gas Stove Double Burner', 'category' => 'appliances']);
        $freebieProduct = new Product(['name' => 'Free Safety Cap', 'category' => 'freebie']);

        $paidItem = new OrderItem([
            'product_id' => 2,
            'price' => 2000.00,
            'quantity' => 1,
            'subtotal' => 2000.00,
            'is_reward' => false,
        ]);
        $paidItem->setRelation('product', $applianceProduct);

        $rewardItem = new OrderItem([
            'product_id' => 3,
            'price' => 500.00,
            'quantity' => 1,
            'subtotal' => 500.00,
            'is_reward' => true,
        ]);
        $rewardItem->setRelation('product', $freebieProduct);

        $order3->setRelation('orderItems', collect([$paidItem, $rewardItem]));

        $spend3 = $method->invoke($controller, $order3);
        $this->assertEquals(2000.00, $spend3);
        $this->assertEquals(20, (int) floor($spend3 / 100));
    }
}
