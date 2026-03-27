@extends('layouts.admin')

@section('title', 'Reorder Report - Low Stock Items')

@section('styles')
<style>
    .report-header {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        padding: 30px;
        border-radius: 12px;
        margin-bottom: 30px;
    }
    
    .report-table {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .table {
        margin: 0;
    }
    
    .table th {
        background: #f8f9fa;
        font-weight: 600;
        color: #c0392b;
        border: none;
        padding: 14px !important;
    }
    
    .table td {
        padding: 14px !important;
        vertical-align: middle;
        border-color: #f0f0f0;
    }
    
    .table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .urgency-critical {
        background: #ffe5e5;
        border-left: 4px solid #e74c3c;
    }
    
    .urgency-critical td:first-child {
        color: #c0392b;
        font-weight: 600;
    }
    
    .quantity-low {
        color: #e74c3c;
        font-weight: 700;
        font-size: 1.1rem;
    }
    
    .deficit-badge {
        background: #ffe5e5;
        color: #c0392b;
        padding: 6px 12px;
        border-radius: 6px;
        font-weight: 600;
        display: inline-block;
    }
    
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .summary-card {
        background: white;
        padding: 20px;
        border-radius: 8px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        border-top: 4px solid var(--gasgo-blue);
    }
    
    .summary-card.critical {
        border-top-color: #e74c3c;
    }
    
    .summary-card .number {
        font-size: 2.5rem;
        font-weight: 700;
        margin: 10px 0;
    }
    
    .summary-card.critical .number {
        color: #e74c3c;
    }
    
    .summary-card .label {
        color: #666;
        font-size: 0.9rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">
    <div class="report-header">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="mb-2"><i class="fas fa-exclamation-circle me-2"></i>Reorder Report</h1>
                <p class="mb-0">Products that have fallen below their reorder level</p>
            </div>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Back to Inventory
            </a>
        </div>
    </div>

    @php
        $lowStockItems = \App\Models\Inventory::with('product')
            ->whereRaw('quantity_on_hand <= reorder_level')
            ->orderBy('quantity_on_hand', 'asc')
            ->get();
    @endphp

    @if($lowStockItems->isNotEmpty())
        <div class="summary-cards">
            <div class="summary-card critical">
                <i class="fas fa-exclamation-triangle" style="font-size: 2rem; color: #e74c3c;"></i>
                <div class="number">{{ $lowStockItems->count() }}</div>
                <div class="label">Products Below Reorder Level</div>
            </div>
            
            <div class="summary-card">
                <i class="fas fa-cube" style="font-size: 2rem; color: var(--gasgo-blue);"></i>
                <div class="number">{{ $lowStockItems->sum('quantity_on_hand') }}</div>
                <div class="label">Total Units Available</div>
            </div>
            
            <div class="summary-card">
                <i class="fas fa-layer-group" style="font-size: 2rem; color: #f39c12;"></i>
                <div class="number">{{ $lowStockItems->sum('reorder_level') }}</div>
                <div class="label">Total Reorder Needed</div>
            </div>
        </div>

        <div class="report-table">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th class="text-center">Current Stock</th>
                        <th class="text-center">Reorder Level</th>
                        <th class="text-center">Shortage</th>
                        <th>Supplier</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($lowStockItems as $item)
                        @php
                            $shortage = $item->reorder_level - $item->quantity_on_hand;
                            $percentage = ($item->quantity_on_hand / $item->reorder_level) * 100;
                        @endphp
                        <tr class="urgency-critical">
                            <td>
                                <strong>{{ $item->product->name }}</strong><br>
                                <small class="text-muted">SKU: {{ $item->product->sku ?? 'N/A' }}</small>
                            </td>
                            <td class="text-center">
                                <span class="quantity-low">{{ $item->quantity_on_hand }}</span>
                            </td>
                            <td class="text-center">{{ $item->reorder_level }}</td>
                            <td class="text-center">
                                <span class="deficit-badge">
                                    <i class="fas fa-arrow-up me-1"></i>{{ $shortage }} units
                                </span>
                            </td>
                            <td>{{ $item->supplier ?? '<span class="text-muted">-</span>' }}</td>
                            <td class="text-center">
                                <a href="{{ route('admin.inventory.show', $item) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                                <a href="{{ route('admin.inventory.edit', $item) }}" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 p-3" style="background: #f8f9fa; border-radius: 8px; border-left: 4px solid #f39c12;">
            <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Recommended Action</h6>
            <p class="mb-0 text-muted">
                Review suppliers and place purchase orders for all items below reorder level. 
                Use the "Edit" button to quickly update stock levels or adjust reorder thresholds.
            </p>
        </div>
    @else
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <strong>No Low Stock Items!</strong> All products are above their reorder levels.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        
        <a href="{{ route('admin.inventory.index') }}" class="btn btn-gasgo">
            <i class="fas fa-arrow-left me-2"></i>Back to Inventory
        </a>
    @endif
</div>
@endsection
