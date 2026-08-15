@extends('layouts.customer')

@section('title', 'My Orders')
@section('nav-orders', 'active')

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
    .order-item-row img { width: 50px; height: 50px; border-radius: 10px; object-fit: contain; background: #fff; }
    .order-item-row .item-name { font-weight: 600; color: #333; font-size: .92rem; }
    .order-item-row .item-qty { font-size: .82rem; color: #888; }
    .order-item-row.reward-item { background: #f0f9ff; border-left: 3px solid #28a745; padding: 10px 12px; border-radius: 8px; }
    .order-item-row.reward-item .item-name { color: #155724; }
    .reward-badge { display: inline-block; background: #28a745; color: white; padding: 4px 12px; border-radius: 12px; font-size: .7rem; font-weight: 700; margin-left: 8px; }
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

    @media (max-width: 767.98px) {
        .order-header, .order-body, .order-footer {
            padding: 14px 16px;
        }
        .filter-tabs {
            overflow-x: auto;
            flex-wrap: nowrap;
            padding-bottom: 8px;
            -webkit-overflow-scrolling: touch;
        }
        .filter-tab {
            white-space: nowrap;
            flex-shrink: 0;
            padding: 8px 16px;
        }
        .order-header .order-date {
            margin-left: 0 !important;
            display: block;
            margin-top: 4px;
        }
    }
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
                    @php $txType = $order->transaction_type ?? 'exchange'; @endphp
                    @if($txType === 'exchange')
                        <span class="badge ms-2" style="background:#e8f4fc;color:#1a6db0;font-size:.72rem;font-weight:600;"><i class="fas fa-exchange-alt me-1"></i>Exchange</span>
                    @elseif($txType === 'new_cylinder')
                        <span class="badge ms-2" style="background:#fff5e6;color:#e07d0a;font-size:.72rem;font-weight:600;"><i class="fas fa-plus-circle me-1"></i>New Cylinder</span>
                    @else
                        <span class="badge ms-2" style="background:#f1f5f9;color:#475569;font-size:.72rem;font-weight:600;"><i class="fas fa-box me-1"></i>{{ ucfirst(str_replace('_', ' ', $txType)) }}</span>
                    @endif
                </div>
                <span class="badge-status badge-{{ $order->status }}">
                    <i class="{{ $statusIcons[$order->status] ?? 'fas fa-info-circle' }} me-1"></i>{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                </span>
            </div>
            <div class="order-body">
                @foreach ($order->orderItems as $item)
                @php
                    // For reward items, use the stored image URL; for regular items, use product image
                    $itemImage = null;
                    
                    if ($item->is_reward) {
                        // First try the stored reward_image_url
                        if ($item->reward_image_url) {
                            $itemImage = $item->reward_image_url;
                        } else {
                            // Fallback for legacy: try to get from product relationship
                            $itemImage = $item->product?->resolved_image;
                        }
                    } else {
                        // For regular items, always use product image
                        $itemImage = $item->product?->resolved_image;
                    }
                @endphp
                <div class="order-item-row @if($item->is_reward) reward-item @endif">
                    @if($itemImage)
                        <img src="{{ $itemImage }}" alt="{{ $item->product_name }}">
                    @else
                        <div class="text-muted small" style="padding: 8px; background: #f8f9fa; border-radius: 8px; min-width: 60px; display: flex; align-items: center; justify-content: center;">
                            No image
                        </div>
                    @endif
                    <div>
                        <div class="item-name">
                            {{ $item->product_name }}
                            @if($item->is_reward)
                                <span class="reward-badge"><i class="fas fa-gift me-1"></i>FREE</span>
                            @endif
                        </div>
                        <div class="item-qty">
                            Qty: {{ $item->quantity }}
                            @if(!$item->is_reward)
                                &times; ₱{{ number_format($item->price, 2) }}
                            @else
                                &times; ₱0.00
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="order-footer">
                <div>
                    <span class="order-total">Total: ₱{{ number_format($order->total_amount, 2) }}</span>
                    <div style="font-size:.85rem;color:#555;margin-top:4px;">Delivery Fee: ₱{{ number_format($order->delivery_fee, 2) }}</div>
                    @if ((float) $order->discount > 0)
                        <div style="font-size:.8rem;color:#1e7e34;font-weight:600;">
                            <i class="fas fa-tag me-1"></i>Reward Discount Applied: ₱{{ number_format($order->discount, 2) }}
                        </div>
                    @endif
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#orderModal{{ $order->id }}">
                        <i class="fas fa-info-circle me-1"></i>Details
                    </button>
                    @if(in_array($order->status, ['pending', 'approved', 'assigned', 'out_for_delivery']))
                        <a href="{{ route('customer.tracking', $order) }}" class="btn btn-gasgo btn-sm"><i class="fas fa-map-marker-alt me-1"></i>Track</a>
                    @endif
                    @if($order->status === 'delivered' || $order->status === 'cancelled')
                        @php
                            $reorderItems = $order->orderItems
                                ->where('is_reward', false)
                                ->map(function($i) {
                                    return [
                                        'product_id' => $i->product_id,
                                        'name' => $i->product_name,
                                        'price' => (float)$i->price,
                                        'image' => $i->product?->resolved_image ?? '',
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

        <!-- Customer Order Details Modal -->
        <div class="modal fade" id="orderModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content" style="border-radius:16px; overflow:hidden;">
                    <div class="modal-header" style="background:linear-gradient(135deg, var(--gasgo-blue), #2196f3); color:#fff;">
                        <h6 class="modal-title fw-bold"><i class="fas fa-receipt me-2"></i>Order #{{ $order->order_number }} Details</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="text-muted small">Status</label>
                                <div>
                                    <span class="badge-status badge-{{ $order->status }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                                    @if($order->is_urgent)
                                        <span class="badge bg-danger ms-1"><i class="fas fa-bolt me-1"></i>URGENT</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">Transaction Type</label>
                                <div>
                                    @if($txType === 'exchange')
                                        <span class="badge" style="background:#e8f4fc;color:#1a6db0;font-size:.82rem;font-weight:600;padding:6px 12px;"><i class="fas fa-exchange-alt me-1"></i>Exchange</span>
                                    @elseif($txType === 'new_cylinder')
                                        <span class="badge" style="background:#fff5e6;color:#e07d0a;font-size:.82rem;font-weight:600;padding:6px 12px;"><i class="fas fa-plus-circle me-1"></i>New Cylinder</span>
                                    @else
                                        <span class="badge" style="background:#f1f5f9;color:#475569;font-size:.82rem;font-weight:600;padding:6px 12px;"><i class="fas fa-box me-1"></i>{{ ucfirst(str_replace('_', ' ', $txType)) }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">Placed On</label>
                                <div class="fw-semibold">{{ $order->created_at->format('F j, Y - g:i A') }}</div>
                            </div>
                            <div class="col-sm-6">
                                <label class="text-muted small">Payment Method</label>
                                <div class="fw-semibold">{{ strtoupper($order->payment_method ?? 'CASH') }}</div>
                            </div>
                        </div>

                        <div class="p-3 mb-3 rounded" style="background:#f8f9fa; border:1px solid #e9ecef;">
                            <h6 class="fw-bold mb-2" style="color:var(--gasgo-blue); font-size:.9rem;"><i class="fas fa-map-marker-alt me-2"></i>Delivery Address</h6>
                            <div class="fw-bold" style="font-size:.92rem;">{{ $order->customer_name ?: ($order->user?->name ?? 'Customer') }}</div>
                            <div class="text-muted small">{{ $order->delivery_address }}</div>
                            @if($order->contact_number)
                                <div class="small mt-1"><i class="fas fa-phone-alt me-1 text-success"></i>{{ $order->contact_number }}</div>
                            @endif
                            @if($order->notes)
                                <div class="small text-muted mt-1"><strong>Notes:</strong> {{ $order->notes }}</div>
                            @endif
                        </div>

                        <h6 class="fw-bold mb-2" style="color:var(--gasgo-blue); font-size:.9rem;"><i class="fas fa-box me-2"></i>Ordered Items</h6>
                        <div class="table-responsive mb-3">
                            <table class="table table-sm align-middle">
                                <thead style="background:#f1f5f9;">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->orderItems as $item)
                                        <tr>
                                            <td>
                                                <span class="fw-semibold">{{ $item->product_name }}</span>
                                                @if($item->is_reward)
                                                    <span class="badge bg-success ms-1" style="font-size:.68rem;">FREE</span>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-end">₱{{ number_format((float) ($item->is_reward ? 0 : $item->price), 2) }}</td>
                                            <td class="text-end fw-bold">₱{{ number_format((float) ($item->is_reward ? 0 : $item->subtotal), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="p-3 rounded" style="background:#f8f9fa; border:1px solid #e9ecef;">
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="text-muted">Subtotal:</span>
                                <span>₱{{ number_format((float) ($order->subtotal ?? 0), 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="text-muted">Delivery Fee:</span>
                                <span>₱{{ number_format((float) ($order->delivery_fee ?? 0), 2) }}</span>
                            </div>
                            @if((float) ($order->discount ?? 0) > 0)
                            <div class="d-flex justify-content-between mb-1 small text-success">
                                <span>Discount:</span>
                                <span>-₱{{ number_format((float) $order->discount, 2) }}</span>
                            </div>
                            @endif
                            <div class="d-flex justify-content-between pt-2 border-top fw-bold" style="font-size:1.05rem; color:var(--gasgo-orange);">
                                <span>Total Amount:</span>
                                <span>₱{{ number_format((float) ($order->total_amount ?? 0), 2) }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" style="border-top:none;">
                        @if(in_array($order->status, ['pending', 'approved', 'assigned', 'out_for_delivery']))
                            <a href="{{ route('customer.tracking', $order) }}" class="btn btn-gasgo btn-sm"><i class="fas fa-map-marker-alt me-1"></i>Track Order</a>
                        @endif
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
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

// Reorder function
function reorder(items) {
    if (!Array.isArray(items) || !items.length) {
        alert('No items to reorder');
        return;
    }

    // Store item IDs in session storage to auto-select them on cart page
    const itemIds = items.map(item => String(item.product_id));
    sessionStorage.setItem('reorderedItems', JSON.stringify(itemIds));

    syncCartAjax(items).then(() => {
        // Redirect to cart page after a short delay to show success message
        setTimeout(() => {
            window.location.href = '{{ route("customer.cart") }}';
        }, 1000);
    }).catch(error => {
        // Error already shown in toast, clear session storage
        sessionStorage.removeItem('reorderedItems');
    });
}

document.querySelectorAll('.reorder-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        var items = JSON.parse(this.dataset.items);
        reorder(items);
    });
});
</script>
@endsection
