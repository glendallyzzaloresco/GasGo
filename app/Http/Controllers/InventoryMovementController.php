<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InventoryMovementController extends Controller
{
    /**
     * Display inventory movements (admin ledger).
     */
    public function index(Request $request)
    {
        $query = StockMovement::with('inventory.product', 'creator');

        // Filter by product
        if ($request->filled('product_id')) {
            $productId = (int) $request->product_id;
            $query->whereHas('inventory', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        }

        // Filter by movement type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $dateFrom = Carbon::parse($request->date_from)->startOfDay();
            $query->where('movement_date', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = Carbon::parse($request->date_to)->endOfDay();
            $query->where('movement_date', '<=', $dateTo);
        }

        // Order by movement_date DESC (most recent first)
        $movements = $query->orderByRaw('COALESCE(movement_date, created_at) DESC')
            ->paginate(50);

        // Get products for filter dropdown
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        // StockMovement does not use reference_type; keep empty collection for backward-compatible view data
        $referenceTypes = collect();

        return view('admin.inventory.movements', compact(
            'movements',
            'products',
            'referenceTypes'
        ));
    }

    /**
     * Export movements as CSV.
     */
    public function export(Request $request)
    {
        $query = StockMovement::with('inventory.product', 'creator');

        // Apply same filters as index
        if ($request->filled('product_id')) {
            $productId = (int) $request->product_id;
            $query->whereHas('inventory', function ($q) use ($productId) {
                $q->where('product_id', $productId);
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $dateFrom = Carbon::parse($request->date_from)->startOfDay();
            $query->where('movement_date', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = Carbon::parse($request->date_to)->endOfDay();
            $query->where('movement_date', '<=', $dateTo);
        }

        $movements = $query->orderByRaw('COALESCE(movement_date, created_at) DESC')->get();

        // Generate CSV
        $fileName = 'inventory_movements_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
        ];

        $callback = function () use ($movements) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, [
                'Date/Time',
                'Product',
                'Type',
                'Quantity',
                'Reference Type',
                'Reference ID',
                'Notes',
                'Created By',
            ]);

            // Data
            foreach ($movements as $movement) {
                $movementDate = $movement->movement_date ?? $movement->created_at;
                fputcsv($file, [
                    $movementDate?->format('Y-m-d H:i:s'),
                    $movement->inventory->product->name ?? '-',
                    $movement->type,
                    $movement->quantity_change,
                    '-',
                    '-',
                    $movement->notes ?? '-',
                    $movement->creator?->name ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get product stock summary for dashboard.
     */
    public function stockSummary()
    {
        $products = Product::where('is_active', true)
            ->orderBy('stock', 'asc')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'stock' => $product->stock,
                    'is_low' => $product->stock < 5, // Consider as low stock if < 5
                ];
            });

        return response()->json($products);
    }
}
