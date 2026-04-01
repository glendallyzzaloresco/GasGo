<?php

namespace App\Http\Controllers\Admin;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Freebie;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class InventoryController extends Controller
{
    /**
     * Display all inventory records
     */
    public function index(Request $request)
    {
        $query = Inventory::with('product');

        // Use hardcoded categories
        $categories = collect(['Tank', 'Accessories', 'Appliance', 'Freebie']);

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

        // Filter by product category from DB
        if ($request->filled('category')) {
            $query = $query->whereHas('product', function ($q) use ($request) {
                $q->where('category', $request->category);
            });
        }

        // Get all inventories without pagination first for custom sorting
        $allInventories = $query->get();
        
        // Determine sorting method
        $sortBy = $request->input('sort_by', 'name_asc');
        
        $sorted = $allInventories->sortBy(function($item) use ($sortBy) {
            $name = strtolower($item->product->name ?? '');
            $category = strtolower($item->product->category ?? '');
            
            switch($sortBy) {
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
                    } elseif ($item->quantity_on_hand <= $item->reorder_level) {
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
        
        // Today's stock received (stock_in movements)
        $stockReceived = StockMovement::whereIn('type', ['stock_in'])
        ->whereBetween('movement_date', [$today, $todayEnd])
        ->with('inventory.product')
        ->orderBy('movement_date', 'desc')
        ->get();
        
        // Today's total stock received
        $totalStockReceived = $stockReceived->sum('quantity_change');
        
        // Get current empty tanks count for tank products with latest delivery date
        $deliveryDates = \Illuminate\Support\Facades\DB::table('deliveries')
            ->join('orders', 'deliveries.order_id', '=', 'orders.id')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('deliveries.status', 'delivered')
            ->where('products.category', 'Tank')
            ->selectRaw('products.id as product_id, MAX(deliveries.updated_at) as latest_delivery_date')
            ->groupBy('products.id')
            ->pluck('latest_delivery_date', 'product_id');
        
        $emptyTanksReturned = Inventory::whereHas('product', function($q) {
            $q->where('category', 'Tank');
        })
        ->where('empty_on_hand', '>', 0)
        ->with('product')
        ->orderBy('empty_on_hand', 'desc')
        ->get();
        
        // Attach delivery dates to each inventory record
        $emptyTanksReturned = $emptyTanksReturned->map(function($inv) use ($deliveryDates) {
            $inv->delivery_date = $deliveryDates[$inv->product_id] ?? null;
            return $inv;
        });
        
        // Total empty tanks
        $totalEmptyReturned = $emptyTanksReturned->sum('empty_on_hand');

        return view('admin.inventory.index', compact('inventories', 'categories', 'freebies', 'emptyTanksReturned', 'stockReceived', 'totalEmptyReturned', 'totalStockReceived'));
    }

    /**
     * Show inventory details and history
     */
    public function show(Inventory $inventory)
    {
        $inventory->load('product', 'movements.creator');
        // Sort by movement_date (DESC) instead of created_at for accurate audit trail
        $movements = $inventory->movements()->orderBy('movement_date', 'desc')->paginate(15);

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
            'reorder_level' => 'required|integer|min:0',
            'supplier' => 'nullable|string|max:255',
            'status' => 'required|in:active,discontinued,damaged',
            'expiry_date' => 'nullable|date|after:today',
            'batch_number' => 'nullable|string|max:255',
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
        // Get raw input first
        $movementDateRaw = $request->input('movement_date');
        
        $validated = $request->validate([
            'quantity_change' => 'required|integer|min:1',
            'type' => 'required|in:stock_in,stock_out,sale,damage,return',
            'notes' => 'nullable|string|max:255',
            'movement_date' => 'nullable|string', // Accept raw string format
        ]);

        // Convert datetime-local format (YYYY-MM-DDTHH:MM) to Y-m-d H:i:s
        if ($movementDateRaw && !empty($movementDateRaw)) {
            try {
                // Replace T with space to convert from datetime-local format
                $movementDateFormatted = str_replace('T', ' ', $movementDateRaw);
                $movementDate = Carbon::createFromFormat('Y-m-d H:i', $movementDateFormatted);
            } catch (\Exception $e) {
                $movementDate = now();
            }
        } else {
            $movementDate = now();
        }
        
        $quantityChange = $validated['quantity_change'];
        $type = $validated['type'];

        // For Stock In: add to inventory, update last_restocked
        // For Stock Out types: subtract from inventory
        if ($type === 'stock_in') {
            $inventory->increment('quantity_on_hand', $quantityChange);
            $inventory->update(['last_restocked' => $movementDate]);
        } else {
            // Stock Out: subtract quantity
            $inventory->decrement('quantity_on_hand', $quantityChange);
        }

        // Record movement with positive quantity for tracking
        StockMovement::create([
            'inventory_id' => $inventory->id,
            'quantity_change' => $type === 'stock_in' ? $quantityChange : -$quantityChange,
            'type' => $type,
            'notes' => $validated['notes'],
            'movement_date' => $movementDate,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Stock adjusted successfully. Movement recorded.');
    }

    /**
     * Show reorder report
     */
    public function reorderReport()
    {
        $lowStockItems = Inventory::with('product')
            ->whereRaw('quantity_on_hand <= reorder_level')
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
            ->where('expiry_date', '<', now())
            ->where('status', '!=', 'discontinued')
            ->orderBy('expiry_date')
            ->get();

        $upcomingExpiry = Inventory::with('product')
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
        $inventories = Inventory::with('product')->get();

        $csv = "Product Name,SKU,Current Stock,Reorder Level,Status,Supplier,Expiry Date,Last Restocked\n";

        foreach ($inventories as $inv) {
            $csv .= sprintf(
                '"%s","%s",%d,%d,"%s","%s","%s","%s"' . "\n",
                $inv->product->name,
                $inv->product->sku ?? 'N/A',
                $inv->quantity_on_hand,
                $inv->reorder_level,
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
