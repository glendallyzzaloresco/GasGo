@extends('layouts.admin')

@section('title', 'Inventory Management')
@section('nav-inventory', 'active')

@section('admin-styles')
<style>
    :root {
        --primary-blue: var(--gasgo-blue);
        --primary-orange: var(--gasgo-orange);
    }

    .inventory-header {
        background: linear-gradient(135deg, #1a6db0 0%, #2196f3 100%);
        color: white;
        padding: 35px 30px;
        border-radius: 12px;
        margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(26, 109, 176, 0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .inventory-header h1 {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .inventory-header p {
        font-size: 0.95rem;
        opacity: 0.95;
        margin: 0;
    }
    
    .filter-section {
        background: white;
        padding: 24px;
        border-radius: 8px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        border-left: 4px solid var(--primary-blue);
    }

    .filter-section .form-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: #333;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .filter-section .form-control,
    .filter-section .form-select {
        border-radius: 6px;
        border: 1.5px solid #e0e0e0;
        font-size: 0.9rem;
        transition: all 0.3s ease;
    }

    .filter-section .form-control:focus,
    .filter-section .form-select:focus {
        border-color: var(--primary-blue);
        box-shadow: 0 0 0 3px rgba(26, 109, 176, 0.1);
    }

    .filter-section .btn {
        font-weight: 600;
        border-radius: 6px;
        padding: 10px 20px;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }

    .filter-section .btn-gasgo {
        background: linear-gradient(135deg, var(--primary-blue) 0%, #1e5090 100%);
        border: none;
        box-shadow: 0 4px 12px rgba(26, 109, 176, 0.3);
    }

    .filter-section .btn-gasgo:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(26, 109, 176, 0.4);
    }

    .filter-section .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(39, 174, 96, 0.4) !important;
    }

    .filter-section .btn-primary {
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(39, 174, 96, 0.3);
    }

    /* Table Layout */
    .inventory-table-wrapper {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 30px;
    }

    .inventory-section {
        margin-bottom: 18px;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        background: #fff;
    }

    .inventory-section summary {
        list-style: none;
        cursor: pointer;
        padding: 14px 20px;
        color: #fff;
        font-weight: 700;
        font-size: 1.02rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        user-select: none;
        transition: all 0.3s ease;
    }



    .inventory-section summary::-webkit-details-marker {
        display: none;
    }

    .section-summary-left {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .section-summary-right {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 0.82rem;
        opacity: 0.95;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .section-chevron {
        transition: transform 0.3s ease;
    }

    details[open] .section-chevron {
        transform: rotate(180deg);
    }

    .section-body {
        background: #fff;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .history-scroll-wrap {
        max-height: 280px;
        overflow-y: auto;
    }

    .table {
        margin: 0;
        font-size: 0.95rem;
    }

    .table thead {
        background: linear-gradient(135deg, #1a6db0 0%, #2196f3 100%);
        color: white;
    }

    .table thead th {
        border: none;
        padding: 16px 12px;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        vertical-align: middle;
    }

    .table tbody tr {
        border-bottom: 1px solid #f0f0f0;
    }

    .table tbody td {
        padding: 14px 12px;
        vertical-align: middle;
    }

    .product-name-cell {
        font-weight: 600;
        color: #333;
        max-width: 100%;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .product-table-image {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .product-table-image-placeholder {
        width: 50px;
        height: 50px;
        background: #f0f0f0;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .stock-status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        align-self: flex-start;
    }

    .stock-status-badge.in-stock {
        background: #d4f4dd;
        color: #0d5f2a;
    }

    .stock-status-badge.low-stock {
        background: #ffe5e5;
        color: #c41e3a;
    }

    .stock-status-badge.out-of-stock {
        background: #fce4ec;
        color: #880e4f;
    }

    .inventory-status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 16px;
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
    }

    .status-active {
        background: #d4edda;
        color: #155724;
    }

    .status-discontinued {
        background: #f8d7da;
        color: #721c24;
    }

    .status-damaged {
        background: #fff3cd;
        color: #856404;
    }

    .inventory-card-actions {
        display: flex;
        gap: 8px;
    }

    /* Category Badge Styles */
    [class^="category-badge-"] {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: capitalize;
    }

    .category-badge-tank {
        background: #e3f2fd;
        color: #1565c0;
    }

    .category-badge-accessories {
        background: #f3e5f5;
        color: #6a1b9a;
    }

    .category-badge-freebie {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .category-badge-stove,
    .category-badge-burner,
    .category-badge-appliance,
    .category-badge-appliances {
        background: #ff6f00;
        color: white;
    }

    .product-category {
        display: block;
    }

    .category-header {
        display: block;
    }

    .empty-inventory {
        text-align: center;
        padding: 60px 20px;
        color: #999;
        background: white;
        border-radius: 8px;
    }

    .empty-inventory i {
        font-size: 3.5rem;
        color: #ddd;
        margin-bottom: 16px;
    }

    .empty-inventory h5 {
        color: #666;
        font-weight: 600;
    }

    .loading-spinner {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        z-index: 9999;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        text-align: center;
    }

    .loading-spinner.show {
        display: block;
    }

    .spinner-border-custom {
        width: 50px;
        height: 50px;
        border: 4px solid #f3f3f3;
        border-top: 4px solid var(--gasgo-blue);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 12px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.3);
        z-index: 9998;
    }

    .overlay.show {
        display: block;
    }

    /* Action Button Hover Effects */
    .btn-sm {
        transition: all 0.2s ease !important;
    }

    .btn-view {
    }

    .btn-edit {
    }
</style>
@endsection

@section('content')
<div class="container-fluid p-4">


    <div class="inventory-header">
        <div>
            <h1 class="mb-2"><i class="fas fa-warehouse me-2"></i>Inventory Management</h1>
            <p class="mb-0">Monitor and manage your LPG product inventory</p>
        </div>
    </div>

    <!-- Summary Cards Section -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-left-primary shadow" id="emptyTanksCard">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="text-primary text-uppercase font-weight-bold text-xs mb-1">
                                <i class="fas fa-undo me-2"></i>Empty Tanks in Hand
                            </div>
                            <div class="h3 mb-0 text-gray-800" id="emptyTanksTotalUnits" style="font-weight: 700; color: #1a6db0;">
                                {{ $totalEmptyReturned }} Units
                            </div>
                        </div>
                        @if($emptyTanksReturned->count() > 0)
                        <button type="button" class="btn btn-sm btn-primary" id="emptyTanksViewBtn" data-bs-toggle="modal" data-bs-target="#emptyTanksModal">
                            <i class="fas fa-eye me-1"></i>View
                        </button>
                        @else
                        <button type="button" class="btn btn-sm btn-primary d-none" id="emptyTanksViewBtn" data-bs-toggle="modal" data-bs-target="#emptyTanksModal">
                            <i class="fas fa-eye me-1"></i>View
                        </button>
                        @endif
                    </div>
                    @if($emptyTanksReturned->count() > 0)
                    <div class="mt-3" id="emptyTanksPreviewWrap" style="max-height: 150px; overflow-y: auto;">
                      
                        <div id="emptyTanksPreviewList">
                        @foreach($emptyTanksReturned->take(3) as $inv)
                        <div class="mb-1 p-2 bg-light rounded" style="border-left: 3px solid #1a6db0; font-size: 0.85rem;">
                            <div class="d-flex justify-content-between">
                                <span style="color: #333;">{{ $inv->product->name }}</span>
                                <span class="badge bg-info">{{ $inv->empty_on_hand }}</span>
                            </div>
                        </div>
                        @endforeach
                        </div>
                        <small id="emptyTanksPreviewMore" class="text-muted d-block mt-2 @if($emptyTanksReturned->count() <= 3) d-none @endif">+{{ max($emptyTanksReturned->count() - 3, 0) }} more...</small>
                    </div>
                    @else
                    <div class="mt-3 d-none" id="emptyTanksPreviewWrap" style="max-height: 150px; overflow-y: auto;">
                        <div id="emptyTanksPreviewList"></div>
                        <small id="emptyTanksPreviewMore" class="text-muted d-block mt-2 d-none"></small>
                    </div>
                    <small class="text-muted d-block mt-2" id="emptyTanksEmptyText">No empty tanks in hand</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-left-success shadow">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="text-success text-uppercase font-weight-bold text-xs mb-1">
                                <i class="fas fa-box me-2"></i>Stock Received (Today)
                            </div>
                            <div class="h3 mb-0 text-gray-800" style="font-weight: 700; color: #27ae60;">
                                {{ $totalStockReceived }} Units
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#stockReceivedModal">
                            <i class="fas fa-eye me-1"></i>View
                        </button>
                    </div>
                    @if($stockReceived->count() > 0)
                    <div class="mt-3" style="max-height: 150px; overflow-y: auto;">
                      
                        @foreach($stockReceived->take(3) as $movement)
                        <div class="mb-1 p-2 bg-light rounded" style="border-left: 3px solid #27ae60; font-size: 0.85rem;">
                            <div class="d-flex justify-content-between">
                                <span style="color: #333;">{{ $movement->inventory->product->name }}</span>
                                <span class="badge bg-success">{{ $movement->quantity_change }}</span>
                            </div>
                            <small class="text-muted">{{ $movement->movement_date->format('h:i A') }}</small>
                        </div>
                        @endforeach
                        </div>
                        <small id="stockReceivedPreviewMore" class="text-muted d-block mt-2 @if($stockReceived->count() <= 3) d-none @endif">+{{ max($stockReceived->count() - 3, 0) }} more...</small>
                    </div>
                    @else
                    <div class="mt-3 d-none" style="max-height: 150px; overflow-y: auto;">
                        <div id="stockReceivedPreviewList"></div>
                        <small id="stockReceivedPreviewMore" class="text-muted d-block mt-2 d-none"></small>
                    </div>
                    <small class="text-muted d-block mt-2" id="stockReceivedEmptyText">No stock received today</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Stock Movement History Button -->
    <div class="mb-4">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#recentStockMovementModal" style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%); border: none; font-weight: 600; padding: 12px 24px; border-radius: 6px; font-size: 1rem;">
            <i class="fas fa-history me-2"></i>View Recent Stock Movement History
        </button>
    </div>

    <!-- Empty Tanks Modal -->
    <div class="modal fade" id="emptyTanksModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #1a6db0 0%, #2196f3 100%); color: white;">
                    <h5 class="modal-title"><i class="fas fa-undo me-2"></i>Empty Tanks in Hand</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="emptyTanksFilterForm" method="GET" action="{{ route('admin.inventory.index') }}" class="row g-2 align-items-end mb-3">
                        @foreach(request()->except('empty_date') as $key => $value)
                            @if(!is_array($value) && $value !== null && $value !== '')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <div class="col-md-5">
                            <label for="emptyDateFilter" class="form-label mb-1">Filter by Date</label>
                            <input type="date" id="emptyDateFilter" name="empty_date" class="form-control" value="{{ $selectedEmptyDate }}">
                        </div>
                        <div class="col-md-7 d-flex gap-2">
                            <button id="emptyDateApplyBtn" type="submit" class="btn btn-primary">
                                <i class="fas fa-filter me-1"></i>Apply Date
                            </button>
                            <button id="emptyDateClearBtn" type="button" class="btn btn-outline-secondary">
                                <i class="fas fa-eraser me-1"></i>Clear
                            </button>
                        </div>
                    </form>

                    <div class="mb-2" id="emptyTanksFilterSummary">
                        <small class="text-muted">
                            @if($selectedEmptyDate)
                                Showing empty tank returns for <strong>{{ \Carbon\Carbon::parse($selectedEmptyDate)->format('M d, Y') }}</strong>
                            @else
                                Showing empty tank returns for all dates
                            @endif
                            : <strong>{{ $totalEmptyReturnedByDate ?? 0 }}</strong> unit(s)
                        </small>
                    </div>

                    <table class="table table-striped table-hover">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th>Product Name</th>
                                <th style="text-align: center;">Empty Tanks</th>
                                <th>Date Delivered</th>
                            </tr>
                        </thead>
                        <tbody id="emptyTanksTableBody">
                            @forelse($emptyTankReturnsByDate as $item)
                                <tr>
                                    <td>
                                        <strong>{{ $item->product_name }}</strong>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="badge bg-warning text-dark" style="font-size: 0.95rem; padding: 10px 14px;">
                                            {{ (int) $item->returned_qty }} Units
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->latest_delivery_date)
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($item->latest_delivery_date)->format('M d, Y - h:i A') }}
                                            </small>
                                        @else
                                            <small class="text-muted text-secondary">--</small>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        No empty tank returns found for the selected date.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <small class="text-muted" id="emptyTanksTotalReturned">Total Returned: <strong>{{ $totalEmptyReturnedByDate ?? 0 }}</strong> Units</small>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Stock Received Modal -->
    <div class="modal fade" id="stockReceivedModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%); color: white;">
                    <h5 class="modal-title"><i class="fas fa-box me-2"></i>Stock Received</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="stockReceivedFilterForm" class="row g-2 align-items-end mb-3">
                        @foreach(request()->except('stock_date') as $key => $value)
                            @if(!is_array($value) && $value !== null && $value !== '')
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach
                        <div class="col-md-5">
                            <label for="stockDateFilter" class="form-label mb-1">Filter by Date</label>
                            <input type="date" id="stockDateFilter" name="stock_date" class="form-control">
                        </div>
                        <div class="col-md-7 d-flex gap-2">
                            <button id="stockDateApplyBtn" type="submit" class="btn btn-success">
                                <i class="fas fa-filter me-1"></i>Apply Date
                            </button>
                            <button id="stockDateClearBtn" type="button" class="btn btn-outline-secondary">
                                <i class="fas fa-eraser me-1"></i>Clear
                            </button>
                        </div>
                    </form>

                    <div class="mb-2" id="stockReceivedFilterSummary">
                        <small class="text-muted">
                            Showing stock received for all dates : <strong>{{ count($stockReceived) }}</strong> deliveries
                        </small>
                    </div>

                    <table class="table table-striped table-hover">
                        <thead style="background: #f8f9fa;">
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($stockReceived as $movement)
                            <tr>
                                <td>
                                    <strong>{{ $movement->inventory->product->name }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-success" style="font-size: 0.9rem; padding: 8px 12px;">
                                        {{ $movement->quantity_change }} Units
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $movement->movement_date->format('M d, Y - h:i A') }}
                                    </small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    No stock received found for the selected date.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <small class="text-muted" id="stockReceivedTotal">Total Received: <strong>{{ $totalStockReceived ?? count($stockReceived) }}</strong> Units</small>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Stock Movement History Modal -->
    <div class="modal fade" id="recentStockMovementModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%); color: white;">
                    <h5 class="modal-title"><i class="fas fa-history me-2"></i>Recent Stock Movement History</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>Showing <strong>{{ ($recentStockMovements ?? collect())->count() }}</strong> recent records
                            </small>
                        </div>
                        <a href="{{ route('admin.inventory.movements') }}" class="btn btn-sm" style="background: #0f766e; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem;">
                            <i class="fas fa-external-link-alt me-1"></i>View Full History
                        </a>
                    </div>
                    
                    @if(($recentStockMovements ?? collect())->isEmpty())
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-inbox me-2"></i>No stock movement history yet.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 25%;">Product</th>
                                        <th style="width: 12%;">Type</th>
                                        <th style="width: 10%;">Quantity</th>
                                        <th style="width: 18%;">Date & Time</th>
                                        <th style="width: 23%;">Notes</th>
                                        <th style="width: 12%;">By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentStockMovements as $movement)
                                        @php
                                            $movementType = strtolower((string) $movement->type);
                                            $movementDate = $movement->movement_date ?? $movement->created_at;
                                            $quantityValue = (int) $movement->quantity_change;
                                            $badgeClass = 'bg-secondary';
                                            $qtyClass = 'text-success';

                                            if ($movementType === 'stock_in' || $movementType === 'purchase' || $quantityValue > 0) {
                                                $badgeClass = 'bg-success';
                                            } elseif ($movementType === 'stock_out' || $movementType === 'sale' || $movementType === 'damage' || $quantityValue < 0) {
                                                $badgeClass = 'bg-danger';
                                                $qtyClass = 'text-danger';
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $movement->inventory->product->name ?? 'Unknown Product' }}</strong>
                                            </td>
                                            <td>
                                                <span class="badge {{ $badgeClass }}" style="text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.3px;">
                                                    {{ str_replace('_', ' ', $movement->type) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="{{ $qtyClass }}" style="font-weight: 700;">
                                                    {{ $quantityValue > 0 ? '+' : '' }}{{ $quantityValue }}
                                                </span>
                                            </td>
                                            <td>
                                                <small>{{ $movementDate ? $movementDate->format('M d, Y - h:i A') : 'N/A' }}</small>
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ $movement->notes ?: 'No notes' }}</small>
                                            </td>
                                            <td>
                                                <small>{{ $movement->creator->name ?? 'System' }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Toast Notification Container -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11; right: 20px; bottom: 20px;">
        <div id="stockNotification" class="toast align-items-center border-0 shadow-lg" role="alert" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%); color: white; min-width: 300px;" data-bs-autohide="true" data-bs-delay="3000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong id="toastMessage">Stock Added Successfully!</strong>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <div class="filter-section">
        <div class="row g-3 mb-3">
            <div class="col-md-3">
                <label class="form-label"><i class="fas fa-search me-2"></i>Search Product</label>
                <input type="text" id="searchInput" name="search" class="form-control" placeholder="Enter product name..." 
                       value="{{ request('search') }}">
            </div>
            
            <div class="col-md-2">
                <label class="form-label"><i class="fas fa-check-circle me-2"></i>Stock Status</label>
                <select id="statusSelect" name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="in_stock" {{ request('status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    <option value="out_of_stock" {{ request('status') === 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Sort By</label>
                <select id="sortSelect" name="sort_by" class="form-select">
                    <option value="name_asc" {{ request('sort_by', 'name_asc') === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                    <option value="name_desc" {{ request('sort_by') === 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                    <option value="category_asc" {{ request('sort_by') === 'category_asc' ? 'selected' : '' }}>Category (A-Z)</option>
                    <option value="category_desc" {{ request('sort_by') === 'category_desc' ? 'selected' : '' }}>Category (Z-A)</option>
                    <option value="stock_level" {{ request('sort_by') === 'stock_level' ? 'selected' : '' }}>Stock Level</option>
                    <option value="stock_high" {{ request('sort_by') === 'stock_high' ? 'selected' : '' }}>Stock (High to Low)</option>
                    <option value="stock_low" {{ request('sort_by') === 'stock_low' ? 'selected' : '' }}>Stock (Low to High)</option>
                    <option value="lastrestocked_new" {{ request('sort_by') === 'lastrestocked_new' ? 'selected' : '' }}>Recently Restocked</option>
                    <option value="lastrestocked_old" {{ request('sort_by') === 'lastrestocked_old' ? 'selected' : '' }}>Not Recently Restocked</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label"><i class="fas fa-tags me-2"></i>Category</label>
                <select id="categorySelect" name="category" class="form-select">
                    <option value="">All Categories</option>
                    <option value="tank" {{ strtolower((string) request('category')) === 'tank' ? 'selected' : '' }}>Tank</option>
                    <option value="accessories" {{ strtolower((string) request('category')) === 'accessories' ? 'selected' : '' }}>Accessories</option>
                    <option value="appliances" {{ in_array(strtolower((string) request('category')), ['appliance', 'appliances'], true) ? 'selected' : '' }}>Appliances</option>
                    <option value="freebie" {{ strtolower((string) request('category')) === 'freebie' ? 'selected' : '' }}>Freebie</option>
                </select>
            </div>

            <div class="col-md-3"></div>
        </div>
    </div>

    <div id="inventoryResults">
        @if($inventories->isEmpty())
            <div class="empty-inventory">
                <i class="fas fa-inbox"></i>
                <h5>No inventory records found</h5>
                <p>Start adding products to inventory management</p>
            </div>
        @else
            <!-- UNIFIED PRODUCTS TABLE -->
            <details class="inventory-section" open>
                <summary style="background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);">
                    <span class="section-summary-left"><i class="fas fa-cubes"></i>All Products</span>
                    <span class="section-summary-right">{{ $inventories->count() }} items <i class="fas fa-chevron-down section-chevron"></i></span>
                </summary>
                <div class="section-body">
                <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Product Name</th>
                            <th style="width: 12%;">Category</th>
                            <th style="width: 12%;">Stock on Hand</th>
                            <th style="width: 10%;">Reorder Level</th>
                            <th style="width: 12%;">Status</th>
                            <th style="width: 29%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Sort inventories by category: Tank, Accessories, Appliances
                            $sortedInventories = $inventories->sortBy(function($inv) {
                                $category = strtolower($inv->product->category);
                                $order = match($category) {
                                    'tank' => 0,
                                    'accessories' => 1,
                                    'appliances' => 2,
                                    default => 3,
                                };
                                return [$order, strtolower($inv->product->name)];
                            });
                        @endphp
                        @foreach($sortedInventories as $inventory)
                            @php
                                $stockStatus = 'in-stock';
                                $stockLabel = 'In Stock';
                                if ($inventory->quantity_on_hand == 0) {
                                    $stockStatus = 'out-of-stock';
                                    $stockLabel = 'Out of Stock';
                                } elseif ($inventory->quantity_on_hand <= $inventory->reorder_level) {
                                    $stockStatus = 'low-stock';
                                    $stockLabel = 'Low Stock';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="product-name-cell">
                                        <img src="{{ $inventory->product->image_url ?? asset('images/default-product.png') }}" 
                                             alt="{{ $inventory->product->name }}" 
                                             class="product-table-image">
                                        <span>{{ $inventory->product->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge category-badge-{{ strtolower($inventory->product->category) }}" style="font-size: 0.9rem; padding: 6px 12px;">
                                        {{ ucfirst($inventory->product->category) }}
                                    </span>
                                </td>
                                <td class="font-weight-bold stock-on-hand">
                                    {{ $inventory->quantity_on_hand }}
                                </td>
                                <td>{{ $inventory->reorder_level }}</td>
                                <td>
                                    <span class="stock-status-badge {{ $stockStatus }}" style="display: inline-block; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 600;">
                                        {{ $stockLabel }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.inventory.show', $inventory) }}" class="btn btn-sm btn-view" style="background: #2196f3; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem;">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        <button type="button" class="btn btn-sm" onclick="setAdjustInventory('{{ $inventory->id }}', '{{ $inventory->product->name }}')" data-bs-toggle="modal" data-bs-target="#adjustStockModal" style="background: #27ae60; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem;">
                                            <i class="fas fa-plus me-1"></i>Add Stock
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                </div>
            </details>

            <!-- Freebies & Rewards Section -->
            @if($freebies && $freebies->count() > 0)
            <details class="inventory-section">
                <summary style="background: linear-gradient(135deg, #27ae60 0%, #1f8449 100%);">
                    <span class="section-summary-left"><i class="fas fa-gift"></i>Freebies & Rewards</span>
                    <span class="section-summary-right">{{ $freebies->count() }} items <i class="fas fa-chevron-down section-chevron"></i></span>
                </summary>
                <div class="section-body">
                <table class="table table-hover">
                    <thead>
                        <tr style="background: linear-gradient(135deg, #27ae60 0%, #1f8449 100%); color: white;">
                            <th style="width: 30%;">Name</th>
                            <th style="width: 20%;">Category</th>
                            <th style="width: 12%;">Stock</th>
                            <th style="width: 12%;">Reward Points</th>
                            <th style="width: 12%;">Status</th>
                            <th style="width: 14%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($freebies as $freebie)
                            @php
                                $stockStatus = 'in-stock';
                                $stockLabel = 'In Stock';
                                
                                if ($freebie->stock == 0) {
                                    $stockStatus = 'out-of-stock';
                                    $stockLabel = 'Out of Stock';
                                } elseif ($freebie->stock <= 5) {
                                    $stockStatus = 'low-stock';
                                    $stockLabel = 'Low Stock';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="product-name-cell">
                                        <img src="{{ $freebie->image_url ?? asset('images/default-product.png') }}" 
                                             alt="{{ $freebie->name }}" 
                                             class="product-table-image">
                                        <span>{{ $freebie->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span style="color: #666; font-weight: 500;">{{ ucfirst($freebie->category ?? 'Reward') }}</span>
                                </td>
                                <td>
                                    <span style="font-weight: 700; color: #27ae60; font-size: 1.05rem;">{{ $freebie->stock }}</span>
                                </td>
                                <td>
                                    <span style="color: #666; font-weight: 600;">{{ $freebie->reward_points_required ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="stock-status-badge {{ $stockStatus }}">
                                        {{ $stockLabel }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.products', ['tab' => 'freebies']) }}" class="btn btn-sm btn-view" style="background: #2196f3; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem;">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        <button type="button" class="btn btn-sm" style="background: #27ae60; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem;">
                                            <i class="fas fa-plus me-1"></i>Add Stock
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </details>
            @endif
        @endif
    </div>

    <!-- Loading Spinner -->
    <div class="overlay" id="loadingOverlay"></div>
    <div class="loading-spinner" id="loadingSpinner">
        <div class="spinner-border-custom"></div>
        <p>Filtering inventory...</p>
    </div>
</div>

<!-- Quick Add Stock Modal -->
<div class="modal fade" id="quickAddModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Quick Add Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="quickAddForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Product</label>
                        <select name="inventory_id" id="inventorySelect" class="form-select" required>
                            <option value="">Choose a product...</option>
                            @forelse($inventories as $inv)
                                <option value="{{ $inv->id }}">
                                    {{ $inv->product->name }} (Current: {{ $inv->quantity_on_hand }} units)
                                </option>
                            @empty
                                <option disabled>No inventory items available</option>
                            @endforelse
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type</label>
                        <select name="type" class="form-select" required>
                            <option value="stock_in">Stock In (Restock/Refill)</option>
                            <option value="stock_out">Stock Out / Correction</option>
                            <option value="sale">Sale</option>
                            <option value="damage">Damage / Loss</option>
                            <option value="return">Return from Customer</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity_change" class="form-control" min="1" required placeholder="Enter quantity...">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notes (Optional)</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g., Delivery from vendor, customer return...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gasgo">
                        <i class="fas fa-save me-2"></i>Add Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal for viewing inventory details -->
<div class="modal fade" id="inventoryModal" tabindex="-1" aria-labelledby="inventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1a6db0 0%, #2196f3 100%); color: #fff; border: none;">
                <h5 class="modal-title" id="inventoryModalLabel">
                    <i class="fas fa-box me-2"></i>Inventory Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
            </div>
            <div class="modal-body" id="inventoryModalBody">
                <!-- Content loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Add Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-labelledby="adjustStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #4caf50 0%, #45a049 100%); color: white;">
                <h5 class="modal-title" id="adjustStockModalLabel">
                    <i class="fas fa-plus me-2"></i>Add Stock - <span id="adjustProductName"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
            </div>
            <form id="adjustStockForm" method="POST">
                @csrf
                <input type="hidden" name="inventory_id" id="adjustInventoryId">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Adjustment Type *</label>
                        <select name="type" class="form-select" id="adjustmentType" required>
                            <option value="">Select type...</option>
                            <optgroup label="STOCK IN (Add Inventory)">
                                <option value="stock_in">Stock In (Restock/Refill)</option>
                            </optgroup>
                            <optgroup label="STOCK OUT (Reduce Inventory)">
                                <option value="stock_out">Stock Out / Correction</option>
                                <option value="sale">Sale</option>
                                <option value="damage">Damage / Loss</option>
                                <option value="return">Customer Return (Empty Tank - LPG Only)</option>
                            </optgroup>
                        </select>
                        @error('type')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label" for="quantityInput">Quantity *</label>
                        <input type="number" name="quantity_change" class="form-control" id="quantityInput" required 
                               placeholder="Enter positive number (units)" min="1">
                        <small class="text-muted" id="quantityHint">Enter quantity to add (Stock In only accepts positive values)</small>
                        @error('quantity_change')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Movement Date & Time</label>
                        <input type="datetime-local" name="movement_date" class="form-control" id="movementDateTime" 
                               value="" readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                        <small class="text-muted">Automatically set to today's date and time</small>
                        @if ($errors->has('movement_date'))
                            <small class="text-danger d-block mt-1">{{ $errors->first('movement_date') }}</small>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reference/Notes (Optional)</label>
                        <textarea name="notes" class="form-control" id="notesInput"
                               placeholder="e.g., Delivery from supplier, damaged batch, etc." maxlength="255" rows="2"></textarea>
                        @error('notes')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gasgo">
                        <i class="fas fa-save me-2"></i>Add Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Add Stock Modal -->
<div class="modal fade" id="bulkAddStockModal" tabindex="-1" aria-labelledby="bulkAddStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%); color: white;">
                <h5 class="modal-title" id="bulkAddStockModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Add Stock to Inventory
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="filter: brightness(0) invert(1);"></button>
            </div>
            <form id="bulkAddStockForm" method="POST">
                @csrf
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Product *</label>
                        <select name="inventory_id" class="form-select" id="bulkProductSelect" required>
                            <option value="">-- Choose a product --</option>
                            @php
                                $allInventories = $inventories ?? collect();
                                // Group by category for better organization
                                $grouped = [
                                    'Tank' => $allInventories->filter(fn($inv) => strtolower($inv->product->category) === 'tank'),
                                    'Appliances' => $allInventories->filter(fn($inv) => in_array(strtolower($inv->product->category), ['stove', 'burner', 'appliance', 'appliances'], true)),
                                    'Accessories' => $allInventories->filter(fn($inv) => strtolower($inv->product->category) === 'accessories'),
                                ];
                            @endphp
                            @foreach($grouped as $category => $items)
                                @if($items->count() > 0)
                                    <optgroup label="{{ $category }}">
                                        @foreach($items as $inventory)
                                            <option value="{{ $inventory->id }}" data-stock="{{ $inventory->quantity_on_hand }}" data-name="{{ $inventory->product->name }}">
                                                {{ $inventory->product->name }} (Stock: {{ $inventory->quantity_on_hand }})
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-2">
                            <strong id="selectedProductInfo"></strong>
                        </small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Adjustment Type *</label>
                            <select name="type" class="form-select" id="bulkAdjustmentType" required>
                                <option value="">Select type...</option>
                                <optgroup label="STOCK IN (Add Inventory)">
                                    <option value="stock_in">Stock In (Restock/Refill)</option>
                                </optgroup>
                                <optgroup label="STOCK OUT (Reduce Inventory)">
                                    <option value="stock_out">Stock Out / Correction</option>
                                    <option value="sale">Sale</option>
                                    <option value="damage">Damage / Loss</option>
                                    <option value="return">Customer Return (Empty Tank - LPG Only)</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="bulkQuantityInput">Quantity *</label>
                            <input type="number" name="quantity_change" class="form-control" id="bulkQuantityInput" required 
                                   placeholder="Enter quantity" min="1">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Movement Date & Time</label>
                        <input type="datetime-local" name="movement_date" class="form-control" id="bulkMovementDateTime" 
                               readonly style="background-color: #f8f9fa; cursor: not-allowed;">
                        <small class="text-muted">Automatically set to today's date and time</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Reference/Notes (Optional)</label>
                        <textarea name="notes" class="form-control" id="bulkNotesInput"
                               placeholder="e.g., Delivery from supplier, damaged batch, etc." maxlength="255" rows="2"></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save me-2"></i>Add Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ===== BULK ADD STOCK MODAL FUNCTIONALITY =====
document.addEventListener('DOMContentLoaded', function() {
    // Initialize bulk add stock modal datetime
    const bulkMovementDateInput = document.getElementById('bulkMovementDateTime');
    if (bulkMovementDateInput) {
        bulkMovementDateInput.value = new Date().toISOString().slice(0, 16);
    }

    // Handle bulk product selection
    const bulkProductSelect = document.getElementById('bulkProductSelect');
    const selectedProductInfo = document.getElementById('selectedProductInfo');
    
    if (bulkProductSelect) {
        bulkProductSelect.addEventListener('change', function() {
            if (this.value) {
                const selectedOption = this.options[this.selectedIndex];
                const productName = selectedOption.getAttribute('data-name');
                const currentStock = selectedOption.getAttribute('data-stock');
                selectedProductInfo.textContent = `${productName} - Current Stock: ${currentStock} units`;
            } else {
                selectedProductInfo.textContent = '';
            }
        });
    }

    // Handle bulk adjustment type change
    const bulkAdjustmentType = document.getElementById('bulkAdjustmentType');
    const bulkQuantityInput = document.getElementById('bulkQuantityInput');
    
    if (bulkAdjustmentType) {
        bulkAdjustmentType.addEventListener('change', function() {
            const selectedType = this.value;
            if (selectedType === 'stock_in') {
                bulkQuantityInput.placeholder = 'Enter quantity to add';
                bulkQuantityInput.style.borderColor = '#27ae60';
            } else if (['stock_out', 'sale', 'damage', 'return'].includes(selectedType)) {
                bulkQuantityInput.placeholder = 'Enter quantity to remove';
                bulkQuantityInput.style.borderColor = '#e74c3c';
            } else {
                bulkQuantityInput.placeholder = 'Enter quantity';
                bulkQuantityInput.style.borderColor = '';
            }
        });
    }

    // Handle bulk add stock form submission
    const bulkAddStockForm = document.getElementById('bulkAddStockForm');
    if (bulkAddStockForm) {
        bulkAddStockForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const inventoryId = document.getElementById('bulkProductSelect').value;
            const adjustmentType = document.getElementById('bulkAdjustmentType').value;
            const quantity = document.getElementById('bulkQuantityInput').value;
            
            // Validation
            if (!inventoryId) {
                alert('Please select a product');
                return;
            }
            if (!adjustmentType) {
                alert('Please select an Adjustment Type');
                return;
            }
            if (!quantity || quantity < 1) {
                alert('Please enter a valid quantity');
                return;
            }
            
            const formData = new FormData(this);
            
            // Build the URL with inventory ID
            const url = `{{ route('admin.inventory.adjust', ':id') }}`.replace(':id', inventoryId);
            
            fetch(url, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (response.ok) {
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('bulkAddStockModal'));
                    if (modal) {
                        modal.hide();
                    }
                    // Reload page to show updated stock
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    return response.text().then(text => { 
                        console.error('Error response:', text);
                        alert('Error: ' + (text || response.statusText)); 
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error adding stock: ' + error.message);
            });
        });
    }

    // Reset form when modal is closed
    const bulkAddStockModal = document.getElementById('bulkAddStockModal');
    if (bulkAddStockModal) {
        bulkAddStockModal.addEventListener('hide.bs.modal', function() {
            document.getElementById('bulkProductSelect').value = '';
            document.getElementById('bulkAdjustmentType').value = '';
            document.getElementById('bulkQuantityInput').value = '';
            document.getElementById('bulkNotesInput').value = '';
            document.getElementById('selectedProductInfo').textContent = '';
        });
    }

    // ===== EXISTING FUNCTIONALITY BELOW =====
    

    
    // Handle adjustment type change
    const adjustmentTypeSelect = document.getElementById('adjustmentType');
    const quantityInput = document.getElementById('quantityInput');
    const quantityHint = document.getElementById('quantityHint');
    
    if (adjustmentTypeSelect) {
        adjustmentTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            if (selectedType === 'stock_in') {
                quantityInput.min = '1';
                quantityInput.placeholder = 'Enter quantity to add';
                quantityHint.textContent = 'Stock In always adds to inventory - enter positive quantity only';
                quantityHint.style.color = '#27ae60';
            } else if (['stock_out', 'sale', 'damage', 'return'].includes(selectedType)) {
                quantityInput.min = '1';
                quantityInput.placeholder = 'Enter quantity to remove';
                quantityHint.textContent = 'Enter positive quantity - system will subtract from inventory';
                quantityHint.style.color = '#e74c3c';
            } else {
                quantityInput.min = '1';
                quantityHint.textContent = 'Enter quantity';
                quantityHint.style.color = '#999';
            }
            quantityInput.value = '';
        });
    }
});

// Function to set inventory for adjustment
function setAdjustInventory(inventoryId, productName) {
    document.getElementById('adjustInventoryId').value = inventoryId;
    document.getElementById('adjustProductName').textContent = productName;
    document.getElementById('adjustmentType').value = '';
    document.getElementById('quantityInput').value = '';
    document.getElementById('notesInput').value = '';
    
    const movementDateInput = document.getElementById('movementDateTime');
    if (movementDateInput) {
        // Set to server's current date/time using the same approach as Laravel
        // Use now() which is already in server timezone
        const now = new Date();
        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, '0');
        const day = String(now.getDate()).padStart(2, '0');
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        
        const localDateTime = `${year}-${month}-${day}T${hours}:${minutes}`;
        movementDateInput.value = localDateTime;
        movementDateInput.readOnly = false;
        movementDateInput.value = localDateTime;
        movementDateInput.readOnly = true;
    }
}

// Handle adjust stock form submission
document.getElementById('adjustStockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const inventoryId = document.getElementById('adjustInventoryId').value;
    const adjustmentType = document.getElementById('adjustmentType').value;
    const quantity = document.getElementById('quantityInput').value;
    
    // Validation
    if (!inventoryId) {
        alert('Error: Inventory ID not found');
        return;
    }
    if (!adjustmentType) {
        alert('Please select an Adjustment Type');
        return;
    }
    if (!quantity || quantity < 1) {
        alert('Please enter a valid quantity');
        return;
    }
    
    const formData = new FormData(this);
    
    // Build the URL with inventory ID
    const url = `{{ route('admin.inventory.adjust', ':id') }}`.replace(':id', inventoryId);
    
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) {
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('adjustStockModal'));
            if (modal) {
                modal.hide();
            }
            // Show success notification
            showStockNotification('Stock Added Successfully!');
            // Reload after notification is shown
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            return response.text().then(text => { 
                console.error('Error response:', text);
                alert('Error: ' + (text || response.statusText)); 
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error adjusting stock: ' + error.message);
    });
});

// Function to show toast notification
function showStockNotification(message = 'Stock Added Successfully!') {
    const toastElement = document.getElementById('stockNotification');
    const toastMessage = document.getElementById('toastMessage');
    
    if (toastElement && toastMessage) {
        toastMessage.textContent = message;
        const toast = new bootstrap.Toast(toastElement);
        toast.show();
    }
}

const searchInput = document.getElementById('searchInput');
const statusSelect = document.getElementById('statusSelect');
const sortSelect = document.getElementById('sortSelect');
const categorySelect = document.getElementById('categorySelect');
const clearFiltersBtn = document.getElementById('clearFiltersBtn');
const inventoryResults = document.getElementById('inventoryResults');
const loadingSpinner = document.getElementById('loadingSpinner');
const loadingOverlay = document.getElementById('loadingOverlay');
const emptyTanksFilterForm = document.getElementById('emptyTanksFilterForm');
const emptyDateFilterInput = document.getElementById('emptyDateFilter');
const emptyDateApplyBtn = document.getElementById('emptyDateApplyBtn');
const emptyDateClearBtn = document.getElementById('emptyDateClearBtn');
const emptyTanksTotalUnits = document.getElementById('emptyTanksTotalUnits');
const emptyTanksPreviewList = document.getElementById('emptyTanksPreviewList');
const emptyTanksPreviewWrap = document.getElementById('emptyTanksPreviewWrap');
const emptyTanksPreviewMore = document.getElementById('emptyTanksPreviewMore');
const emptyTanksEmptyText = document.getElementById('emptyTanksEmptyText');
const emptyTanksViewBtn = document.getElementById('emptyTanksViewBtn');
let emptyTanksPollTimer = null;
let filterTimeout;

function refreshEmptyTanksCard() {
    fetch(`{{ route('admin.inventory.index') }}?${new URLSearchParams(window.location.search).toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const freshTotal = doc.getElementById('emptyTanksTotalUnits');
        const freshList = doc.getElementById('emptyTanksPreviewList');
        const freshWrap = doc.getElementById('emptyTanksPreviewWrap');
        const freshMore = doc.getElementById('emptyTanksPreviewMore');
        const freshEmptyText = doc.getElementById('emptyTanksEmptyText');
        const freshViewBtn = doc.getElementById('emptyTanksViewBtn');

        if (freshTotal && emptyTanksTotalUnits) {
            emptyTanksTotalUnits.innerHTML = freshTotal.innerHTML;
        }
        if (freshList && emptyTanksPreviewList) {
            emptyTanksPreviewList.innerHTML = freshList.innerHTML;
        }
        if (freshWrap && emptyTanksPreviewWrap) {
            emptyTanksPreviewWrap.className = freshWrap.className;
        }
        if (freshMore && emptyTanksPreviewMore) {
            emptyTanksPreviewMore.className = freshMore.className;
            emptyTanksPreviewMore.innerHTML = freshMore.innerHTML;
        }
        if (emptyTanksEmptyText) {
            if (freshEmptyText) {
                emptyTanksEmptyText.className = freshEmptyText.className;
                emptyTanksEmptyText.innerHTML = freshEmptyText.innerHTML;
            } else {
                emptyTanksEmptyText.classList.add('d-none');
            }
        }
        if (emptyTanksViewBtn) {
            if (freshViewBtn) {
                emptyTanksViewBtn.className = freshViewBtn.className;
            } else {
                emptyTanksViewBtn.classList.add('d-none');
            }
        }
    })
    .catch(error => {
        console.error('Empty tanks card refresh error:', error);
    });
}

document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        refreshEmptyTanksCard();
    }
});

emptyTanksPollTimer = setInterval(refreshEmptyTanksCard, 10000);

function applyEmptyTankDateFilter(dateValue = null) {
    if (!emptyTanksFilterForm) return;

    if (dateValue !== null && emptyDateFilterInput) {
        emptyDateFilterInput.value = dateValue;
    }

    const params = new URLSearchParams(new FormData(emptyTanksFilterForm));

    if (!emptyDateFilterInput?.value) {
        params.delete('empty_date');
    }

    if (emptyDateApplyBtn) {
        emptyDateApplyBtn.disabled = true;
        emptyDateApplyBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
    }

    fetch(`${emptyTanksFilterForm.action}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        const newSummary = doc.getElementById('emptyTanksFilterSummary');
        const newBody = doc.getElementById('emptyTanksTableBody');
        const newTotal = doc.getElementById('emptyTanksTotalReturned');

        if (newSummary) {
            document.getElementById('emptyTanksFilterSummary').innerHTML = newSummary.innerHTML;
        }
        if (newBody) {
            document.getElementById('emptyTanksTableBody').innerHTML = newBody.innerHTML;
        }
        if (newTotal) {
            document.getElementById('emptyTanksTotalReturned').innerHTML = newTotal.innerHTML;
        }
    })
    .catch(error => {
        console.error('Empty tanks date filter error:', error);
        alert('Unable to filter empty tanks right now. Please try again.');
    })
    .finally(() => {
        if (emptyDateApplyBtn) {
            emptyDateApplyBtn.disabled = false;
            emptyDateApplyBtn.innerHTML = '<i class="fas fa-filter me-1"></i>Apply Date';
        }
    });
}

if (emptyTanksFilterForm) {
    emptyTanksFilterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        applyEmptyTankDateFilter();
    });
}

if (emptyDateClearBtn) {
    emptyDateClearBtn.addEventListener('click', function() {
        applyEmptyTankDateFilter('');
    });
}

// Stock Received date filter
const stockDateFilterForm = document.getElementById('stockReceivedFilterForm');
const stockDateFilter = document.getElementById('stockDateFilter');
const stockDateClearBtn = document.getElementById('stockDateClearBtn');
let allStockReceivedData = [];

// Store all stock received data when modal loads
const stockReceivedModal = document.getElementById('stockReceivedModal');
if (stockReceivedModal) {
    stockReceivedModal.addEventListener('show.bs.modal', function() {
        // Store initial data
        const rows = document.querySelectorAll('#stockReceivedModal tbody tr:not(:has(*:only-child))');
        allStockReceivedData = Array.from(rows).map(row => ({
            html: row.innerHTML,
            date: row.querySelector('td:last-child small')?.textContent || ''
        }));
    });
}

if (stockDateFilterForm) {
    stockDateFilterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const selectedDate = stockDateFilter.value;
        
        if (!selectedDate) {
            // Show all data
            const tbody = document.querySelector('#stockReceivedModal tbody');
            tbody.innerHTML = allStockReceivedData.map(item => `<tr>${item.html}</tr>`).join('');
            
            const summaryEl = document.getElementById('stockReceivedFilterSummary');
            if (summaryEl) {
                summaryEl.innerHTML = '<small class="text-muted">Showing stock received for all dates : <strong>' + allStockReceivedData.length + '</strong> deliveries</small>';
            }
        } else {
            // Filter by date
            const selectedDateObj = new Date(selectedDate);
            const filteredData = allStockReceivedData.filter(item => {
                const itemDate = item.date.split(' - ')[0]; // Get the date part
                const itemDateObj = new Date(itemDate);
                return itemDateObj.toLocaleDateString() === selectedDateObj.toLocaleDateString();
            });
            
            const tbody = document.querySelector('#stockReceivedModal tbody');
            if (filteredData.length > 0) {
                tbody.innerHTML = filteredData.map(item => `<tr>${item.html}</tr>`).join('');
                
                const summaryEl = document.getElementById('stockReceivedFilterSummary');
                if (summaryEl) {
                    summaryEl.innerHTML = '<small class="text-muted">Showing stock received for <strong>' + selectedDateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) + '</strong> : <strong>' + filteredData.length + '</strong> deliveries</small>';
                }
            } else {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center text-muted py-4">No stock received found for the selected date.</td></tr>';
                
                const summaryEl = document.getElementById('stockReceivedFilterSummary');
                if (summaryEl) {
                    summaryEl.innerHTML = '<small class="text-muted">Showing stock received for <strong>' + selectedDateObj.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) + '</strong> : <strong>0</strong> deliveries</small>';
                }
            }
        }
    });
}

if (stockDateClearBtn) {
    stockDateClearBtn.addEventListener('click', function(e) {
        e.preventDefault();
        stockDateFilter.value = '';
        
        // Show all data
        const tbody = document.querySelector('#stockReceivedModal tbody');
        tbody.innerHTML = allStockReceivedData.map(item => `<tr>${item.html}</tr>`).join('');
        
        const summaryEl = document.getElementById('stockReceivedFilterSummary');
        if (summaryEl) {
            summaryEl.innerHTML = '<small class="text-muted">Showing stock received for all dates : <strong>' + allStockReceivedData.length + '</strong> deliveries</small>';
        }
    });
}

// Interactive hover-to-expand functionality for inventory sections
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.inventory-section');
    
    sections.forEach(section => {
        section.addEventListener('mouseenter', function() {
            // Open the details element on hover
            this.setAttribute('open', '');
        });
        
        section.addEventListener('mouseleave', function() {
            // Keep it open if user explicitly opened it, close on mouse leave
            // Optional: Remove this if you want sections to stay open after hover
            // this.removeAttribute('open');
        });
    });
});

// Real-time filtering function
function applyFilters() {
    const params = new URLSearchParams();
    
    if (searchInput.value) params.append('search', searchInput.value);
    if (statusSelect.value) params.append('status', statusSelect.value);
    if (sortSelect.value) params.append('sort_by', sortSelect.value);
    if (categorySelect.value) params.append('category', categorySelect.value);
    
    // Show loading spinner
    loadingSpinner.classList.add('show');
    loadingOverlay.classList.add('show');
    
    // Clear previous timeout
    clearTimeout(filterTimeout);
    
    // Add small delay for better UX
    filterTimeout = setTimeout(() => {
        fetch(`{{ route('admin.inventory.index') }}?${params.toString()}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Extract the inventory results section from response
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newResults = doc.getElementById('inventoryResults').innerHTML;
            inventoryResults.innerHTML = newResults;
            
            // Re-initialize any event listeners if needed
            reinitializeEventListeners();
        })
        .catch(error => {
            console.error('Error filtering inventory:', error);
            inventoryResults.innerHTML = '<div class="alert alert-danger">Error loading inventory. Please try again.</div>';
        })
        .finally(() => {
            loadingSpinner.classList.remove('show');
            loadingOverlay.classList.remove('show');
        });
    }, 300); // 300ms debounce
}

// Clear filters
function clearFilters() {
    searchInput.value = '';
    statusSelect.value = '';
    sortSelect.value = 'name_asc';
    categorySelect.value = '';
    applyFilters();
}

// Reinitialize events after dynamic content load
function reinitializeEventListeners() {
    // Any event listeners for dynamically loaded content can be re-attached here
}

// Attach event listeners
searchInput.addEventListener('input', applyFilters);
statusSelect.addEventListener('change', applyFilters);
sortSelect.addEventListener('change', applyFilters);
categorySelect.addEventListener('change', applyFilters);
clearFiltersBtn.addEventListener('click', clearFilters);

// Quick Add Stock Form
document.getElementById('quickAddForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const inventoryId = document.getElementById('inventorySelect').value;
    const formData = new FormData(this);
    
    fetch(`/admin/inventory/${inventoryId}/adjust`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(text || `Request failed with status ${response.status}`);
            });
        }
        return response.text();
    })
    .then(() => {
        // Close modal and reload inventory
        bootstrap.Modal.getInstance(document.getElementById('quickAddModal')).hide();
        applyFilters();
    })
    .catch(error => {
        console.error('Quick add stock error:', error);
        alert('Error adding stock. Please check your input and try again.');
    });
});

// Inventory details modal
document.querySelectorAll('[data-inventory-view]').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const url = this.getAttribute('href');
        
        fetch(url)
            .then(response => response.text())
            .then(html => {
                document.getElementById('inventoryModalBody').innerHTML = html;
                const modal = new bootstrap.Modal(document.getElementById('inventoryModal'));
                modal.show();
            })
            .catch(error => console.error('Error:', error));
    });
});
</script>

@endsection
