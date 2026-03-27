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

    /* Card Grid Layout */
    .inventory-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 30px;
    }

    .inventory-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border-top: 4px solid var(--primary-blue);
        display: flex;
        flex-direction: column;
    }

    .inventory-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 8px 24px rgba(26, 109, 176, 0.16);
    }

    .inventory-card-image {
        background: linear-gradient(135deg, #f0f4f8 0%, #e8ecf1 100%);
        height: 160px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 4rem;
        color: var(--primary-blue);
        border-bottom: 1px solid #e0e0e0;
        overflow: hidden;
    }

    .inventory-card-image img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: #fff;
        padding: 10px;
    }

    .inventory-card-content {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }

    .inventory-card-title {
        font-size: 1rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 10px;
        min-height: 2.2em;
        line-clamp: 2;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .inventory-card-sku {
        font-size: 0.75rem;
        color: #999;
        margin-bottom: 12px;
        font-weight: 500;
    }

    .inventory-card-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f0f0f0;
    }

    .info-item {
        display: flex;
        flex-direction: column;
    }

    .info-label {
        font-size: 0.75rem;
        color: #999;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .info-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a6db0;
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
        margin-bottom: 12px;
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
        margin-top: auto;
    }

    .inventory-card-actions .btn {
        flex: 1;
        padding: 10px 12px;
        font-size: 0.85rem;
        font-weight: 600;
        border-radius: 6px;
        border: none;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .inventory-card-actions .btn-view {
        background: #2196f3;
        color: white;
    }

    .inventory-card-actions .btn-view:hover {
        background: #1976d2;
        transform: translateY(-2px);
    }

    .inventory-card-actions .btn-edit {
        background: var(--primary-orange);
        color: white;
        flex: 0 0 40px;
    }

    .inventory-card-actions .btn-edit:hover {
        background: #e07d0a;
        transform: translateY(-2px);
    }

    .product-category {
        margin-bottom: 40px;
    }

    .category-header {
        background: linear-gradient(135deg, #1a6db0 5%, #154e8a 100%);
        color: white;
        padding: 14px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
        font-weight: 700;
        font-size: 1.05rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .category-header.freebies {
        background: linear-gradient(135deg, #27ae60 5%, #1f8449 100%);
    }

    .category-header.regulators {
        background: linear-gradient(135deg, #f39c12 5%, #d68910 100%);
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
</style>
@endsection

@section('content')
<div class="container-fluid p-4">
    @php
        $resolveImageUrl = function (?string $path): ?string {
            if (! $path) {
                return null;
            }

            $normalized = ltrim($path, '/');

            if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
                return $path;
            }

            if (str_starts_with($normalized, 'storage/') || str_starts_with($normalized, 'images/')) {
                return asset($normalized);
            }

            return asset('storage/' . $normalized);
        };

        $freebieImageByName = \App\Models\Freebie::query()
            ->get(['name', 'image'])
            ->mapWithKeys(function ($freebie) {
                return [strtolower(trim($freebie->name)) => $freebie->image];
            });

        $resolveInventoryImagePath = function ($inventory) use ($freebieImageByName) {
            $productImage = $inventory->product->image ?? null;
            if (! empty($productImage)) {
                return $productImage;
            }

            $name = strtolower(trim((string) ($inventory->product->name ?? '')));
            $nameWithoutSuffix = strtolower(trim((string) preg_replace('/\s*\((freebie|reward)\)\s*$/i', '', $name)));

            return $freebieImageByName[$name] ?? $freebieImageByName[$nameWithoutSuffix] ?? null;
        };
    @endphp

    <div class="inventory-header">
        <div>
            <h1 class="mb-2"><i class="fas fa-warehouse me-2"></i>Inventory Management</h1>
            <p class="mb-0">Monitor and manage your LPG product inventory</p>
        </div>
        <div>
            <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#quickAddModal">
                <i class="fas fa-plus me-2"></i>Add Stock
            </button>
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
                    @foreach(($categories ?? collect()) as $category)
                        <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ $category }}</option>
                    @endforeach
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
            @php
                $grouped = collect($inventories->items())->groupBy(function ($item) {
                    $category = trim((string) ($item->product->category ?? ''));
                    return $category !== '' ? $category : 'Uncategorized';
                });

                // Sort groups by priority: Tanks first, then Freebies, then others
                $grouped = $grouped->sortBy(function ($items, $category) {
                    $categoryLower = strtolower($category);
                    
                    // Priority 1: Tanks
                    if (str_contains($categoryLower, 'tank') || str_contains($categoryLower, 'lpg')) {
                        return '1_tank';
                    }
                    
                    // Priority 2: Freebies & Rewards
                    if (str_contains($categoryLower, 'freebie') || str_contains($categoryLower, 'reward') || str_contains($categoryLower, 'promo')) {
                        return '2_freebie';
                    }
                    
                    // Priority 3: Others
                    return '3_' . $categoryLower;
                });

                $categoryIcon = function (string $category): string {
                    $label = strtolower($category);

                    if (str_contains($label, 'tank') || str_contains($label, 'lpg')) {
                        return '🛢️';
                    }
                    if (str_contains($label, 'regulator') || str_contains($label, 'equipment') || str_contains($label, 'accessor')) {
                        return '🔧';
                    }
                    if (str_contains($label, 'reward') || str_contains($label, 'freebie') || str_contains($label, 'promo')) {
                        return '🎁';
                    }

                    return '📦';
                };
            @endphp
            
            @foreach($grouped as $groupName => $items)
                <div class="product-category">
                    <div class="category-header">
                        <span>{{ $categoryIcon($groupName) }} {{ $groupName }}</span>
                    </div>
                    
                    <div class="inventory-grid">
                        @php
                            // Separate in-stock from out-of-stock within this category
                            $inStockItems = $items->filter(function($item) { return $item->quantity_on_hand > 0; });
                            $outOfStockItems = $items->filter(function($item) { return $item->quantity_on_hand == 0; });
                            
                            // Display in-stock first, then out-of-stock
                            $sortedItems = $inStockItems->concat($outOfStockItems);
                        @endphp
                        
                        @foreach($sortedItems as $inventory)
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
                            
                            @php
                                $dbImagePath = $resolveInventoryImagePath($inventory);
                                $productImageUrl = $resolveImageUrl($dbImagePath);
                            @endphp

                            <div class="inventory-card">
                                <div class="inventory-card-image">
                                    @if($productImageUrl)
                                        <img src="{{ $productImageUrl }}" alt="{{ $inventory->product->name }}">
                                    @else
                                        <i class="fas fa-box-open" aria-hidden="true"></i>
                                    @endif
                                </div>
                                
                                <div class="inventory-card-content">
                                    <h3 class="inventory-card-title">{{ $inventory->product->name }}</h3>
                                    <div class="inventory-card-sku">SKU: {{ $inventory->product->sku ?? 'N/A' }}</div>
                                    
                                    <div class="stock-status-badge {{ $stockStatus }}">
                                        {{ $stockLabel }}
                                    </div>
                                    
                                    <div class="inventory-card-info">
                                        <div class="info-item">
                                            <span class="info-label">Current Stock</span>
                                            <span class="info-value">{{ $inventory->quantity_on_hand }}</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Reorder Level</span>
                                            <span class="info-value">{{ $inventory->reorder_level }}</span>
                                        </div>
                                    </div>
                                    
                                    <div style="margin-bottom: 12px;">
                                        <span class="inventory-status-badge status-{{ $inventory->status }}">
                                            {{ ucfirst($inventory->status) }}
                                        </span>
                                    </div>
                                    
                                    <div class="inventory-card-actions">
                                        <a href="{{ route('admin.inventory.show', $inventory) }}" class="btn btn-view">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                        <a href="{{ route('admin.inventory.edit', $inventory) }}" class="btn btn-edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-center mt-4 mb-5" id="paginationContainer">
                {{ $inventories->render() }}
            </div>
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

<script>
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
