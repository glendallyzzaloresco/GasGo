@extends('layouts.admin')

@section('title', 'Order Details - GasGo Admin')
@section('page-title', 'Order Details')

@section('admin-styles')
<style>
    .detail-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        border: 1px solid #e9ecef;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
    }
    .detail-card h5 {
        font-weight: 700;
        margin-bottom: 16px;
        color: var(--gasgo-blue);
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .detail-card h5 i {
        color: var(--gasgo-orange);
    }
    .detail-row {
        display: flex;
        gap: 30px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .detail-col {
        flex: 1;
        min-width: 250px;
    }
    .detail-label {
        font-weight: 600;
        color: #666;
        font-size: .9rem;
        margin-bottom: 6px;
        display: block;
    }
    .detail-value {
        font-size: 1rem;
        color: #222;
        line-height: 1.5;
    }
    .badge-status {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: .85rem;
    }
    .badge-status.badge-pending { background: #fff3cd; color: #856404; }
    .badge-status.badge-approved { background: #cfe2ff; color: #084298; }
    .badge-status.badge-assigned { background: #d1ecf1; color: #0c5460; }
    .badge-status.badge-out_for_delivery { background: #fff3cd; color: #856404; }
    .badge-status.badge-delivered { background: #d4edda; color: #155724; }
    .badge-status.badge-cancelled { background: #f8d7da; color: #721c24; }

    .item-table {
        width: 100%;
        border-collapse: collapse;
    }
    .item-table th {
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #555;
        border-bottom: 2px solid #dee2e6;
        font-size: .9rem;
    }
    .item-table td {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
    }
    .item-name {
        font-weight: 600;
        color: #222;
    }
    .reward-badge {
        display: inline-block;
        background: #d4edda;
        color: #155724;
        padding: 4px 10px;
        border-radius: 12px;
        font-size: .75rem;
        font-weight: 600;
        margin-left: 8px;
    }
    .proof-image {
        max-width: 300px;
        border-radius: 12px;
        border: 2px solid #dee2e6;
        margin-top: 10px;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Order #{{ $order->order_number }}</h2>
        <p class="text-muted mb-0">{{ $order->created_at->format('F j, Y - g:i A') }}</p>
    </div>
    <a href="{{ route('admin.orders') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Back to Orders
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <!-- Order Status -->
        <div class="detail-card">
            <h5><i class="fas fa-info-circle"></i>Order Status</h5>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Current Status</span>
                    <span class="badge-status badge-{{ $order->status }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                </div>
                @if($order->delivery)
                    <div class="detail-col">
                        <span class="detail-label">Assigned Rider</span>
                        <div class="detail-value">{{ $order->delivery->rider->name ?? 'N/A' }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Order Items -->
        <div class="detail-card">
            <h5><i class="fas fa-box"></i>Order Items</h5>
            <table class="item-table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th style="width:80px;">Qty</th>
                        <th style="width:120px;">Price</th>
                        <th style="width:120px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->orderItems as $item)
                        <tr>
                            <td><span class="item-name">{{ $item->product_name }}</span>@if($item->is_reward)<span class="reward-badge">REWARD</span>@endif</td>
                            <td>{{ $item->quantity }}</td>
                            <td>₱{{ number_format($item->price, 2) }}</td>
                            <td>₱{{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">No items in this order</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Delivery Address -->
        <div class="detail-card">
            <h5><i class="fas fa-map-marker-alt"></i>Delivery Address</h5>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Address</span>
                    <div class="detail-value">{{ $order->delivery_address }}</div>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Contact Number</span>
                    <div class="detail-value">{{ $order->contact_number }}</div>
                </div>
                <div class="detail-col">
                    <span class="detail-label">Delivery Notes</span>
                    <div class="detail-value">{{ $order->notes ?: '—' }}</div>
                </div>
            </div>
            @if($order->latitude && $order->longitude)
                <div class="detail-row">
                    <div class="detail-col">
                        <span class="detail-label">GPS Coordinates</span>
                        <div class="detail-value">{{ $order->latitude }}, {{ $order->longitude }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Customer Information -->
        <div class="detail-card">
            <h5><i class="fas fa-user"></i>Customer</h5>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Name</span>
                    <div class="detail-value">{{ $order->user->name }}</div>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Email</span>
                    <div class="detail-value"><a href="mailto:{{ $order->user->email }}">{{ $order->user->email }}</a></div>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Phone</span>
                    <div class="detail-value"><a href="tel:{{ $order->contact_number }}">{{ $order->contact_number }}</a></div>
                </div>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="detail-card">
            <h5><i class="fas fa-credit-card"></i>Payment</h5>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Method</span>
                    <span class="badge {{ $order->payment_method === 'gcash' ? 'bg-success' : 'bg-secondary' }}">
                        {{ ucfirst($order->payment_method) }}
                    </span>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Subtotal</span>
                    <div class="detail-value">₱{{ number_format($order->subtotal, 2) }}</div>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Delivery Fee</span>
                    <div class="detail-value">₱{{ number_format($order->delivery_fee, 2) }}</div>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label" style="font-size: 1rem;">Total Amount</span>
                    <div class="detail-value" style="font-size: 1.3rem; font-weight: 700; color: var(--gasgo-orange);">₱{{ number_format($order->total_amount, 2) }}</div>
                </div>
            </div>

            @if($order->payment && $order->payment->proof_of_payment)
                <div class="detail-row" style="border-top: 1px solid #e9ecef; padding-top: 16px; margin-top: 16px;">
                    <div class="detail-col">
                        <span class="detail-label">Proof of Payment</span>
                        <img src="{{ asset('storage/' . $order->payment->proof_of_payment) }}" alt="Proof of Payment" class="proof-image">
                    </div>
                </div>
            @endif
        </div>

        <!-- Actions -->
        @if($order->status === 'pending' || $order->status === 'approved')
            <div class="detail-card" style="background: #f8f9fa; border: 2px dashed var(--gasgo-orange);">
                <h5 style="color: var(--gasgo-orange);"><i class="fas fa-motorcycle"></i>Assign Rider</h5>
                <p class="text-muted mb-3" style="font-size: .9rem;">Ready to assign this order to a rider?</p>
                <button class="btn btn-gasgo w-100 assign-btn" 
                    data-order-id="{{ $order->id }}" 
                    data-order-number="{{ $order->order_number }}">
                    <i class="fas fa-motorcycle me-2"></i>Assign Rider
                </button>
            </div>
        @endif
    </div>
</div>

@endsection

@push('scripts')
<script>
document.querySelectorAll('.assign-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const orderId = this.dataset.orderId;
        const orderNumber = this.dataset.orderNumber;
        
        // Trigger the modal or action
        openAssignModal(orderId, orderNumber);
    });
});

function openAssignModal(orderId, orderNumber) {
    // This will trigger the assign rider modal from your existing code
    const assignBtn = document.querySelector(`[data-order-id="${orderId}"]`);
    if (assignBtn && assignBtn.classList.contains('assign-btn')) {
        assignBtn.click();
    }
}
</script>
@endpush
