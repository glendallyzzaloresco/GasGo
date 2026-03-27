<?php

namespace App\Http\Controllers;

use App\Models\Freebie;
use App\Models\Inventory;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // Display all active products
    public function index()
    {
        $products = Product::with('inventory')
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->orderBy('name')
            ->get();

        return view('customer.product', compact('products'));
    }

    // Show single product details
    public function show(Product $product)
    {
        return view('customer.product-detail', compact('product'));
    }

    // Admin: list all products (including inactive)
    public function adminIndex()
    {
        // Sorted for readability: tanks first, freebies next, out-of-stock items last.
        $products = Product::with('inventory')
            ->get()
            ->sortBy(function ($product) {
                $category = strtolower((string) ($product->category ?? ''));
                $categoryOrder = match ($category) {
                    'tank' => 0,
                    'freebie' => 1,
                    default => 2,
                };

                $stock = (int) ($product->quantity_on_hand ?? $product->stock ?? 0);
                $isOutOfStock = $stock <= 0 ? 1 : 0;

                return [
                    $isOutOfStock,
                    $categoryOrder,
                    strtolower((string) $product->name),
                ];
            })
            ->values();

        $productCategories = Product::query()
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');
        
        // Freebies (separate from products)
        $freebies = Freebie::query()
            ->get()
            ->sortBy(function ($freebie) {
                $isOutOfStock = ((int) ($freebie->stock ?? 0) <= 0) ? 1 : 0;

                return [
                    $isOutOfStock,
                    strtolower((string) $freebie->name),
                ];
            })
            ->values();

        return view('admin.products', compact('products', 'freebies', 'productCategories'));
    }

    // Admin: store a new product
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|in:tank,freebie',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'weight'      => 'nullable|string|max:255',
            'image'       => 'nullable|image|max:2048',
            'is_active'   => 'boolean',
        ]);

        $validated['category'] = strtolower((string) $validated['category']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['stock'] = (int) ($validated['stock'] ?? 0);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($validated) {
            $product = Product::create($validated);

            // Keep inventory synced with product stock on creation.
            Inventory::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity_on_hand' => (int) $validated['stock'],
                    'reorder_level' => 5,
                    'status' => 'active',
                ]
            );
        });

        return redirect()->route('admin.products')->with('success', 'Product created successfully.');
    }

    // Admin: update an existing product
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'category'    => 'required|in:tank,freebie',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'nullable|integer|min:0',
            'weight'      => 'nullable|string|max:255',
            'image'       => 'nullable|image|max:2048',
            'is_active'   => 'boolean',
        ]);

        $validated['category'] = strtolower((string) $validated['category']);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($product, $validated) {
            $product->update($validated);

            $inventory = Inventory::firstOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity_on_hand' => 0,
                    'reorder_level' => 5,
                    'status' => 'active',
                ]
            );

            // Keep inventory and product stock aligned when stock is provided from form.
            if (array_key_exists('stock', $validated)) {
                $inventory->update([
                    'quantity_on_hand' => (int) $validated['stock'],
                ]);
            }
        });

        return redirect()->route('admin.products')->with('success', 'Product updated successfully.');
    }

    // Admin: delete a product
    public function destroy(Product $product)
    {
        // Keep historical order integrity: products used in order items cannot be hard-deleted.
        if ($product->orderItems()->exists()) {
            DB::transaction(function () use ($product) {
                // Remove any active cart rows so it can no longer be purchased.
                $product->carts()->delete();

                // Deactivate product and set stock to zero so it's effectively removed from sales.
                $product->update([
                    'is_active' => false,
                    'stock' => 0,
                ]);

                $product->inventory()->update([
                    'quantity_on_hand' => 0,
                    'status' => 'discontinued',
                ]);
            });

            return redirect()->route('admin.products')->with(
                'success',
                'Product is linked to existing orders and was archived instead of permanently deleted.'
            );
        }

        DB::transaction(function () use ($product) {
            $product->carts()->delete();
            $product->delete();
        });

        return redirect()->route('admin.products')->with('success', 'Product deleted successfully.');
    }

    // Admin: store a new freebie
    public function storeFreebie(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'description'           => 'nullable|string',
            'stock'                 => 'required|integer|min:0',
            'category'              => 'nullable|string|max:255',
            'reward_points_required'=> 'nullable|integer|min:0',
            'redemption_type'       => 'required|in:loyalty_points,auto_included,promotional',
            'image'                 => 'nullable|image|max:2048',
            'is_active'             => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('freebies', 'public');
        }

        $validated['reward_points_required'] = (int) ($validated['reward_points_required'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');

        $freebie = Freebie::create($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Freebie created successfully.',
                'freebie_id' => $freebie->id,
            ], 201);
        }

        return redirect()->route('admin.products', ['tab' => 'freebies'])->with('success', 'Freebie created successfully.');
    }

    // Admin: update an existing freebie
    public function updateFreebie(Request $request, Freebie $freebie)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'description'           => 'nullable|string',
            'stock'                 => 'required|integer|min:0',
            'category'              => 'nullable|string|max:255',
            'reward_points_required'=> 'nullable|integer|min:0',
            'redemption_type'       => 'required|in:loyalty_points,auto_included,promotional',
            'image'                 => 'nullable|image|max:2048',
            'is_active'             => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('freebies', 'public');
        }

        $validated['reward_points_required'] = (int) ($validated['reward_points_required'] ?? 0);
        $validated['is_active'] = $request->boolean('is_active');

        $freebie->update($validated);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Freebie updated successfully.',
                'freebie_id' => $freebie->id,
            ]);
        }

        return redirect()->route('admin.products', ['tab' => 'freebies'])->with('success', 'Freebie updated successfully.');
    }

    // Admin: delete a freebie
    public function destroyFreebie(Freebie $freebie)
    {
        $freebie->delete();

        return redirect()->route('admin.products')->with('success', 'Freebie deleted successfully.');
    }
}
