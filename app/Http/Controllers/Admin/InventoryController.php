<?php

namespace App\Http\Controllers\Admin;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Freebie;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class InventoryController extends Controller
{
    /**
     * Display all inventory records
     */
    public function index(Request $request)
    {
        // Auto-ensure all active products have an inventory record
        $productsWithoutInventory = Product::whereDoesntHave('inventory')
            ->where('is_active', true)
            ->where('price', '>', 0)
            ->get();

        foreach ($productsWithoutInventory as $productWithoutInv) {
            Inventory::firstOrCreate(
                ['product_id' => $productWithoutInv->id],
                [
                    'quantity_on_hand' => (int) ($productWithoutInv->stock ?? 0),
                    'empty_on_hand' => 0,
                    'status' => 'active',
                ]
            );
        }

        $query = Inventory::with(['product.categoryModel'])
            ->whereHas('product', function ($q) {
                $q->where('is_active', true)
                    ->where('price', '>', 0);
            })
            ->where('status', '!=', 'discontinued');

        // Use normalized categories for filtering/display controls
        $categories = \App\Models\Category::where('is_active', true)->orderBy('name')->pluck('slug');

        // Filter by stock status (In Stock / Out of Stock)
        if ($request->filled('status')) {
            if ($request->status === 'in_stock') {
                $query = $query->whereRaw('quantity_on_hand > 0');
            } elseif ($request->status === 'out_of_stock') {
                $query = $query->whereRaw('quantity_on_hand = 0');
            }
        }

        // Search by product name
        if ($request->filled('search')) {
            $query = $query->whereHas('product', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        // Filter by product category from DB (normalized, with appliance aliases)
        if ($request->filled('category')) {
            $normalizedCategory = strtolower(trim((string) $request->category));
            $targetSlug = match ($normalizedCategory) {
                'tank', 'tanks', 'cylinder', 'cylinders' => 'lpg-tanks',
                'accessories', 'accessory' => 'accessories',
                'appliances', 'appliance' => 'appliances',
                default => \Illuminate\Support\Str::slug($normalizedCategory),
            };

            $matchedCategory = \App\Models\Category::where('slug', $targetSlug)
                ->orWhereRaw('LOWER(name) = ?', [$normalizedCategory])
                ->first();

            if ($matchedCategory) {
                $query = $query->whereHas('product', function ($q) use ($matchedCategory) {
                    $q->where('category_id', $matchedCategory->id);
                });
            }
        }

        // Get all inventories without pagination first for custom sorting
        $allInventories = $query->get();

        // Determine sorting method
        $sortBy = $request->input('sort_by', 'name_asc');

        $sorted = $allInventories->sortBy(function ($item) use ($sortBy) {
            $name = strtolower($item->product->name ?? '');
            $category = strtolower($item->product->category ?? '');

            switch ($sortBy) {
                case 'name_asc':
                    return $name;

                case 'name_desc':
                    return chr(255) . $name;

                case 'category_asc':
                    return $category . '|' . $name;

                case 'category_desc':
                    return chr(255) . $category . '|' . chr(255) . $name;

                case 'stock_high':
                    return str_pad(999999 - $item->quantity_on_hand, 10, "0", STR_PAD_LEFT);

                case 'stock_low':
                    return str_pad($item->quantity_on_hand, 10, "0", STR_PAD_LEFT);

                case 'lastrestocked_new':
                    return $item->last_restocked ? chr(255) . $item->last_restocked->format('Y-m-d H:i:s') : 'ZZZ';

                case 'lastrestocked_old':
                    return $item->last_restocked ? $item->last_restocked->format('Y-m-d H:i:s') : 'AAA';

                case 'stock_level':
                    // Custom method: In Stock, Low Stock, Out of Stock
                    if ($item->quantity_on_hand == 0) {
                        return "C_out";
                    } elseif ($item->quantity_on_hand <= 5) {
                        return "B_low";
                    }
                    return "A_ok";

                default:
                    return $name;
            }
        })->values();

        // Paginate the sorted collection
        $page = request('page', 1);
        $perPage = 20;
        $inventories = new LengthAwarePaginator(
            $sorted->forPage($page, $perPage),
            $sorted->count(),
            $perPage,
            $page,
            [
                'path' => route('admin.inventory.index'),
                'query' => request()->query(),
            ]
        );

        $freebies = Freebie::where('is_active', true)->get();

        // Get today's summary data
        $today = Carbon::now()->startOfDay();
        $todayEnd = Carbon::now()->endOfDay();

        // Today's stock received (include historical purchase aliases; fallback to created_at if movement_date is missing)
        $stockReceived = StockMovement::whereIn('type', ['stock_in', 'purchase'])
            ->whereBetween(
                \Illuminate\Support\Facades\DB::raw('COALESCE(movement_date, created_at)'),
                [$today, $todayEnd]
            )
            ->with('inventory.product')
            ->orderByRaw('COALESCE(movement_date, created_at) DESC')
            ->get();

        // Today's total stock received
        $totalStockReceived = $stockReceived->sum('full_in');

        $tankMovementQuery = StockMovement::query()
            ->whereHas('inventory.product', function ($query) {
                $query->cylinders();
            })
            ->whereBetween(DB::raw('COALESCE(movement_date, created_at)'), [$today, $todayEnd]);

        $dailyMovementTotals = (clone $tankMovementQuery)
            ->selectRaw('COALESCE(SUM(full_in), 0) as full_in')
            ->selectRaw('COALESCE(SUM(full_out), 0) as full_out')
            ->selectRaw('COALESCE(SUM(empty_in), 0) as empty_in')
            ->selectRaw('COALESCE(SUM(empty_out), 0) as empty_out')
            ->first();

        // Recent stock movement history across all products (for inventory page overview)
        $movementsQuery = StockMovement::with(['inventory.product', 'creator'])
            ->orderByRaw('COALESCE(movement_date, created_at) DESC');

        // Apply movement history filters
        if ($request->filled('movement_date_from')) {
            $movementsQuery->whereDate('movement_date', '>=', $request->input('movement_date_from'));
        }

        if ($request->filled('movement_date_to')) {
            $movementsQuery->whereDate('movement_date', '<=', $request->input('movement_date_to'));
        }

        if ($request->filled('movement_type')) {
            $movementsQuery->where('type', $request->input('movement_type'));
        }

        if ($request->filled('movement_search')) {
            $search = '%' . $request->input('movement_search') . '%';
            $movementsQuery->where(function ($q) use ($search) {
                $q->where('reference', 'like', $search)
                    ->orWhere('notes', 'like', $search);
            });
        }

        $recentStockMovements = $movementsQuery->limit(20)->get();

        // Get current empty tanks count for cylinder products with latest delivery date
        $deliveryDates = \Illuminate\Support\Facades\DB::table('deliveries')
            ->join('orders', 'deliveries.order_id', '=', 'orders.id')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->where('deliveries.status', 'delivered')
            ->where(function ($q) {
                if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'requires_exchange')) {
                    $q->where('products.requires_exchange', true);
                }
                $q->orWhere('products.category_id', 1)
                    ->orWhere('categories.slug', 'lpg-tanks')
                    ->orWhereRaw('LOWER(categories.name) IN (?, ?, ?, ?)', ['tank', 'tanks', 'cylinder', 'cylinders'])
                    ->orWhere('products.name', 'LIKE', '%Tank%')
                    ->orWhere('products.name', 'LIKE', '%Cylinder%');
            })
            ->where(function ($q) {
                $q->whereNull('categories.slug')
                    ->orWhereNotIn('categories.slug', ['accessories', 'appliances', 'freebie']);
            })
            ->where(function ($q) {
                $q->where('products.name', 'NOT LIKE', '%Regulator%')
                    ->where('products.name', 'NOT LIKE', '%Hose%')
                    ->where('products.name', 'NOT LIKE', '%Clamp%')
                    ->where('products.name', 'NOT LIKE', '%Stove%')
                    ->where('products.name', 'NOT LIKE', '%Burner%')
                    ->where('products.name', 'NOT LIKE', '%Hanger%')
                    ->where('products.name', 'NOT LIKE', '%Paste%');
            })
            ->selectRaw('products.id as product_id, MAX(deliveries.updated_at) as latest_delivery_date')
            ->groupBy('products.id')
            ->pluck('latest_delivery_date', 'product_id');

        $emptyTanksReturned = Inventory::whereHas('product', function ($q) {
            $q->cylinders()->where('is_active', true);
        })
            ->where('status', '!=', 'discontinued')
            ->where('empty_on_hand', '>', 0)
            ->with('product')
            ->orderBy('empty_on_hand', 'desc')
            ->get();

        // Attach delivery dates to each inventory record
        $emptyTanksReturned = $emptyTanksReturned->map(function ($inv) use ($deliveryDates) {
            $inv->delivery_date = $deliveryDates[$inv->product_id] ?? null;
            return $inv;
        });

        // Total empty tanks
        $totalEmptyReturned = $emptyTanksReturned->sum('empty_on_hand');

        // Date filter for empty tank returns (show actual inventory.empty_on_hand)
        $selectedEmptyDate = $request->input('empty_date');

        $emptyTankReturnsQuery = Inventory::with('product')
            ->whereHas('product', function ($q) {
                $q->cylinders()->where('is_active', true);
            })
            ->where('status', '!=', 'discontinued')
            ->where('empty_on_hand', '>', 0);

        if (!empty($selectedEmptyDate)) {
            try {
                $normalizedDate = Carbon::parse($selectedEmptyDate)->toDateString();
                $selectedEmptyDate = $normalizedDate;

                // Filter by deliveries that were updated on the selected date
                $emptyTankReturnsQuery->whereHas('product.orders', function ($q) use ($normalizedDate) {
                    $q->whereHas('delivery', function ($dq) use ($normalizedDate) {
                        $dq->where('status', 'delivered')
                            ->whereDate('updated_at', $normalizedDate);
                    });
                });
            } catch (\Exception $e) {
                $selectedEmptyDate = null;
            }
        }

        $emptyTankReturnsByDate = $emptyTankReturnsQuery
            ->orderBy('empty_on_hand', 'desc')
            ->get()
            ->map(function ($inv) {
                return (object) [
                    'product_id' => $inv->product_id,
                    'product_name' => $inv->product->name,
                    'returned_qty' => $inv->empty_on_hand,  // Use actual empty_on_hand count
                    'latest_delivery_date' => $inv->delivery_date ?? \Illuminate\Support\Facades\DB::table('deliveries')
                        ->join('orders', 'deliveries.order_id', '=', 'orders.id')
                        ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                        ->where('deliveries.status', 'delivered')
                        ->where('order_items.product_id', $inv->product_id)
                        ->max('deliveries.updated_at'),
                ];
            });

        $totalEmptyReturnedByDate = (int) $emptyTankReturnsByDate->sum('returned_qty');

        return view('admin.inventory.index', compact(
            'inventories',
            'categories',
            'freebies',
            'emptyTanksReturned',
            'stockReceived',
            'dailyMovementTotals',
            'recentStockMovements',
            'emptyTankReturnsByDate',
            'selectedEmptyDate',
            'totalEmptyReturnedByDate',
            'totalEmptyReturned',
            'totalStockReceived'
        ));
    }

    /**
     * Show inventory details and history
     */
    public function show(Inventory $inventory, Request $request)
    {
        $inventory->load('product');

        // Build query for movements with filters
        $movementsQuery = $inventory->movements()->with('creator');

        // Filter by date range
        if ($request->filled('movement_date_from')) {
            $movementsQuery->whereDate('movement_date', '>=', $request->input('movement_date_from'));
        }

        if ($request->filled('movement_date_to')) {
            $movementsQuery->whereDate('movement_date', '<=', $request->input('movement_date_to'));
        }

        // Filter by type
        if ($request->filled('movement_type')) {
            $movementsQuery->where('type', $request->input('movement_type'));
        }

        // Get movements ordered by date descending
        $movements = $movementsQuery->orderBy('movement_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.inventory.show', compact('inventory', 'movements'));
    }

    /**
     * Show form to edit inventory
     */
    public function edit(Inventory $inventory)
    {
        return view('admin.inventory.edit', compact('inventory'));
    }

    /**
     * Update inventory record
     */
    public function update(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'supplier' => 'nullable|string|max:255',
            'status' => 'required|in:active,discontinued,damaged',
            'expiry_date' => 'nullable|date|after:today',
        ]);

        // Update only settings fields - never update quantity_on_hand here
        // All stock changes must go through the adjust() method
        $inventory->update($validated);

        return redirect()->route('admin.inventory.show', $inventory)
            ->with('success', 'Inventory settings updated successfully.');
    }

    /**
     * Adjust stock quantity
     */
    public function adjust(Request $request, Inventory $inventory)
    {
        $validated = $request->validate([
            'quantity_change' => 'required|integer|min:1',
            'type' => 'required|in:stock_in,stock_out,empty_in,empty_out,sale,damage,return',
            'reference' => 'required|string|max:100',
            'notes' => 'required|string|max:255',
            'supplier' => 'nullable|string|max:255',
            'movement_date' => 'nullable|string',
        ]);

        $isCylinderProduct = $inventory->product?->isCylinder() ? true : false;

        // Always use server's current time for consistency
        $movementDate = now();

        $quantityChange = (int) $validated['quantity_change'];
        $type = $validated['type'];

        DB::transaction(function () use ($inventory, $type, $quantityChange, $movementDate, $validated, $isCylinderProduct) {
            $inventory = Inventory::whereKey($inventory->id)->lockForUpdate()->firstOrFail();

            if (isset($validated['supplier']) && trim((string) $validated['supplier']) !== '') {
                $inventory->supplier = trim((string) $validated['supplier']);
            }

            $fullIn = 0;
            $fullOut = 0;
            $emptyIn = 0;
            $emptyOut = 0;

            if ($type === 'stock_in') {
                $inventory->increment('quantity_on_hand', $quantityChange);
                $inventory->update(['last_restocked' => $movementDate]);
                $fullIn = $quantityChange;
            } elseif ($type === 'stock_out') {
                if ((int) $inventory->quantity_on_hand < $quantityChange) {
                    throw new \RuntimeException('Insufficient stock for this adjustment.');
                }
                $inventory->decrement('quantity_on_hand', $quantityChange);
                $fullOut = $quantityChange;
            } elseif ($type === 'empty_in') {
                if (!$isCylinderProduct) {
                    throw new \RuntimeException('Empty cylinder tracking only applies to cylinders.');
                }
                $inventory->increment('empty_on_hand', $quantityChange);
                $emptyIn = $quantityChange;
            } elseif ($type === 'empty_out') {
                if (!$isCylinderProduct) {
                    throw new \RuntimeException('Empty cylinder tracking only applies to cylinders.');
                }
                if ((int) $inventory->empty_on_hand < $quantityChange) {
                    throw new \RuntimeException('Insufficient empty cylinders on hand for this adjustment.');
                }
                $inventory->decrement('empty_on_hand', $quantityChange);
                $emptyOut = $quantityChange;
            } elseif ($type === 'return') {
                $inventory->increment('quantity_on_hand', $quantityChange);
                $fullIn = $quantityChange;
            } elseif ($type === 'damage') {
                if ((int) $inventory->quantity_on_hand < $quantityChange) {
                    throw new \RuntimeException('Insufficient stock for this adjustment.');
                }
                $inventory->decrement('quantity_on_hand', $quantityChange);
                $fullOut = $quantityChange;
            } elseif ($type === 'sale') {
                if ((int) $inventory->quantity_on_hand < $quantityChange) {
                    throw new \RuntimeException('Insufficient stock for this adjustment.');
                }
                $inventory->decrement('quantity_on_hand', $quantityChange);
                $fullOut = $quantityChange;
                if ($isCylinderProduct) {
                    $inventory->increment('empty_on_hand', $quantityChange);
                    $emptyIn = $quantityChange;
                }
            }

            if (isset($validated['supplier']) && trim((string) $validated['supplier']) !== '') {
                $inventory->save();
            }

            StockMovement::create([
                'inventory_id' => $inventory->id,
                'full_in' => $fullIn,
                'full_out' => $fullOut,
                'empty_in' => $emptyIn,
                'empty_out' => $emptyOut,
                'type' => $type,
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
                'movement_date' => $movementDate,
                'created_by' => Auth::id() ?? 1,
            ]);
        });

        return back()->with('success', 'Stock adjusted successfully. Movement recorded.');
    }


    public function markCylinderReturned(Request $request, \App\Models\StockMovement $movement)
    {
        $user = Auth::user();

        // Only allow admin users
        if (!$user || $user->role !== 'admin') {
            abort(403, 'Unauthorized');
        }

        // Ensure this movement refers to a sale and mentions new_cylinder
        $notes = strtolower((string) $movement->notes);
        if (str_contains($notes, 'new_cylinder') === false && str_contains($notes, 'new cylinder') === false) {
            return redirect()->back()->with('error', 'This movement is not a new cylinder delivery.');
        }

        $inventory = $movement->inventory;
        if (!$inventory || !$inventory->product?->isCylinder()) {
            return redirect()->back()->with('error', 'Return tracking only applies to cylinder products.');
        }

        // Determine quantity that was sent as full_out for this movement
        $quantity = (int) max(0, $movement->full_out ?? 0);
        if ($quantity <= 0) {
            return redirect()->back()->with('error', 'No quantity found to mark as returned.');
        }

        // Idempotency: do not create duplicate return records for same reference
        $existingReturn = \App\Models\StockMovement::query()
            ->where('inventory_id', $inventory->id)
            ->where(function ($q) use ($movement) {
                $q->where('reference', $movement->reference)
                    ->orWhere('notes', 'like', '%returned for ' . $movement->reference . '%');
            })
            ->where('empty_in', '>', 0)
            ->exists();

        if ($existingReturn) {
            return redirect()->back()->with('info', 'Return already recorded for this delivery.');
        }

        DB::transaction(function () use ($inventory, $quantity, $movement, $user) {
            // Update inventory empty_on_hand (customer returned empties)
            $inventory->increment('empty_on_hand', $quantity);

            // Create stock movement for the returned empty cylinders
            \App\Models\StockMovement::create([
                'inventory_id' => $inventory->id,
                'full_in' => 0,
                'full_out' => 0,
                'empty_in' => $quantity,
                'empty_out' => 0,
                'type' => 'return',
                'reference' => $movement->reference,
                'notes' => 'Empty returned for ' . ($movement->reference ?? 'order'),
                'movement_date' => now(),
                'created_by' => $user->id,
            ]);
        });

        return redirect()->back()->with('success', 'Marked returned: ' . $quantity . ' empty cylinder(s).');
    }

    /**
     * Show reorder report
     */
    public function reorderReport()
    {
        $lowStockItems = Inventory::with('product')
            ->whereHas('product', function ($q) {
                $q->where('is_active', true);
            })
            ->where('status', '!=', 'discontinued')
            ->where('quantity_on_hand', '<=', 5)
            ->orderBy('quantity_on_hand')
            ->get();

        return view('admin.inventory.reorder-report', compact('lowStockItems'));
    }

    /**
     * Show expiry report
     */
    public function expiryReport()
    {
        $expiredItems = Inventory::with('product')
            ->whereHas('product', function ($q) {
                $q->where('is_active', true);
            })
            ->where('expiry_date', '<', now())
            ->where('status', '!=', 'discontinued')
            ->orderBy('expiry_date')
            ->get();

        $upcomingExpiry = Inventory::with('product')
            ->whereHas('product', function ($q) {
                $q->where('is_active', true);
            })
            ->whereBetween('expiry_date', [now(), now()->addMonths(1)])
            ->where('status', '!=', 'discontinued')
            ->orderBy('expiry_date')
            ->get();

        return view('admin.inventory.expiry-report', compact('expiredItems', 'upcomingExpiry'));
    }

    /**
     * Download inventory report as CSV
     */
    public function exportCsv()
    {
        $inventories = Inventory::with('product')
            ->whereHas('product', function ($q) {
                $q->where('is_active', true);
            })
            ->where('status', '!=', 'discontinued')
            ->get();

        $csv = "Product Name,SKU,Current Stock,Reorder Level,Status,Supplier,Expiry Date,Last Restocked\n";

        foreach ($inventories as $inv) {
            $csv .= sprintf(
                '"%s","%s",%d,"%s","%s","%s","%s"' . "\n",
                $inv->product->name,
                $inv->product->sku ?? 'N/A',
                $inv->quantity_on_hand,
                $inv->status,
                $inv->supplier ?? 'N/A',
                $inv->expiry_date ?? 'N/A',
                $inv->last_restocked ?? 'N/A'
            );
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inventory-' . date('Y-m-d') . '.csv"',
        ]);
    }
}
