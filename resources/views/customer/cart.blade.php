@extends('layouts.customer')

@section('title', 'GasGo - Cart')
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

    .cart-container { position: relative; z-index: 2; }
    .cart-card {
        background: white; border-radius: 20px; box-shadow: 0 8px 30px rgba(0,0,0,.08); overflow: hidden;
    }
    .cart-item {
        display: flex; align-items: center; gap: 16px; padding: 18px 24px;
        border-bottom: 1px solid #f0f0f0; transition: background .2s;
    }
    .cart-item:hover { background: #fafafa; }
    .cart-item:last-child { border-bottom: none; }
    .cart-item img { width: 70px; height: 70px; border-radius: 12px; object-fit: cover; background: var(--gasgo-blue-light); }
    .cart-item .item-details { flex: 1; }
    .cart-item .item-name { font-weight: 600; color: var(--gasgo-blue); }
    .cart-item .item-price { color: var(--gasgo-orange); font-weight: 700; }
    .cart-item .item-subtotal { font-weight: 700; font-size: 1.05rem; color: #333; min-width: 100px; text-align: right; }
    .qty-control {
        display: flex; align-items: center; gap: 0; border: 2px solid #eee; border-radius: 10px; overflow: hidden;
    }
    .qty-control button {
        width: 34px; height: 34px; border: none; background: #f8f8f8; font-size: 1rem; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: background .2s;
    }
    .qty-control button:hover { background: var(--gasgo-blue-light); }
    .qty-control span { width: 36px; text-align: center; font-weight: 600; font-size: .95rem; }
    .remove-btn {
        background: none; border: none; color: #ccc; font-size: 1.1rem; cursor: pointer; transition: color .2s;
    }
    .remove-btn:hover { color: #e74c3c; }

    /* Summary */
    .cart-summary {
        background: white; border-radius: 20px; padding: 28px;
        box-shadow: 0 8px 30px rgba(0,0,0,.08); position: sticky; top: 100px;
    }
    .cart-summary h5 { font-weight: 700; color: var(--gasgo-blue); border-bottom: 2px solid #f0f0f0; padding-bottom: 14px; }
    .summary-row { display: flex; justify-content: space-between; padding: 10px 0; font-size: .95rem; }
    .summary-row.total { font-size: 1.2rem; font-weight: 700; border-top: 2px solid var(--gasgo-orange); margin-top: 8px; padding-top: 14px; }
    .summary-row.total .value { color: var(--gasgo-orange); }

    /* Empty */
    .empty-cart { text-align: center; padding: 60px 20px; }
    .empty-cart i { font-size: 4rem; color: #ddd; margin-bottom: 16px; }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold"><i class="fas fa-shopping-cart me-2"></i>Shopping Cart</h1>
        <p class="mb-0" style="opacity:.9;">Review your items before checkout</p>
    </div>
</section>

<section class="container section-padding cart-container">
    <div class="row g-4" id="cartWrapper">
        <!-- Will be rendered by JS -->
    </div>
</section>
@endsection

@section('scripts')
<script>
function renderCart() {
    const cart = JSON.parse(localStorage.getItem('gasgo_cart')) || [];
    const wrapper = document.getElementById('cartWrapper');

    if (cart.length === 0) {
        wrapper.innerHTML = `
            <div class="col-12">
                <div class="cart-card">
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <h4 class="fw-bold" style="color:var(--gasgo-blue);">Your Cart is Empty</h4>
                        <p class="text-muted">Browse our products and add items to your cart</p>
                        <a href="{{ url('/customer/product') }}" class="btn btn-gasgo mt-2"><i class="fas fa-fire me-2"></i>Browse Products</a>
                    </div>
                </div>
            </div>`;
        return;
    }

    let subtotal = 0;
    let itemsHtml = '';
    cart.forEach((item, idx) => {
        const sub = item.price * item.quantity;
        subtotal += sub;
        const imgSrc = item.image || "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='70' height='70'%3E%3Crect width='70' height='70' fill='%23e8f4fc'/%3E%3C/svg%3E";
        itemsHtml += `
        <div class="cart-item" data-index="${idx}">
            <img src="${imgSrc}" alt="${item.name}">
            <div class="item-details">
                <div class="item-name">${item.name}</div>
                <div class="item-price">₱${item.price.toLocaleString('en-PH', {minimumFractionDigits:2})}</div>
            </div>
            <div class="qty-control">
                <button onclick="changeQty(${idx},-1)"><i class="fas fa-minus" style="font-size:.7rem;"></i></button>
                <span>${item.quantity}</span>
                <button onclick="changeQty(${idx},1)"><i class="fas fa-plus" style="font-size:.7rem;"></i></button>
            </div>
            <div class="item-subtotal">₱${sub.toLocaleString('en-PH', {minimumFractionDigits:2})}</div>
            <button class="remove-btn" onclick="removeItem(${idx})"><i class="fas fa-trash-alt"></i></button>
        </div>`;
    });

    const deliveryFee = 50;
    const total = subtotal + deliveryFee;

    wrapper.innerHTML = `
        <div class="col-lg-8">
            <div class="cart-card">
                <div style="padding:20px 24px 10px;border-bottom:1px solid #f0f0f0;">
                    <h5 class="fw-bold mb-0" style="color:var(--gasgo-blue);"><i class="fas fa-box me-2"></i>Cart Items (${cart.length})</h5>
                </div>
                ${itemsHtml}
            </div>
        </div>
        <div class="col-lg-4">
            <div class="cart-summary">
                <h5><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                <div class="summary-row"><span>Subtotal</span><span>₱${subtotal.toLocaleString('en-PH',{minimumFractionDigits:2})}</span></div>
                <div class="summary-row"><span>Delivery Fee</span><span>₱${deliveryFee.toFixed(2)}</span></div>
                <div class="summary-row total"><span>Total</span><span class="value">₱${total.toLocaleString('en-PH',{minimumFractionDigits:2})}</span></div>
                <a href="{{ url('/customer/checkout') }}" class="btn btn-gasgo w-100 mt-3 btn-checkout-sync">
                    <i class="fas fa-lock me-2"></i>Proceed to Checkout
                </a>
                <a href="{{ url('/customer/product') }}" class="btn btn-gasgo-outline w-100 mt-2" style="padding:12px;">
                    <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                </a>
            </div>
        </div>`;

    // Attach checkout handler
    const checkoutBtn = document.querySelector('.btn-checkout-sync');
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            syncCartAndCheckout();
        });
    }
}

function changeQty(index, delta) {
    let cart = JSON.parse(localStorage.getItem('gasgo_cart')) || [];
    cart[index].quantity = Math.max(1, cart[index].quantity + delta);
    localStorage.setItem('gasgo_cart', JSON.stringify(cart));
    renderCart(); updateCartCount();
}

function removeItem(index) {
    let cart = JSON.parse(localStorage.getItem('gasgo_cart')) || [];
    cart.splice(index, 1);
    localStorage.setItem('gasgo_cart', JSON.stringify(cart));
    renderCart(); updateCartCount();
}

function syncCartAndCheckout() {
    const cart = JSON.parse(localStorage.getItem('gasgo_cart')) || [];
    if (!cart.length) return;

    // Build a hidden form to POST cart items to the sync endpoint
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("customer.cart.sync") }}';

    // CSRF token
    const csrf = document.createElement('input');
    csrf.type = 'hidden';
    csrf.name = '_token';
    csrf.value = '{{ csrf_token() }}';
    form.appendChild(csrf);

    // Add each cart item
    cart.forEach((item, i) => {
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'items[' + i + '][product_id]';
        idInput.value = item.id;
        form.appendChild(idInput);

        const qtyInput = document.createElement('input');
        qtyInput.type = 'hidden';
        qtyInput.name = 'items[' + i + '][quantity]';
        qtyInput.value = item.quantity;
        form.appendChild(qtyInput);
    });

    document.body.appendChild(form);
    form.submit();
}

renderCart();
</script>
@endsection
