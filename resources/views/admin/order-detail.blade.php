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
                    <div style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                        <span class="badge-status badge-{{ $order->status }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                        @if($order->is_urgent)
                            <span class="badge" style="background:#dc3545; color:white; font-size:.85rem; font-weight:600; padding:6px 12px;"><i class="fas fa-bolt me-1"></i>URGENT</span>
                        @endif
                    </div>
                </div>
                <div class="detail-col">
                    <span class="detail-label">Transaction Type</span>
                    <div class="detail-value">{{ ucfirst(str_replace('_', ' ', $order->transaction_type ?? 'exchange')) }}</div>
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
            @php
                $homepageSettings = \App\Models\HomepageSetting::singleton();
                $availablePaymentMethods = collect($homepageSettings->availablePaymentMethods());
                $selectedPaymentMethod = $availablePaymentMethods->firstWhere('key', $order->payment_method);
                $proofImageUrl = $order->payment && filled($order->payment->proof_of_payment)
                    ? asset('storage/' . ltrim($order->payment->proof_of_payment, '/'))
                    : null;
            @endphp
            
            <!-- Payment Proof Image - Top Section -->
            @if($proofImageUrl)
                <div class="detail-row" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #e9ecef;">
                    <div class="detail-col" style="width: 100%;">
                        <span class="detail-label">
                            <i class="fas fa-receipt" style="color: var(--gasgo-orange); margin-right: 6px;"></i>Payment Proof
                        </span>
                        <div style="margin-top: 12px;">
                            <img src="{{ $proofImageUrl }}" alt="Proof of Payment" class="proof-image" style="max-width: 100%; height: auto; cursor: pointer;" onclick="this.style.transform='scale(1.05)'; setTimeout(() => this.style.transform='scale(1)', 200);">
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Method</span>
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="badge {{ $order->payment_method === 'gcash' ? 'bg-success' : 'bg-secondary' }}">
                            {{ ucfirst($order->payment_method) }}
                        </span>
                        @if(!empty($selectedPaymentMethod['image_url']))
                            <div style="width:64px;height:64px;border:1px solid #dee2e6;border-radius:12px;padding:6px;background:#fff;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                                <img src="{{ $selectedPaymentMethod['image_url'] }}" alt="{{ $selectedPaymentMethod['label'] }}" style="width:100%;height:100%;object-fit:contain;">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @if($selectedPaymentMethod)
                <div class="detail-row">
                    <div class="detail-col">
                        <span class="detail-label">Account Name</span>
                        <div class="detail-value">{{ $selectedPaymentMethod['account_name'] ?: 'N/A' }}</div>
                    </div>
                    <div class="detail-col">
                        <span class="detail-label">Account Number</span>
                        <div class="detail-value">{{ $selectedPaymentMethod['account_number'] ?: 'N/A' }}</div>
                    </div>
                </div>
            @endif
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
            @if($order->discount > 0)
                <div class="detail-row">
                    <div class="detail-col">
                        <span class="detail-label">Discount Applied</span>
                        <div class="detail-value text-danger">-₱{{ number_format($order->discount, 2) }}</div>
                    </div>
                </div>
            @endif
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Total Amount</span>
                    <div class="detail-value" style="font-size: 1.3rem; font-weight: 700; color: var(--gasgo-orange);">₱{{ number_format($order->total_amount, 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        @if($order->status === 'pending')
            <div class="detail-card" style="background: #f8f9fa; border: 2px dashed #28a745;">
                <h5 style="color: #28a745;"><i class="fas fa-check-circle"></i>Approve Order</h5>
                <p class="text-muted mb-3" style="font-size: .9rem;">Approve this order so it can be assigned to a rider.</p>
                <form action="{{ route('admin.orders.status', $order) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Transaction Type</label>
                        <select name="transaction_type" class="form-select" required>
                            <option value="exchange" {{ ($order->transaction_type ?? 'exchange') === 'exchange' ? 'selected' : '' }}>Exchange</option>
                            <option value="new_cylinder" {{ ($order->transaction_type ?? '') === 'new_cylinder' ? 'selected' : '' }}>New Cylinder</option>
                            <option value="not_tank" {{ ($order->transaction_type ?? '') === 'not_tank' ? 'selected' : '' }}>Not Tank</option>
                        </select>
                        <small class="text-muted">Set this before approval to control inventory behavior at delivery completion.</small>
                    </div>
                    <input type="hidden" name="status" value="approved">
                    <button type="submit" class="btn btn-gasgo w-100" style="background:#28a745; color:#fff; font-weight:700;">
                        <i class="fas fa-check me-2"></i>Approve Order
                    </button>
                </form>
            </div>
        @elseif($order->status === 'approved')
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

<!-- Assign Rider Modal -->
<div class="modal fade" id="assignRiderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius:16px;">
            <form id="assignRiderForm" method="POST" action="{{ route('admin.deliveries.store') }}">
                @csrf
                <input type="hidden" name="order_id" id="assignOrderId" value="{{ $order->id }}">
                <div class="modal-header" style="border-bottom:none;">
                    <h5 class="modal-title fw-bold" style="color:var(--gasgo-blue);">
                        <i class="fas fa-motorcycle me-2" style="color:var(--gasgo-orange);"></i>Assign Rider
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted" style="font-size:.88rem;">
                        Assign a rider to order <strong id="assignOrderNumber">#{{ $order->order_number }}</strong>:
                    </p>
                    @if($riders->count() > 0)
                        <div class="list-group">
                            @foreach($riders as $rider)
                                <label class="list-group-item d-flex align-items-center gap-3 mb-2" style="border-radius:12px;cursor:pointer;">
                                    <input type="radio" name="rider_id" value="{{ $rider->user_id }}" class="form-check-input" required>
                                    <div>
                                        <div class="fw-bold">{{ $rider->user->name ?? 'Unknown' }}</div>
                                        <small class="text-muted">
                                            {{ $rider->vehicle_type ?? 'No vehicle info' }}
                                            @if($rider->plate_number) &bull; {{ $rider->plate_number }} @endif
                                        </small>
                                    </div>
                                    @if($rider->availability === 'available')
                                        <span class="badge bg-success ms-auto">Available</span>
                                    @elseif($rider->availability === 'busy')
                                        <span class="badge bg-warning text-dark ms-auto">Busy</span>
                                    @elseif($rider->availability === 'returning')
                                        <span class="badge bg-info ms-auto">Returning to Store</span>
                                    @else
                                        <span class="badge bg-secondary ms-auto">Offline</span>
                                    @endif
                                </label>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-warning" style="border-radius:12px;">
                            <i class="fas fa-exclamation-triangle me-2"></i>No riders are currently online. Please wait for a rider to come online.
                        </div>
                    @endif
                </div>
                <div class="modal-footer" style="border-top:none;">
                    <button type="button" class="btn" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                    @if($riders->count() > 0)
                        <button type="submit" class="btn" style="background:var(--gasgo-orange);color:#fff;border-radius:10px;font-weight:600;">
                            <i class="fas fa-check me-1"></i>Assign Rider
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const assignButton = document.querySelector('.assign-btn');
        if (assignButton) {
            assignButton.addEventListener('click', function() {
                const orderId = this.dataset.orderId;
                const orderNumber = this.dataset.orderNumber;
                openAssignModal(orderId, orderNumber);
            });
        }
    });

    function openAssignModal(orderId, orderNumber) {
        const orderInput = document.getElementById('assignOrderId');
        const orderLabel = document.getElementById('assignOrderNumber');
        if (orderInput) {
            orderInput.value = orderId;
        }
        if (orderLabel) {
            orderLabel.textContent = '#' + orderNumber;
        }
        document.querySelectorAll('#assignRiderForm input[name="rider_id"]').forEach(r => r.checked = false);
        const modalElement = document.getElementById('assignRiderModal');
        if (modalElement) {
            new bootstrap.Modal(modalElement).show();
        }
    }
</script>
@endsection
