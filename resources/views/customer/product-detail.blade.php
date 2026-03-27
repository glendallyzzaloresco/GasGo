@extends('layouts.customer')

@section('title', 'GasGo - ' . $product->name)
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

    .back-link {
        display: inline-block; margin-bottom: 20px; color: var(--gasgo-blue);
        text-decoration: none; font-weight: 600; font-size: .95rem;
    }
    .back-link:hover { color: var(--gasgo-orange); }

    .product-detail-card {
        background: white; border-radius: 20px; padding: 30px;
        box-shadow: 0 8px 30px rgba(0,0,0,.08);
    }

    .product-detail-img {
        height: 300px; background: #f8f9fa;
        display: flex; align-items: center; justify-content: center;
        border-radius: 16px; margin-bottom: 30px;
    }
    .product-detail-img img { max-height: 280px; object-fit: contain; }

    .product-detail-body h2 {
        font-weight: 700; color: var(--gasgo-blue); margin-bottom: 10px;
    }

    .product-detail-variant {
        font-size: 1rem; color: #666; margin-bottom: 15px;
        padding-bottom: 15px; border-bottom: 2px solid #eee;
    }

    .product-detail-price {
        font-size: 2rem; font-weight: 700; color: var(--gasgo-orange);
        margin-bottom: 20px;
    }

    .product-detail-stock {
        display: inline-block; padding: 8px 16px;
        border-radius: 20px; font-weight: 600; font-size: .9rem;
        margin-bottom: 25px;
    }
    .product-detail-stock.in {
        background: #d4edda; color: #27ae60;
    }
    .product-detail-stock.out {
        background: #f8d7da; color: #e74c3c;
    }

    .product-detail-description {
        background: #f8f9fa; padding: 20px;
        border-radius: 12px; line-height: 1.6;
        margin-bottom: 30px;
    }
    .product-detail-description p {
        margin-bottom: 10px; color: #555;
    }
    .product-detail-description p:last-child {
        margin-bottom: 0;
    }

    .product-detail-specs {
        background: #f8f9fa; padding: 20px;
        border-radius: 12px; margin-bottom: 30px;
    }
    .spec-item {
        padding: 10px 0; display: flex;
        justify-content: space-between; align-items: center;
    }
    .spec-item:not(:last-child) {
        border-bottom: 1px solid #eee;
    }
    .spec-label {
        font-weight: 600; color: #555;
    }
    .spec-value {
        color: var(--gasgo-orange); font-weight: 700;
    }
    .spec-value.text-success {
        color: #27ae60;
    }
    .spec-value.text-danger {
        color: #e74c3c;
    }

    .product-detail-actions {
        display: flex; gap: 12px; flex-wrap: wrap;
    }
    .btn-checkout {
        flex: 1; min-width: 200px;
        background: var(--gasgo-orange); color: white;
        border: none; padding: 14px 24px;
        border-radius: 12px; font-weight: 700;
        font-size: .95rem; cursor: pointer;
        transition: all .25s;
        display: flex; align-items: center; justify-content: center;
        text-decoration: none;
        font-family: inherit;
    }
    .btn-checkout:hover {
        background: #f5820f; transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(247, 148, 29, .3);
    }
    .btn-checkout:disabled {
        background: #ccc; cursor: not-allowed; transform: none;
    }

    .btn-add-cart {
        flex: 1; min-width: 200px;
        background: white; color: var(--gasgo-blue);
        border: 2px solid var(--gasgo-blue); padding: 12px 24px;
        border-radius: 12px; font-weight: 700;
        font-size: .95rem; cursor: pointer;
        transition: all .25s;
    }
    .btn-add-cart:hover {
        background: var(--gasgo-blue); color: white;
        transform: translateY(-2px);
    }
    .btn-add-cart:disabled {
        border-color: #ccc; color: #ccc; cursor: not-allowed;
    }
</style>
@endsection

@section('content')
@php
    $inStock = (int) ($product->quantity_on_hand ?? 0) > 0;
    $img = $product->resolved_image;
@endphp

<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold">{{ $product->name }}</h1>
        <p class="mb-0" style="opacity:.9;">Product Details</p>
    </div>
</section>

<section class="container section-padding" style="position:relative;z-index:2;">
    <a href="{{ route('customer.products') }}" class="back-link">
        <i class="fas fa-chevron-left me-1"></i>Back to Products
    </a>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="product-detail-card">
                <div class="product-detail-img">
                    @if($img)
                        <img src="{{ $img }}" alt="{{ $product->name }}">
                    @else
                        <span class="text-muted small">No image available</span>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="product-detail-card">
                <h2>{{ $product->name }}</h2>
                
                @if($product->description)
                    <!-- <p class="product-detail-variant">
                        <strong>Variant:</strong> {{ $product->description }}
                    </p> -->
                @endif

                <div class="product-detail-price">
                    ₱{{ number_format($product->price, 2) }}
                </div>

                <div class="product-detail-specs">
                    <div class="spec-item">
                        <span class="spec-label">Weight/Size:</span>
                        <span class="spec-value">{{ $product->weight ?: 'Standard' }}</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Price:</span>
                        <span class="spec-value">₱{{ number_format($product->price, 2) }}</span>
                    </div>
                    <div class="spec-item">
                        <span class="spec-label">Availability:</span>
                        <span class="spec-value {{ $inStock ? 'text-success' : 'text-danger' }}">
                            {{ $inStock ? 'In Stock' : 'Out of Stock' }}
                        </span>
                    </div>
                </div>

                <div class="product-detail-actions">
                    <button class="btn-add-cart add-to-cart-btn" 
                            data-id="{{ $product->id }}" 
                            data-name="{{ $product->name }}" 
                            data-price="{{ $product->price }}" 
                            data-image="{{ $img }}"
                            {{ $inStock ? '' : 'disabled' }}>
                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                    </button>
                    <button class="btn-checkout checkout-btn" 
                            data-id="{{ $product->id }}" 
                            data-name="{{ $product->name }}" 
                            data-price="{{ $product->price }}" 
                            data-image="{{ $img }}"
                            {{ $inStock ? '' : 'disabled' }}>
                        <i class="fas fa-credit-card me-2"></i>Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
document.querySelector('.add-to-cart-btn').addEventListener('click', function(e) {
    e.preventDefault();
    const id = parseInt(this.dataset.id);
    const name = this.dataset.name;
    const price = parseFloat(this.dataset.price);
    const image = this.dataset.image;
    
    addToCartAjax(id, 1).catch(error => {
        console.error('Add to cart error:', error);
    });
});

document.querySelector('.checkout-btn').addEventListener('click', function(e) {
    e.preventDefault();
    const id = parseInt(this.dataset.id);
    const name = this.dataset.name;
    const price = parseFloat(this.dataset.price);
    const image = this.dataset.image;
    const checkoutUrl = "{{ route('customer.checkout') }}";
    
    // Add product to cart first, then redirect to checkout
    addToCartAjax(id, 1).then(() => {
        // Redirect to checkout after adding to cart
        window.location.href = checkoutUrl;
    }).catch(error => {
        console.error('Add to cart error:', error);
        // Still redirect to checkout even if there's an error
        window.location.href = checkoutUrl;
    });
});
</script>
@endsection
