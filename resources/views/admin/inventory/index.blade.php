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

    /* Table Layout */
    .inventory-table-wrapper {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
        margin-bottom: 30px;
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
        transition: background 0.2s ease;
    }

    .table tbody tr:hover {
        background: #f9fbfd;
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
        transition: transform 0.2s ease;
    }

    .product-table-image:hover {
        transform: scale(1.05);
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

    .product-category {
        display: none;
    }

    .category-header {
        display: none;
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
        transition: all 0.2s ease !important;
    }

    .btn-view:hover {
        background: #1976d2 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(33, 150, 243, 0.4) !important;
    }

    .adjust-stock-btn {
        transition: all 0.2s ease !important;
    }

    .adjust-stock-btn:hover {
        background: #45a049 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(76, 175, 80, 0.4) !important;
    }

    .btn-edit {
        transition: all 0.2s ease !important;
    }

    .btn-edit:hover {
        background: #f57c00 !important;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 152, 0, 0.4) !important;
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

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="filter-section">
        <form id="filterForm" class="row g-3">
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
                    <option value="Tank" {{ request('category') === 'Tank' ? 'selected' : '' }}>Tank</option>
                    <option value="Accessories" {{ request('category') === 'Accessories' ? 'selected' : '' }}>Accessories</option>
                    <option value="Freebie" {{ request('category') === 'Freebie' ? 'selected' : '' }}>Freebie</option>
                </select>
            </div>
            
            <div class="col-md-1 d-flex align-items-end gap-2">
                <button type="button" id="clearFiltersBtn" class="btn btn-outline-secondary flex-grow-1">
                    <i class="fas fa-redo me-2"></i>Reset
                </button>
            </div>
        </form>
    </div>

    <div id="inventoryResults">
        @if($inventories->isEmpty())
            <div class="empty-inventory">
                <i class="fas fa-inbox"></i>
                <h5>No inventory records found</h5>
                <p>Start adding products to inventory management</p>
            </div>
        @else
            <div class="inventory-table-wrapper">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th style="width: 28%;">Product Name</th>
                            <th style="width: 8%;">SKU</th>
                            <th style="width: 10%;">Category</th>
                            <th style="width: 9%;">Stock on Hand</th>
                            <th style="width: 9%;">Empty Tanks <span style="font-size: 0.75rem; color: #999;">(LPG Only)</span></th>
                            <th style="width: 8%;">Reorder Level</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 18%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventories as $inventory)
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
                                    <div class="product-name-cell" style="display: flex; align-items: center; gap: 12px;">
                                        @if($inventory->product->image)
                                            <img src="{{ asset('storage/' . $inventory->product->image) }}" 
                                                 alt="{{ $inventory->product->name }}" 
                                                 class="product-table-image"
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #e0e0e0;">
                                        @else
                                            <div class="product-table-image-placeholder" 
                                                 style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 6px; border: 1px solid #e0e0e0; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas fa-image" style="color: #ccc; font-size: 1.5rem;"></i>
                                            </div>
                                        @endif
                                        <span style="font-weight: 500; color: #333;">{{ $inventory->product->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span style="color: #999; font-size: 0.85rem;">{{ $inventory->product->sku ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span style="color: #666; font-weight: 500;">{{ $inventory->product->category ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span style="font-weight: 700; color: #1a6db0; font-size: 1.05rem;">{{ $inventory->quantity_on_hand }}</span>
                                </td>
                                <td>
                                    @if(strtolower($inventory->product->category) === 'tank')
                                        <span style="font-weight: 700; color: #ff6f00; font-size: 1.05rem;">{{ $inventory->empty_on_hand ?? 0 }}</span>
                                    @else
                                        <span style="color: #999; font-size: 0.85rem;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span style="color: #666;">{{ $inventory->reorder_level }}</span>
                                </td>
                                <td>
                                    <span class="stock-status-badge {{ $stockStatus }}">
                                        {{ $stockLabel }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 8px;">
                                        <a href="{{ route('admin.inventory.show', $inventory) }}" class="btn btn-sm btn-view" style="background: #2196f3; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem;" title="View">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        <button type="button" class="btn btn-sm adjust-stock-btn" style="background: #4caf50; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem;" data-bs-toggle="modal" data-bs-target="#adjustStockModal" 
                                            data-inventory-id="{{ $inventory->id }}" data-product-name="{{ $inventory->product->name }}">
                                            <i class="fas fa-plus me-1"></i>Add Stock
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: #999;">
                                    <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 12px; display: block;"></i>
                                    No inventory items found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-4 mb-5" id="paginationContainer">
                {{ $inventories->render() }}
            </div>

            <!-- Freebies & Rewards Section -->
            @if($freebies && $freebies->count() > 0)
            <div class="inventory-table-wrapper">
                <div style="background: linear-gradient(135deg, #27ae60 0%, #1f8449 100%); color: white; padding: 14px 20px; font-weight: 700; font-size: 1.05rem;">
                    <i class="fas fa-gift me-2"></i>Freebies & Rewards
                </div>
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
                                    <div class="product-name-cell">{{ $freebie->name }}</div>
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
                                    <a href="{{ route('admin.products', ['tab' => 'freebies']) }}" class="btn btn-sm" style="background: #27ae60; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem;" title="Manage">
                                        <i class="fas fa-edit me-1"></i>Manage
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
                            <option value="purchase">Purchase</option>
                            <option value="sale">Sale</option>
                            <option value="adjustment">Correction</option>
                            <option value="return">Return from Customer</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" name="quantity" class="form-control" min="1" required placeholder="Enter quantity...">
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
                               value="" data-datetime-placeholder="true">
                        <small class="text-muted">Leave blank for current date/time</small>
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

<script>
// Set default datetime to now
document.addEventListener('DOMContentLoaded', function() {
    const movementDateInput = document.getElementById('movementDateTime');
    if (movementDateInput) {
        movementDateInput.value = new Date().toISOString().slice(0, 16);
    }
    
    // Add event listeners to all Adjust Stock buttons
    document.querySelectorAll('.adjust-stock-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const inventoryId = this.getAttribute('data-inventory-id');
            const productName = this.getAttribute('data-product-name');
            setAdjustInventory(inventoryId, productName);
        });
    });
    
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
        // Start with empty value to indicate "use current time"
        movementDateInput.value = '';
        
        // Set default value when user focuses on the field
        movementDateInput.addEventListener('focus', function(e) {
            if (!this.value) {
                this.value = new Date().toISOString().slice(0, 16);
            }
        }, { once: true });
    }
}

// Handle adjust stock form submission
document.getElementById('adjustStockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const inventoryId = document.getElementById('adjustInventoryId').value;
    const formData = new FormData(this);
    
    // Remove the hidden inventory_id field to avoid duplication
    formData.delete('inventory_id');
    
    fetch(`{{ route('admin.inventory.adjust', ':id') }}`.replace(':id', inventoryId), {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (response.ok) {
            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('adjustStockModal')).hide();
            // Reload page to show updated stock
            window.location.reload();
        } else {
            return response.text().then(text => { throw new Error(text); });
        }
    })
    .catch(error => {
        alert('Error adjusting stock: ' + error.message);
    });
});

const searchInput = document.getElementById('searchInput');
const statusSelect = document.getElementById('statusSelect');
const sortSelect = document.getElementById('sortSelect');
const categorySelect = document.getElementById('categorySelect');
const clearFiltersBtn = document.getElementById('clearFiltersBtn');
const inventoryResults = document.getElementById('inventoryResults');
const loadingSpinner = document.getElementById('loadingSpinner');
const loadingOverlay = document.getElementById('loadingOverlay');
let filterTimeout;

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
    .then(response => response.text())
    .then(() => {
        // Close modal and reload inventory
        bootstrap.Modal.getInstance(document.getElementById('quickAddModal')).hide();
        applyFilters();
    })
    .catch(error => {
        alert('Error adding stock: ' + error);
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
