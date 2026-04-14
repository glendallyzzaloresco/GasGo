@extends('layouts.customer')

@section('title', 'GasGo - Products')
@section('nav-products', 'active')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 100%);
        color: white; padding: 50px 0 60px; margin-bottom: -30px; position: relative;
    }
    .page-header::after {
        content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 60px;
        background: #f8f9fa; clip-path: ellipse(55% 100% at 50% 100%);
    }

    .filter-bar {
        background: white; border-radius: 16px; padding: 20px 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,.06); margin-bottom: 30px;
    }
    .filter-btn {
        padding: 8px 20px; border-radius: 25px; font-weight: 600; font-size: .85rem;
        border: 2px solid #eee; background: white; color: #555; transition: all .25s; cursor: pointer;
        text-decoration: none; display: inline-block;
    }
    .filter-btn:hover, .filter-btn.active {
        background: var(--gasgo-blue); color: white; border-color: var(--gasgo-blue);
    }

    .product-card {
        background: white; border-radius: 20px; overflow: hidden;
        box-shadow: 0 8px 30px rgba(0,0,0,.08); transition: transform .35s, box-shadow .35s; height: 100%;
        cursor: pointer;
    }
    .product-card:hover {
        transform: translateY(-8px); box-shadow: 0 16px 40px rgba(0,0,0,.14);
    }
    .product-card .product-img {
        height: 220px; background: white;
        display: flex; align-items: center; justify-content: center; position: relative;
    }
    .product-card .product-img img { max-height: 180px; object-fit: contain; }
    .product-badge {
        position: absolute; top: 14px; left: 14px;
        background: var(--gasgo-orange); color: white;
        padding: 4px 14px; border-radius: 20px; font-size: .75rem; font-weight: 600;
    }
    .product-badge.accessory { background: var(--gasgo-blue); }
    .product-body { padding: 20px; }
    .product-body h5 { font-weight: 700; color: var(--gasgo-blue); }
    .product-price { font-size: 1.25rem; font-weight: 700; color: var(--gasgo-orange); }
    .product-weight { font-size: .82rem; color: #888; }
    .product-stock { font-size: .8rem; font-weight: 500; }
    .product-stock.in { color: #27ae60; }
    .product-stock.out { color: #e74c3c; }
    
    /* Notification Toast Styles */
    .notification-toast {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        background: white;
        padding: 16px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        border-left: 4px solid #28a745;
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 350px;
        animation: slideInNotification 0.3s ease-out;
    }
    
    .notification-toast.success {
        background: #d4edda;
        color: #155724;
        border-left-color: #28a745;
    }
    
    .notification-toast.error {
        background: #f8d7da;
        color: #721c24;
        border-left-color: #dc3545;
    }
    
    .notification-toast i {
        font-size: 18px;
        flex-shrink: 0;
    }
    
    .notification-toast-content {
        flex: 1;
    }
    
    .notification-toast-message {
        font-weight: 500;
        margin-bottom: 6px;
    }
    
    .notification-toast-action {
        display: flex;
        gap: 8px;
    }
    
    .notification-toast-btn {
        background: rgba(0,0,0,0.1);
        border: none;
        color: inherit;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .notification-toast-btn:hover {
        background: rgba(0,0,0,0.2);
    }
    
    .notification-toast-close {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        opacity: 0.7;
        padding: 0;
        flex-shrink: 0;
        color: inherit;
    }
    
    .notification-toast-close:hover {
        opacity: 1;
    }
    
    @keyframes slideInNotification {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    .btn-outline-gasgo {
        border: 2px solid var(--gasgo-blue);
        color: var(--gasgo-blue);
        background: transparent;
        padding: 6px 14px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-outline-gasgo:hover:not(:disabled) {
        background: var(--gasgo-blue);
        color: white;
    }

    .btn-outline-gasgo:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    /* Product Action Buttons */
    .product-actions {
        display: flex;
        gap: 10px;
        margin-top: 16px;
    }

    .product-actions .btn-add {
        flex: 1;
        background: var(--gasgo-orange);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .product-actions .btn-add:hover:not(:disabled) {
        background: #f07708;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(247, 148, 29, 0.3);
    }

    .product-actions .btn-add:disabled {
        background: #ccc;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .product-actions .btn-buy {
        flex: 1;
        background: transparent;
        color: var(--gasgo-blue);
        border: 2px solid var(--gasgo-blue);
        padding: 8px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .product-actions .btn-buy:hover:not(:disabled) {
        background: var(--gasgo-blue);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 109, 176, 0.3);
    }

    .product-actions .btn-buy:disabled {
        border-color: #ccc;
        color: #ccc;
        cursor: not-allowed;
        opacity: 0.6;
    }

</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold" data-aos="fade-up">Product Catalog</h1>
        <p class="mb-0" style="opacity:.9;" data-aos="fade-up" data-aos-delay="100">Browse our LPG tanks and accessories</p>
    </div>
</section>

<section class="container section-padding" style="position:relative;z-index:2;">
    <!-- Filters -->
    <div class="filter-bar" data-aos="fade-up">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-bold text-muted me-2"><i class="fas fa-filter me-1"></i>Filter:</span>
            <a href="{{ route('customer.products') }}" class="filter-btn {{ !request('category') ? 'active' : '' }}">All</a>
            <a href="{{ route('customer.products', ['category' => 'tank']) }}" class="filter-btn {{ strtolower(request('category')) === 'tank' ? 'active' : '' }}">LPG Tanks</a>
            <a href="{{ route('customer.products', ['category' => 'accessories']) }}" class="filter-btn {{ strtolower(request('category')) === 'accessories' ? 'active' : '' }}">Accessories</a>
            <a href="{{ route('customer.products', ['category' => 'appliances']) }}" class="filter-btn {{ strtolower(request('category')) === 'appliances' ? 'active' : '' }}">Appliances</a>
            <div class="ms-auto">
                <input type="text" class="form-control form-control-gasgo" placeholder="Search products..." id="searchProduct" onkeyup="searchProducts(this.value)" style="padding:10px 18px;font-size:.9rem;">
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row g-4" id="productGrid">
        @forelse($products as $index => $product)
            @php
                $productCategory = strtolower((string) ($product->category ?? 'accessory'));
                
                // Map database category to display category
                $categoryMap = [
                    'tank' => 'lpg',
                    'lpg' => 'lpg',
                    'tanks' => 'lpg',
                    'appliance' => 'appliances',
                    'appliances' => 'appliances',
                    'accessory' => 'accessories',
                    'accessories' => 'accessories',
                    'freebie' => 'accessories'
                ];
                
                $category = $categoryMap[$productCategory] ?? 'accessories';
                
                $inStock = (int) ($product->quantity_on_hand ?? 0) > 0;
                $img = $product->resolved_image;
            @endphp
            <div class="col-lg-3 col-md-6 product-item" data-category="{{ $category }}" data-name="{{ $product->name }}" data-aos="fade-up" data-aos-delay="{{ (($index % 6) + 1) * 100 }}">
                <a href="{{ route('customer.product.show', $product->id) }}" style="text-decoration: none; color: inherit;">
                    <div class="product-card">
                        <div class="product-img">
                            @if($category === 'lpg')
                                <span class="product-badge">LPG</span>
                            @elseif($category === 'appliances')
                                <span class="product-badge" style="background: #e74c3c;">Appliances</span>
                            @else
                                <span class="product-badge accessory">Accessory</span>
                            @endif
                            @if($img)
                                <img src="{{ $img }}" alt="{{ $product->name }}" class="img-fluid">
                            @else
                                <span class="text-muted small">No image available</span>
                            @endif
                        </div>
                        <div class="product-body">
                            <h5>{{ $product->name }}</h5>
                            <p class="product-weight"><i class="fas fa-weight-hanging me-1"></i>{{ $product->weight ?: ucfirst($category) }}</p>
                            <p class="product-stock {{ $inStock ? 'in' : 'out' }}"><i class="fas {{ $inStock ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>{{ $inStock ? 'In Stock' : 'Out of Stock' }}</p>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center gap-2" style="margin-bottom: 12px;">
                                <span class="product-price">₱{{ number_format($product->price, 2) }}</span>
                            </div>
                            <div class="product-actions" style="flex-direction: column;">
                                <button class="btn-buy buy-now-btn" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}" {{ $inStock ? '' : 'disabled' }} title="Buy Now">
                                    <i class="fas fa-bolt"></i>Buy Now
                                </button>
                                <button class="btn-add add-to-cart-btn" data-id="{{ $product->id }}" data-name="{{ $product->name }}" data-price="{{ $product->price }}" data-image="{{ $img }}" {{ $inStock ? '' : 'disabled' }} title="Add to Cart">
                                    <i class="fas fa-shopping-cart"></i>Add to Cart
                                </button>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12 text-center text-muted py-5">No products available yet.</div>
        @endforelse
    </div>
</section>
@endsection

@section('scripts')
<script>
// Notification system with View Cart button
function showNotificationWithAction(message, type = 'success', duration = 3000) {
    const toast = document.createElement('div');
    toast.className = `notification-toast ${type}`;
    
    const icons = {
        success: '<i class="fas fa-check-circle"></i>',
        error: '<i class="fas fa-exclamation-circle"></i>'
    };
    
    const cartUrl = "{{ route('customer.cart') }}";
    
    toast.innerHTML = `
        ${icons[type] || ''}
        <div class="notification-toast-content">
            <div class="notification-toast-message">${message}</div>
            <div class="notification-toast-action">
                <button type="button" class="notification-toast-btn" onclick="window.location.href='${cartUrl}'">View Cart</button>
            </div>
        </div>
        <button type="button" class="notification-toast-close">×</button>
    `;
    
    document.body.appendChild(toast);
    
    const closeBtn = toast.querySelector('.notification-toast-close');
    closeBtn.addEventListener('click', () => {
        toast.remove();
    });
    
    if (duration) {
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, duration);
    }
}

function addToCart(id, name, price, image) {
    addToCartAjax(id, 1)
        .then(() => {
            // Show success notification with View Cart button
            showNotificationWithAction(`✓ ${name} added to cart!`, 'success', 5000);
        })
        .catch(error => {
            console.error('Add to cart error:', error);
            showNotificationWithAction('Failed to add item to cart', 'error', 3000);
        });
}

document.querySelectorAll('.add-to-cart-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        addToCart(parseInt(this.dataset.id), this.dataset.name, parseFloat(this.dataset.price), this.dataset.image);
    });
});

function snapshotActionButtonState() {
    document.querySelectorAll('.buy-now-btn, .add-to-cart-btn').forEach(btn => {
        if (!btn.dataset.defaultHtml) {
            btn.dataset.defaultHtml = btn.innerHTML;
            btn.dataset.initialDisabled = btn.disabled ? '1' : '0';
        }
    });
}

function restoreActionButtonState() {
    document.querySelectorAll('.buy-now-btn, .add-to-cart-btn').forEach(btn => {
        if (btn.dataset.defaultHtml) {
            btn.innerHTML = btn.dataset.defaultHtml;
            btn.disabled = btn.dataset.initialDisabled === '1';
        }
    });

    buyNowInProgress = false;
}

window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        restoreActionButtonState();
    }
});

// Buy Now button event listener
document.querySelectorAll('.buy-now-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        buyNow(parseInt(this.dataset.id));
    });
});

// Buy Now function - adds product and redirects to checkout
let buyNowInProgress = false;

function buyNow(productId) {
    if (buyNowInProgress) return;

    const button = document.querySelector(`.buy-now-btn[data-id="${productId}"]`);
    if (!button) return;

    buyNowInProgress = true;
    const originalHtml = button.innerHTML;
    const timeoutMs = 10000;
    const timeoutPromise = new Promise((_, reject) => {
        setTimeout(() => reject(new Error('Request timeout')), timeoutMs);
    });
    
    // Disable button during loading
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Loading...';
    
    // Add selected item first so checkout can load it, then redirect directly to checkout
    Promise.race([addToCartAjax(productId, 1), timeoutPromise])
        .then(() => {
            const checkoutUrl = "{{ route('customer.checkout') }}" + '?selected_items=' + productId;
            window.location.href = checkoutUrl;
        })
        .catch(error => {
            console.error('Buy Now error:', error);
            button.disabled = false;
            button.innerHTML = originalHtml;
            showNotificationWithAction('Failed to process Buy Now', 'error', 3000);
        })
        .finally(() => {
            buyNowInProgress = false;
        });
}

// Combined filter and search logic
let activeFilter = 'all';
let searchQuery = '';

function filterProducts(category, buttonElement) {
    console.log('Filter clicked:', category);
    
    // Update active filter
    activeFilter = category;
    
    // Update button states
    const allButtons = document.querySelectorAll('.filter-btn');
    allButtons.forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Add active class to clicked button
    if (buttonElement) {
        buttonElement.classList.add('active');
    }
    
    // Apply filter
    console.log('Filtering by:', activeFilter);
    applyFiltersAndSearch();
}

function searchProducts(query) {
    searchQuery = query;
    console.log('Search query:', query);
    applyFiltersAndSearch();
}

function applyFiltersAndSearch() {
    const items = document.querySelectorAll('.product-item');
    console.log('Total items:', items.length, 'Active filter:', activeFilter, 'Search:', searchQuery);
    
    let visibleCount = 0;
    items.forEach(item => {
        const itemCategory = item.getAttribute('data-category');
        const itemName = item.getAttribute('data-name') || '';
        
        // Check filter
        const categoryMatch = (activeFilter === 'all' || itemCategory === activeFilter);
        
        // Check search
        const searchMatch = itemName.toLowerCase().includes(searchQuery.toLowerCase());
        
        // Show or hide
        const shouldShow = categoryMatch && searchMatch;
        item.style.display = shouldShow ? '' : 'none';
        
        if (shouldShow) visibleCount++;
        
        console.log('  Item:', itemName, '| Category:', itemCategory, '| Match:', shouldShow);
    });
    
    console.log('Visible items:', visibleCount);
}

// Also try to initialize if DOM is already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', updateProductDisplay);
} else {
    updateProductDisplay();
}
snapshotActionButtonState();
</script>
@endsection
