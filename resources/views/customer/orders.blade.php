@extends('layouts.customer')

@section('title', 'GasGo - My Orders')
@section('nav-orders', 'active')

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
    .order-card {
        background: white; border-radius: 20px; overflow: hidden;
        box-shadow: 0 8px 30px rgba(0,0,0,.08); margin-bottom: 20px; transition: transform .3s;
    }
    .order-card:hover { transform: translateY(-4px); }
    .order-header {
        display: flex; justify-content: space-between; align-items: center;
        padding: 18px 24px; border-bottom: 1px solid #f0f0f0; flex-wrap: wrap; gap: 10px;
    }
    .order-header .order-id { font-weight: 700; color: var(--gasgo-blue); }
    .order-header .order-date { font-size: .85rem; color: #888; }
    .order-body { padding: 18px 24px; }
    .order-item-row { display: flex; align-items: center; gap: 14px; padding: 8px 0; }
    .order-item-row img { width: 50px; height: 50px; border-radius: 10px; object-fit: cover; background: var(--gasgo-blue-light); }
    .order-item-row .item-name { font-weight: 600; color: #333; font-size: .92rem; }
    .order-item-row .item-qty { font-size: .82rem; color: #888; }
    .order-footer {
        display: flex; justify-content: space-between; align-items: center;
        padding: 14px 24px; background: #fafafa; flex-wrap: wrap; gap: 10px;
    }
    .order-total { font-weight: 700; font-size: 1.1rem; color: var(--gasgo-orange); }
    .badge-status { padding: 6px 16px; border-radius: 20px; font-size: .78rem; font-weight: 600; }
    .badge-pending { background: #fff3cd; color: #856404; }
    .badge-approved { background: #d1ecf1; color: #0c5460; }
    .badge-assigned { background: #e8f4fc; color: #1a6db0; }
    .badge-out_for_delivery { background: #fff5e6; color: #e07d0a; }
    .badge-delivered { background: #d4edda; color: #155724; }
    .badge-cancelled { background: #f8d7da; color: #721c24; }
    .filter-tabs { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 24px; }
    .filter-tab {
        padding: 8px 20px; border-radius: 25px; font-weight: 600; font-size: .85rem;
        border: 2px solid #eee; background: white; color: #555; cursor: pointer; transition: all .25s;
    }
    .filter-tab:hover, .filter-tab.active { background: var(--gasgo-blue); color: white; border-color: var(--gasgo-blue); }
    .empty-orders { text-align: center; padding: 60px 20px; }
    .empty-orders i { font-size: 4rem; color: #ddd; margin-bottom: 14px; }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold"><i class="fas fa-receipt me-2"></i>My Orders</h1>
        <p class="mb-0" style="opacity:.9;">View your order history and reorder quickly</p>
    </div>
</section>

<section class="container section-padding" style="position:relative;z-index:2;">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" data-aos="fade-up">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="filter-tabs" data-aos="fade-up">
        <button class="filter-tab active" data-filter="all">All Orders</button>
        <button class="filter-tab" data-filter="pending">Pending</button>
        <button class="filter-tab" data-filter="approved">Approved</button>
        <button class="filter-tab" data-filter="out_for_delivery">Out for Delivery</button>
        <button class="filter-tab" data-filter="delivered">Delivered</button>
        <button class="filter-tab" data-filter="cancelled">Cancelled</button>
    </div>

    <div id="orderList">
        @forelse ($orders as $order)
        @php
            $statusIcons = [
                'pending' => 'fas fa-clock',
                'approved' => 'fas fa-thumbs-up',
                'assigned' => 'fas fa-user-check',
                'out_for_delivery' => 'fas fa-truck',
                'delivered' => 'fas fa-check-circle',
                'cancelled' => 'fas fa-ban',
            ];
            $statusLabels = [
                'pending' => 'Pending',
                'approved' => 'Approved',
                'assigned' => 'Assigned',
                'out_for_delivery' => 'Out for Delivery',
                'delivered' => 'Delivered',
                'cancelled' => 'Cancelled',
            ];
        @endphp
        <div class="order-card" data-status="{{ $order->status }}" data-aos="fade-up">
            <div class="order-header">
                <div>
                    <span class="order-id">Order #{{ $order->order_number }}</span>
                    <span class="order-date ms-3">{{ $order->created_at->format('M j, Y — g:i A') }}</span>
                </div>
                <span class="badge-status badge-{{ $order->status }}">
                    <i class="{{ $statusIcons[$order->status] ?? 'fas fa-info-circle' }} me-1"></i>{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                </span>
            </div>
            <div class="order-body">
                @foreach ($order->orderItems as $item)
                <div class="order-item-row">
                    <img src="{{ $item->product && $item->product->image ? asset($item->product->image) : asset('images/11kg.jpg') }}" alt="{{ $item->product_name }}">
                    <div>
                        <div class="item-name">{{ $item->product_name }}</div>
                        <div class="item-qty">Qty: {{ $item->quantity }} &times; ₱{{ number_format($item->price, 2) }}</div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="order-footer">
                <span class="order-total">Total: ₱{{ number_format($order->total_amount, 2) }}</span>
                <div class="d-flex gap-2">
                    @if(in_array($order->status, ['pending', 'approved', 'assigned', 'out_for_delivery']))
                        <a href="{{ route('customer.tracking', $order) }}" class="btn btn-gasgo btn-sm"><i class="fas fa-map-marker-alt me-1"></i>Track</a>
                    @endif
                    @if($order->status === 'delivered' || $order->status === 'cancelled')
                        @php
                            $reorderItems = $order->orderItems->map(function($i) {
                                return [
                                    'id' => $i->product_id,
                                    'name' => $i->product_name,
                                    'price' => (float)$i->price,
                                    'image' => $i->product && $i->product->image ? asset($i->product->image) : '',
                                    'quantity' => $i->quantity,
                                ];
                            })->values();
                        @endphp
                        <button class="btn btn-gasgo-outline btn-sm reorder-btn"
                            data-items='@json($reorderItems)'>
                            <i class="fas fa-redo me-1"></i>Reorder
                        </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="order-card" data-aos="fade-up">
            <div class="empty-orders">
                <i class="fas fa-receipt"></i>
                <h4 class="fw-bold" style="color:var(--gasgo-blue);">No Orders Yet</h4>
                <p class="text-muted">Start shopping and your orders will appear here</p>
                <a href="{{ route('customer.products') }}" class="btn btn-gasgo mt-2"><i class="fas fa-fire me-2"></i>Browse Products</a>
            </div>
        </div>
        @endforelse
    </div>
</section>
@endsection

@section('scripts')
@if(session('success'))
<script>
    // Clear localStorage cart after successful order placement
    localStorage.removeItem('gasgo_cart');
    if (typeof updateCartCount === 'function') updateCartCount();
</script>
@endif

<script>
// Filter tabs
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const filter = this.dataset.filter;
        document.querySelectorAll('.order-card').forEach(card => {
            card.style.display = (filter === 'all' || card.dataset.status === filter) ? '' : 'none';
        });
    });
});

// Reorder
function reorder(items) {
    let cart = JSON.parse(localStorage.getItem('gasgo_cart')) || [];
    items.forEach(function(item) {
        const existing = cart.find(c => c.id === item.id);
        if (existing) { existing.quantity += item.quantity; }
        else { cart.push(item); }
    });
    localStorage.setItem('gasgo_cart', JSON.stringify(cart));
    if (typeof updateCartCount === 'function') updateCartCount();
    window.location.href = '{{ url("/customer/productCart") }}';
}

document.querySelectorAll('.reorder-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var items = JSON.parse(this.dataset.items);
        reorder(items);
    });
});
</script>
@endsection
