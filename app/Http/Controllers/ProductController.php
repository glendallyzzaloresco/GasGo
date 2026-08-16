<?php

namespace App\Http\Controllers;

use App\Models\Freebie;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProductController extends Controller
{
    // Display all active products with optional category filter
    public function index(Request $request)
    {
        $query = Product::with('inventory')
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->where(function ($q) {
                $q->whereNull('category')
                  ->orWhereRaw('LOWER(category) != ?', ['freebie']);
            });

        // Apply category filter if provided (case-insensitive & handles aliases)
        $category = $request->query('category');
        if ($category) {
            $catLower = strtolower(trim($category));
            if (in_array($catLower, ['tank', 'tanks', 'cylinder', 'cylinders'])) {
                $query->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(category)'), ['tank', 'tanks', 'cylinder', 'cylinders']);
                $category = 'tank';
            } else {
                $query->whereRaw('LOWER(category) = ?', [$catLower]);
            }
        }

        $products = $query->orderBy('name')->get();
        $activeCategory = $category;

        // Fetch distinct categories, normalize tank/tanks aliases, and exclude freebie
        $rawCategories = Product::where('is_active', true)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->where('price', '>', 0)
            ->whereRaw('LOWER(category) != ?', ['freebie'])
            ->pluck('category');

        $categories = $rawCategories
            ->map(function ($c) {
                $trimmed = strtolower(trim($c));
                if (in_array($trimmed, ['tank', 'tanks', 'cylinder', 'cylinders'])) {
                    return 'Tank';
                }
                return ucfirst($trimmed);
            })
            ->unique()
            ->values();

        return view('customer.product', compact('products', 'activeCategory', 'categories'));
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
        $products = Product::with(['inventory', 'orderItems'])
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
            'name'           => 'required|string|max:255',
            'category'       => 'required|in:tank,accessories,appliances,freebie',
            'requires_exchange'    => 'nullable|boolean',
            'description'    => 'nullable|string',
            'price'          => 'nullable|numeric|min:0',
            'cost_price'     => 'required_unless:category,freebie|nullable|numeric|min:0',
            'selling_price'  => 'required_unless:category,freebie|nullable|numeric|min:0',
            'stock'          => 'nullable|integer|min:0',
            'weight'         => 'nullable|string|max:255',
            'image'          => 'nullable|image|max:2048',
            'is_active'      => 'boolean',
        ]);

        $validated['category'] = strtolower((string) $validated['category']);
        $isTankCategory = in_array($validated['category'], ['tank', 'tanks', 'cylinder', 'cylinders']) || str_contains(strtolower($validated['name']), 'tank');
        if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'requires_exchange')) {
            $validated['requires_exchange'] = $request->has('requires_exchange') ? $request->boolean('requires_exchange') : $isTankCategory;
        } else {
            unset($validated['requires_exchange']);
        }
        $validated['is_active'] = $request->boolean('is_active');
        $validated['stock'] = (int) ($validated['stock'] ?? 0);

        if ($validated['category'] === 'freebie') {
            $validated['cost_price'] = (float) ($validated['cost_price'] ?? 0);
            $validated['selling_price'] = (float) ($validated['selling_price'] ?? 0);
            $validated['price'] = 0;
        } else {
            // If price is not provided, use selling_price as the price
            if (!isset($validated['price']) || is_null($validated['price'])) {
                $validated['price'] = $validated['selling_price'];
            }
        }

        if ($request->hasFile('image')) {
            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
            $validated['image'] = $request->file('image')->store('products', $disk);
        }

        // If category is freebie, save into freebies table
        if ($validated['category'] === 'freebie') {
            $freebie = Freebie::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'stock' => (int) ($validated['stock'] ?? 0),
                'category' => 'Promotional Gifts',
                'reward_points_required' => 0,
                'redemption_type' => 'promotional',
                'image' => $validated['image'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            $message = 'Freebie added to Freebies successfully.';

            if ($request->expectsJson()) {
                return response()->json(['success' => true, 'message' => $message, 'freebie_id' => $freebie->id], 200);
            }

            return redirect()->route('admin.products', ['tab' => 'freebies'])->with('success', $message);
        }

        DB::transaction(function () use ($validated) {
            $product = Product::create($validated);

            // Keep inventory synced with product stock on creation.
            $inventory = Inventory::updateOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity_on_hand' => (int) $validated['stock'],
                    'reorder_level' => 5,
                    'status' => 'active',
                ]
            );

            // Create stock movement record if initial stock is added
            if ((int) $validated['stock'] > 0) {
                try {
                    StockMovement::create([
                        'inventory_id' => $inventory->id,
                        'full_in' => (int) $validated['stock'],
                        'full_out' => 0,
                        'empty_in' => 0,
                        'empty_out' => 0,
                        'type' => 'stock_in',
                        'notes' => 'Initial stock - product created',
                        'movement_date' => now(),
                        'created_by' => Auth::check() ? Auth::id() : 1,
                    ]);
                } catch (\Exception $e) {
                    Log::error('StockMovement creation failed: ' . $e->getMessage());
                }
            }
        });

        $message = 'Product created successfully.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message], 200);
        }

        return redirect()->route('admin.products')->with('success', $message);
    }

    // Admin: update an existing product
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'category'       => 'required|in:tank,accessories,appliances,freebie',
            'requires_exchange'    => 'nullable|boolean',
            'description'    => 'nullable|string',
            'price'          => 'nullable|numeric|min:0',
            'cost_price'     => 'required_unless:category,freebie|nullable|numeric|min:0',
            'selling_price'  => 'required_unless:category,freebie|nullable|numeric|min:0',
            'stock'          => 'nullable|integer|min:0',
            'weight'         => 'nullable|string|max:255',
            'image'          => 'nullable|image|max:2048',
            'is_active'      => 'boolean',
        ]);

        $validated['category'] = strtolower((string) $validated['category']);
        $isTankCategory = in_array($validated['category'], ['tank', 'tanks', 'cylinder', 'cylinders']) || str_contains(strtolower($validated['name']), 'tank');
        if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'requires_exchange')) {
            $validated['requires_exchange'] = $request->has('requires_exchange') ? $request->boolean('requires_exchange') : ($product->requires_exchange ?? $isTankCategory);
        } else {
            unset($validated['requires_exchange']);
        }
        $validated['is_active'] = $request->boolean('is_active');

        if ($validated['category'] === 'freebie') {
            $freebie = Freebie::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'stock' => (int) ($validated['stock'] ?? 0),
                'category' => 'Promotional Gifts',
                'reward_points_required' => 0,
                'redemption_type' => 'promotional',
                'image' => $validated['image'] ?? $product->image,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            Inventory::where('product_id', $product->id)->delete();
            try {
                $product->delete();
            } catch (\Exception $e) {
                $product->update(['is_active' => false]);
            }

            $message = 'Item saved into Freebies successfully.';

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => $message, 'freebie_id' => $freebie->id], 200);
            }

            return redirect()->route('admin.products', ['tab' => 'freebies'])->with('success', $message);
        } else {
            // If price is not provided, use selling_price as the price
            if (!isset($validated['price']) || is_null($validated['price'])) {
                $validated['price'] = $validated['selling_price'];
            }
        }

        if ($request->hasFile('image')) {
            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
            $validated['image'] = $request->file('image')->store('products', $disk);
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
                $oldStock = $inventory->quantity_on_hand;
                $newStock = (int) $validated['stock'];
                $difference = $newStock - $oldStock;

                $inventory->update([
                    'quantity_on_hand' => $newStock,
                    'last_restocked' => Carbon::now(),
                ]);

                // Create stock movement record if there's a difference
                if ($difference != 0) {
                    $movementType = $difference > 0 ? 'stock_in' : 'stock_out';

                    try {
                        StockMovement::create([
                            'inventory_id' => $inventory->id,
                            'full_in' => $difference > 0 ? $difference : 0,
                            'full_out' => $difference < 0 ? abs($difference) : 0,
                            'empty_in' => 0,
                            'empty_out' => 0,
                            'type' => $movementType,
                            'notes' => $difference > 0 ? 'Stock adjustment - increased via product edit' : 'Stock adjustment - decreased via product edit',
                            'movement_date' => now(),
                            'created_by' => Auth::check() ? Auth::id() : 1,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('StockMovement creation failed: ' . $e->getMessage());
                    }
                }
            }
        });

        $message = 'Product updated successfully.';

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message], 200);
        }

        return redirect()->route('admin.products')->with('success', $message);
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

            return redirect()->route('admin.products', ['tab' => 'archived'])->with(
                'success',
                'Product is linked to existing orders and was moved to Archived Products instead of permanently deleted.'
            );
        }

        DB::transaction(function () use ($product) {
            $product->carts()->delete();
            $product->delete();
        });

        return redirect()->route('admin.products')->with('success', 'Product deleted permanently.');
    }

    // Admin: restore an archived product
    public function restore(Request $request, Product $product)
    {
        $stock = $request->filled('stock') ? max(0, (int) $request->input('stock')) : 0;

        DB::transaction(function () use ($product, $stock) {
            $product->update([
                'is_active' => true,
                'stock' => $stock,
            ]);

            $inventory = Inventory::firstOrCreate(
                ['product_id' => $product->id],
                [
                    'quantity_on_hand' => 0,
                    'reorder_level' => 5,
                    'status' => 'active',
                ]
            );

            $inventory->update([
                'quantity_on_hand' => $stock,
                'status' => 'active',
                'last_restocked' => $stock > 0 ? Carbon::now() : $inventory->last_restocked,
            ]);

            if ($stock > 0) {
                try {
                    StockMovement::create([
                        'inventory_id' => $inventory->id,
                        'full_in' => $stock,
                        'full_out' => 0,
                        'empty_in' => 0,
                        'empty_out' => 0,
                        'type' => 'stock_in',
                        'notes' => 'Stock initialized on product restoration',
                        'movement_date' => now(),
                        'created_by' => Auth::id() ?? 1,
                    ]);
                } catch (\Exception $e) {
                    Log::error('StockMovement restore creation failed: ' . $e->getMessage());
                }
            }
        });

        $message = "Product '{$product->name}' was restored successfully and is now active.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('admin.products', ['tab' => 'products'])->with('success', $message);
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
            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
            $validated['image'] = $request->file('image')->store('freebies', $disk);
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
            $disk = config('filesystems.default') === 's3' ? 's3' : 'public';
            $validated['image'] = $request->file('image')->store('freebies', $disk);
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

        return redirect()->route('admin.products', ['tab' => 'freebies'])->with('success', 'Freebie deleted successfully.');
    }

    // Admin: restore an archived freebie
    public function restoreFreebie(Request $request, Freebie $freebie)
    {
        $freebie->update(['is_active' => true]);

        $message = "Freebie '{$freebie->name}' was restored successfully.";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('admin.products', ['tab' => 'freebies'])->with('success', $message);
    }

    // Admin: adjust freebie stock
    public function adjustFreebieStock(Request $request, Freebie $freebie)
    {
        $validated = $request->validate([
            'quantity_change' => 'required|integer|min:1',
            'type' => 'required|in:stock_in,stock_out,damage,return',
            'notes' => 'nullable|string|max:255',
            'movement_date' => 'nullable|date',
        ]);

        $quantityChange = (int) $validated['quantity_change'];
        $type = $validated['type'];

        if (in_array($type, ['stock_in', 'return'], true)) {
            $freebie->increment('stock', $quantityChange);
        } else {
            $freebie->decrement('stock', $quantityChange);
        }

        $freebie->refresh();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Freebie stock updated successfully.',
                'stock' => $freebie->stock,
            ]);
        }

        return redirect()->route('admin.products', ['tab' => 'freebies'])
            ->with('success', 'Freebie stock updated successfully.');
    }
}
