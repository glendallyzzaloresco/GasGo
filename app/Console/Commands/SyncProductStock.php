<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncProductStock extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:product-stock';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product.stock with inventory.quantity_on_hand for all products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting stock synchronization...');

        $inventories = Inventory::all();
        $count = 0;
        $errors = 0;

        foreach ($inventories as $inventory) {
            try {
                if ($inventory->product_id) {
                    DB::table('products')
                        ->where('id', $inventory->product_id)
                        ->update([
                            'stock' => $inventory->quantity_on_hand,
                            'updated_at' => now(),
                        ]);
                    $count++;
                }
            } catch (\Exception $e) {
                $this->error("Failed to sync product {$inventory->product_id}: " . $e->getMessage());
                $errors++;
            }
        }

        $this->info("✓ Synchronized {$count} products");
        if ($errors > 0) {
            $this->warn("✗ {$errors} errors encountered");
        } else {
            $this->info("✓ All products synced successfully!");
        }
    }
}
