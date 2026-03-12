@extends('layouts.customer')

@section('title', 'GasGo - Checkout')

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
    .checkout-card {
        background: white; border-radius: 20px; padding: 30px;
        box-shadow: 0 8px 30px rgba(0,0,0,.08); margin-bottom: 24px;
    }
    .checkout-card h5 { font-weight: 700; color: var(--gasgo-blue); margin-bottom: 20px; }
    .checkout-card h5 i { color: var(--gasgo-orange); margin-right: 8px; }
    .form-label { font-weight: 600; font-size: .9rem; color: #555; }
    .payment-option {
        border: 2px solid #eee; border-radius: 14px; padding: 16px 20px; cursor: pointer;
        display: flex; align-items: center; gap: 14px; transition: all .25s;
    }
    .payment-option:hover, .payment-option.selected {
        border-color: var(--gasgo-orange); background: var(--gasgo-orange-light);
    }
    .payment-option .pay-icon {
        width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; color: white;
    }
    .payment-option .pay-icon.cash { background: linear-gradient(135deg, #27ae60, #2ecc71); }
    .payment-option .pay-icon.gcash { background: linear-gradient(135deg, #007dfe, #00b0ff); }

    /* Summary sidebar */
    .order-summary {
        background: white; border-radius: 20px; padding: 28px;
        box-shadow: 0 8px 30px rgba(0,0,0,.08); position: sticky; top: 100px;
    }
    .order-summary h5 { font-weight: 700; color: var(--gasgo-blue); border-bottom: 2px solid #f0f0f0; padding-bottom: 14px; }
    .summary-item { display: flex; justify-content: space-between; padding: 8px 0; font-size: .9rem; }
    .summary-item.total {
        font-size: 1.15rem; font-weight: 700; border-top: 2px solid var(--gasgo-orange);
        margin-top: 8px; padding-top: 14px;
    }
    .summary-item.total .val { color: var(--gasgo-orange); }
    .order-item-mini { display: flex; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f8f8f8; }
    .order-item-mini img { width: 46px; height: 46px; border-radius: 8px; object-fit: cover; background: var(--gasgo-blue-light); }
    .order-item-mini .name { font-weight: 600; font-size: .85rem; color: #333; }
    .order-item-mini .qty { font-size: .78rem; color: #888; }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold"><i class="fas fa-clipboard-check me-2"></i>Checkout</h1>
        <p class="mb-0" style="opacity:.9;">Complete your order details</p>
    </div>
</section>

<section class="container section-padding" style="position:relative;z-index:2;">
    <form action="{{ route('customer.order.store') }}" method="POST" id="checkoutForm">
        @csrf
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Delivery Address -->
                <div class="checkout-card">
                    <h5><i class="fas fa-map-marker-alt"></i>Delivery Address</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control form-control-gasgo" value="{{ Auth::user()->name }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-control form-control-gasgo" name="contact_number" value="{{ old('contact_number', Auth::user()->phone) }}" placeholder="09XX XXX XXXX" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Complete Address</label>
                            <textarea class="form-control form-control-gasgo" name="delivery_address" rows="3" placeholder="House/Unit No., Street, Barangay, City/Municipality" required>{{ old('delivery_address', Auth::user()->address) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Delivery Notes (optional)</label>
                            <input type="text" class="form-control form-control-gasgo" name="notes" value="{{ old('notes') }}" placeholder="Landmark, gate color, etc.">
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="checkout-card">
                    <h5><i class="fas fa-credit-card"></i>Payment Method</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="payment-option selected" onclick="selectPayment(this,'cash')">
                                <div class="pay-icon cash"><i class="fas fa-money-bill-wave"></i></div>
                                <div>
                                    <div class="fw-bold">Cash on Delivery</div>
                                    <small class="text-muted">Pay when you receive your order</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="payment-option" onclick="selectPayment(this,'gcash')">
                                <div class="pay-icon gcash"><i class="fas fa-mobile-alt"></i></div>
                                <div>
                                    <div class="fw-bold">GCash</div>
                                    <small class="text-muted">Pay via GCash e-wallet</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="payment_method" id="paymentMethod" value="cash">
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-lg-4">
                <div class="order-summary">
                    <h5><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                    @foreach ($cartItems as $item)
                    <div class="order-item-mini">
                        <img src="{{ $item->product->image ? asset($item->product->image) : '' }}" alt="{{ $item->product->name }}">
                        <div class="flex-grow-1">
                            <div class="name">{{ $item->product->name }}</div>
                            <div class="qty">Qty: {{ $item->quantity }} &times; ₱{{ number_format($item->product->price, 2) }}</div>
                        </div>
                        <div class="fw-bold" style="font-size:.9rem;">₱{{ number_format($item->product->price * $item->quantity, 2) }}</div>
                    </div>
                    @endforeach
                    <div class="summary-item mt-2"><span>Subtotal</span><span>₱{{ number_format($subtotal, 2) }}</span></div>
                    <div class="summary-item"><span>Delivery Fee</span><span>₱50.00</span></div>
                    <div class="summary-item total"><span>Total</span><span class="val">₱{{ number_format($subtotal + 50, 2) }}</span></div>
                    <button type="submit" class="btn btn-gasgo w-100 mt-3">
                        <i class="fas fa-check-circle me-2"></i>Place Order
                    </button>
                    <a href="{{ route('customer.cart') }}" class="btn btn-gasgo-outline w-100 mt-2" style="padding:12px;">
                        <i class="fas fa-arrow-left me-2"></i>Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </form>
</section>
@endsection

@section('scripts')
<script>
function selectPayment(el, method) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('paymentMethod').value = method;
}
</script>

@if(session('success'))
<script>
    // Clear localStorage cart after successful order placement
    localStorage.removeItem('gasgo_cart');
    if (typeof updateCartCount === 'function') updateCartCount();
</script>
@endif
@endsection
