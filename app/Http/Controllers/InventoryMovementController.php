<?php

namespace App\Http\Controllers;

use App\Models\InventoryMovement;
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
        $query = InventoryMovement::with('product', 'creator');

        // Filter by product
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by type (IN, OUT, ADJUSTMENT)
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

        // Filter by reference type
        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->reference_type);
        }

        // Order by movement_date DESC (most recent first)
        $movements = $query->orderByDesc('movement_date')
            ->paginate(50);

        // Get products for filter dropdown
        $products = Product::where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'id');

        // Get unique reference types
        $referenceTypes = InventoryMovement::distinct('reference_type')
            ->whereNotNull('reference_type')
            ->pluck('reference_type')
            ->sort();

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
        $query = InventoryMovement::with('product', 'creator');

        // Apply same filters as index
        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
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

        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->reference_type);
        }

        $movements = $query->orderByDesc('movement_date')->get();

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
                fputcsv($file, [
                    $movement->movement_date->format('Y-m-d H:i:s'),
                    $movement->product->name,
                    $movement->type,
                    $movement->quantity,
                    $movement->reference_type ?? '-',
                    $movement->reference_id ?? '-',
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
