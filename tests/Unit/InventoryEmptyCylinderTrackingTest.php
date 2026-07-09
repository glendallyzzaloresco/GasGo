<?php

namespace Tests\Unit;

use App\Models\Inventory;
use App\Models\Product;
use Tests\TestCase;

class InventoryEmptyCylinderTrackingTest extends TestCase
{
    public function test_tank_products_can_track_empty_cylinders_but_other_categories_cannot(): void
    {
        $tankInventory = new Inventory();
        $tankInventory->empty_on_hand = 4;
        $tankInventory->setRelation('product', new Product(['category' => 'Tank']));

        $nonTankInventory = new Inventory();
        $nonTankInventory->empty_on_hand = 4;
        $nonTankInventory->setRelation('product', new Product(['category' => 'Accessories']));

        $this->assertTrue($tankInventory->supportsEmptyCylinderTracking());
        $this->assertFalse($nonTankInventory->supportsEmptyCylinderTracking());
    }
}
