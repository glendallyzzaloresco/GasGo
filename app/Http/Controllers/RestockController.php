<?php

namespace App\Http\Controllers;

use App\Models\Restock;
use App\Models\RestockItem;
use App\Models\Inventory;
use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RestockController extends Controller
{
    /**
     * Display a listing of all restocks.
     */
    public function index()
    {
        $restocks = Restock::with('creator', 'items.product')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.restock.index', compact('restocks'));
    }

    /**
     * Show the form for creating a new restock.
     */
    public function create()
    {
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.restock.create', compact('products'));
    }

    /**
     * Store a newly created restock in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $restock = Restock::create([
            'supplier_name' => $validated['supplier_name'],
            'status' => 'DRAFT',
            'created_by' => Auth::id(),
        ]);

        // Add restock items
        foreach ($validated['items'] as $item) {
            RestockItem::create([
                'restock_id' => $restock->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        return redirect()->route('admin.restock.show', $restock)
            ->with('success', 'Restock created successfully.');
    }

    /**
     * Display the specified restock.
     */
    public function show(Restock $restock)
    {
        $restock->load('creator', 'items.product', 'movements');

        return view('admin.restock.show', compact('restock'));
    }

    /**
     * Show the form for editing the specified restock.
     */
    public function edit(Restock $restock)
    {
        if ($restock->status === 'RECEIVED') {
            abort(403, 'Cannot edit a received restock.');
        }

        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->get();

        $restock->load('items');

        return view('admin.restock.edit', compact('restock', 'products'));
    }

    /**
     * Update the specified restock in storage.
     */
    public function update(Request $request, Restock $restock)
    {
        if ($restock->status === 'RECEIVED') {
            abort(403, 'Cannot edit a received restock.');
        }

        $validated = $request->validate([
            'supplier_name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $restock->update([
            'supplier_name' => $validated['supplier_name'],
        ]);

        // Delete existing items and recreate
        $restock->items()->delete();

        foreach ($validated['items'] as $item) {
            RestockItem::create([
                'restock_id' => $restock->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
            ]);
        }

        return redirect()->route('admin.restock.show', $restock)
            ->with('success', 'Restock updated successfully.');
    }

    /**
     * Mark restock as received and create inventory movements.
     */
    public function markReceived(Request $request, Restock $restock)
    {
        if ($restock->status === 'RECEIVED') {
            return response()->json([
                'message' => 'Restock already marked as received.',
                'status' => 'warning',
            ], 422);
        }

        try {
            DB::transaction(function () use ($restock) {
                $restock->loadMissing('items');

                foreach ($restock->items as $item) {
                    $inventory = Inventory::where('product_id', $item->product_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$inventory) {
                        throw new \RuntimeException("Inventory record missing for product ID {$item->product_id}.");
                    }

                    $quantity = (int) $item->quantity;
                    $inventory->increment('quantity_on_hand', $quantity);
                    $inventory->update(['last_restocked' => now()]);

                    if (!$inventory->supportsEmptyCylinderTracking()) {
                        $inventory->forceFill(['empty_on_hand' => $inventory->empty_on_hand]);
                    }

                    StockMovement::create([
                        'inventory_id' => $inventory->id,
                        'full_in' => $quantity,
                        'full_out' => 0,
                        'empty_in' => 0,
                        'empty_out' => 0,
                        'type' => 'stock_in',
                        'reference' => 'RST-' . $restock->id,
                        'notes' => "Restock received from {$restock->supplier_name}",
                        'movement_date' => now(),
                        'created_by' => Auth::id(),
                    ]);
                }

                $restock->update([
                    'status' => 'RECEIVED',
                    'received_at' => now(),
                ]);
            });

            return response()->json([
                'message' => 'Restock marked as received and inventory updated.',
                'status' => 'success',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error marking restock as received: ' . $e->getMessage(),
                'status' => 'error',
            ], 500);
        }
    }

    /**
     * Delete the specified restock.
     */
    public function destroy(Request $request, Restock $restock)
    {
        if ($restock->status === 'RECEIVED') {
            abort(403, 'Cannot delete a received restock.');
        }

        $restock->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Restock deleted successfully.',
            ]);
        }

        return redirect()->route('admin.restock.index')
            ->with('success', 'Restock deleted successfully.');
    }
}
