<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\Product;

// Replicate exact route logic
$products = Product::query()
    ->with('inventory')
    ->where('is_active', true)
    ->where('price', '>', 0)
    ->get()
    ->sortByDesc('created_at')
    ->values();

echo "=== FETCHED ALL PRODUCTS ===\n";
echo "Total fetched: " . count($products) . "\n";
foreach($products as $p) {
    echo "  - {$p->name} ({$p->category})\n";
}

// Now apply the grouping logic
$categoryMap = $products->mapToGroups(function ($item) {
    $normalized = strtolower(trim($item->category ?? 'uncategorized'));
    return [$normalized => $item];
});

echo "\n=== GROUPED BY NORMALIZED CATEGORY ===\n";
foreach($categoryMap as $cat => $items) {
    echo "$cat: " . count($items) . " products\n";
}

$featuredByCategory = [];

echo "\n=== DISTRIBUTION LOGIC (NEW - ALTERNATING) ===\n";

// First pass: get 1 from each category
echo "First pass: Taking 1 from each category\n";
foreach ($categoryMap as $normalizedCategory => $categoryProducts) {
    if (count($featuredByCategory) >= 4) break;
    $first = $categoryProducts->first();
    if ($first) {
        echo "  Adding {$first->name} from $normalizedCategory\n";
        $featuredByCategory[] = $first;
    }
}

// Second pass: get additional products from categories if needed (up to 4 total)
if (count($featuredByCategory) < 4) {
    echo "Second pass: Taking additional products to reach 4\n";
    foreach ($categoryMap as $normalizedCategory => $categoryProducts) {
        if (count($featuredByCategory) >= 4) break;
        // Skip first product we already took
        $remaining = $categoryProducts->skip(1);
        foreach ($remaining as $product) {
            if (count($featuredByCategory) >= 4) break;
            if (!in_array($product->id, array_column($featuredByCategory, 'id'))) {
                echo "  Adding {$product->name} from $normalizedCategory\n";
                $featuredByCategory[] = $product;
            }
        }
    }
}

echo "\n=== FINAL FEATURED PRODUCTS ===\n";
echo "Total: " . count($featuredByCategory) . "\n";
foreach($featuredByCategory as $p) {
    echo "  - {$p->name} ({$p->category})\n";
}
