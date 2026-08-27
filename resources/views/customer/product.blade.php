@extends('layouts.customer')

@section('title', 'Products')
@section('nav-products', 'active')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 100%);
        color: white; padding: 50px 0 60px; margin-bottom: -30px; position: relative;
    }
    .page-header::after {
        content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 60px;
        background: #ffffff; clip-path: ellipse(55% 100% at 50% 100%);
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

    @media (max-width: 767.98px) {
        .filter-bar {
            padding: 14px 16px;
        }
        .filter-bar .ms-auto {
            width: 100%;
            margin-top: 10px;
            margin-left: 0 !important;
        }
        .filter-bar input#searchProduct {
            width: 100%;
        }
    }
    
    /* ===== NOTIFICATION TOAST ===== */
    .gasgo-notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        background: #d4f1f0;
        border: 1px solid #a8dcd9;
        border-radius: 12px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        max-width: 380px;
        width: 90%;
        animation: slideInNotification 0.35s ease-out;
        font-size: 0.95rem;
        color: #1a5a57;
    }

    .gasgo-notification.error {
        background: #ffe8e8;
        border-color: #ffb3b3;
        color: #8b0000;
    }

    .gasgo-notification.error .notification-icon {
        background: #ff4444;
    }

    .notification-icon {
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: var(--gasgo-orange);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 1.3rem;
    }

    .notification-content {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .notification-text {
        font-weight: 500;
        line-height: 1.4;
    }

    .notification-link {
        color: var(--gasgo-orange);
        text-decoration: none;
        font-weight: 600;
        margin-left: 8px;
        white-space: nowrap;
        transition: opacity 0.2s;
    }

    .notification-link:hover {
        opacity: 0.8;
        text-decoration: underline;
    }

    .notification-error .notification-link {
        color: #8b0000;
    }

    .notification-close {
        flex-shrink: 0;
        background: none;
        border: none;
        color: #999;
        font-size: 1.3rem;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
    }

    .notification-close:hover {
        color: #333;
    }

    .notification-error .notification-close {
        color: #b30000;
    }

    @keyframes slideInNotification {
        from {
            transform: translateX(420px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    @keyframes slideOutNotification {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(420px);
            opacity: 0;
        }
    }

    .gasgo-notification.fade-out {
        animation: slideOutNotification 0.35s ease-out forwards;
    }

    @media (max-width: 576px) {
        .gasgo-notification {
            top: 10px;
            right: 10px;
            left: 10px;
            width: auto;
            max-width: none;
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
        <p class="mb-0" style="opacity:.9;" data-aos="fade-up" data-aos-delay="100">Browse our {{ strtolower($homepageSettings->industry_noun ?? 'quality products') }} and catalog</p>
    </div>
</section>

<section class="container section-padding" style="position:relative;z-index:2;">
    <!-- Filters -->
    <div class="filter-bar" data-aos="fade-up">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="fw-bold text-muted me-2"><i class="fas fa-filter me-1"></i>Filter:</span>
            <a href="javascript:void(0)" onclick="filterProducts('all', this)" class="filter-btn {{ !request('category') || request('category') === 'all' ? 'active' : '' }}"><i class="fas fa-th-large me-1"></i>All</a>
            @if(isset($categories) && count($categories) > 0)
                @foreach($categories as $cat)
                    @php
                        $catSlug = is_array($cat) ? ($cat['slug'] ?? '') : strtolower((string)$cat);
                        $catName = is_array($cat) ? ($cat['name'] ?? ucfirst($catSlug)) : ucfirst($catSlug);
                        $catIcon = is_array($cat) ? ($cat['icon_class'] ?? 'fas fa-tag') : 'fas fa-tag';
                    @endphp
                    <a href="javascript:void(0)" onclick="filterProducts('{{ $catSlug }}', this)" class="filter-btn {{ strtolower(request('category', '')) === $catSlug ? 'active' : '' }}">
                        <i class="{{ $catIcon }} me-1"></i>{{ $catName }}
                    </a>
                @endforeach
            @endif
            <div class="ms-auto">
                <input type="text" class="form-control form-control-gasgo" placeholder="Search products..." id="searchProduct" onkeyup="searchProducts(this.value)" style="padding:10px 18px;font-size:.9rem;">
            </div>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row g-4" id="productGrid">
        @forelse($products as $index => $product)
            @php
                $categorySlug = strtolower(trim((string) ($product->category ?? 'tank')));
                $categoryLabel = $product->category_label;
                $categoryIcon = $product->category_icon;
                
                $inStock = (int) ($product->quantity_on_hand ?? 0) > 0;
                $img = $product->resolved_image;
            @endphp
            <div class="col-lg-3 col-md-6 product-item" data-category="{{ $categorySlug }}" data-name="{{ $product->name }}" data-aos="fade-up" data-aos-delay="{{ (($index % 6) + 1) * 100 }}">
                <a href="{{ route('customer.product.show', $product->id) }}" style="text-decoration: none; color: inherit;">
                    <div class="product-card">
                        <div class="product-img">
                            <span class="product-badge" style="background:{{ $product->category_color }};"><i class="{{ $categoryIcon }} me-1"></i>{{ $categoryLabel }}</span>
                            @if($img)
                                <img src="{{ $img }}" alt="{{ $product->name }}" class="img-fluid" onerror="this.onerror=null;this.src='{{ asset('images/default-product.png') }}';">
                            @else
                                <img src="{{ asset('images/default-product.png') }}" alt="{{ $product->name }}" class="img-fluid">
                            @endif
                        </div>
                        <div class="product-body">
                            <h5>{{ $product->name }}</h5>
                            <p class="product-weight"><i class="fas fa-tag me-1"></i>{{ $product->weight ?: $categoryLabel }}</p>
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
    const cartUrl = "{{ route('customer.cart') }}";
    const notification = document.createElement('div');
    
    notification.className = `gasgo-notification ${type === 'error' ? 'error' : ''}`;
    
    const iconHtml = type === 'success' 
        ? '<i class="fas fa-check"></i>' 
        : '<i class="fas fa-exclamation"></i>';
    
    const viewCartHtml = type === 'success' 
        ? `<a href="${cartUrl}" class="notification-link">View Cart</a>` 
        : '';
    
    notification.innerHTML = `
        <div class="notification-icon">${iconHtml}</div>
        <div class="notification-content">
            <div class="notification-text">${message}${viewCartHtml}</div>
        </div>
        <button class="notification-close" aria-label="Close notification">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.classList.add('fade-out');
        setTimeout(() => notification.remove(), 350);
    });
    
    document.body.appendChild(notification);
    
    if (duration) {
        setTimeout(() => {
            if (notification.parentNode) {
                notification.classList.add('fade-out');
                setTimeout(() => notification.remove(), 350);
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
        // Check filter
        const isTankFilter = ['tank', 'tanks', 'cylinder', 'cylinders', 'lpg-tanks', 'lpg'].includes((activeFilter || '').toLowerCase());
        const isTankItem = ['tank', 'tanks', 'cylinder', 'cylinders', 'lpg-tanks', 'lpg'].includes((itemCategory || '').toLowerCase());

        let categoryMatch = false;
        if (activeFilter === 'all') {
            categoryMatch = true;
        } else if (isTankFilter) {
            categoryMatch = isTankItem;
        } else {
            categoryMatch = (itemCategory === activeFilter);
        }
        
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
