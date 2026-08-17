@extends('layouts.admin')

@section('title', 'Order Details')
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

    @media (max-width: 768px) {
        .detail-card {
            padding: 14px 12px;
            border-radius: 14px;
            margin-bottom: 14px;
        }
        .detail-card h5 {
            font-size: 0.95rem;
            margin-bottom: 12px;
        }
        .detail-row {
            gap: 12px;
            margin-bottom: 10px;
        }
        .detail-col {
            min-width: 100%;
        }
        .detail-label {
            font-size: 0.8rem;
            margin-bottom: 2px;
        }
        .detail-value {
            font-size: 0.92rem;
        }
        .item-table th, .item-table td {
            padding: 8px;
            font-size: 0.82rem;
        }
        .proof-image {
            max-width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
        <h3 class="mb-0 fw-bold" style="font-size: 1.25rem;">Order #{{ $order->order_number ?? $order->id }}</h3>
        <p class="text-muted mb-0" style="font-size: 0.8rem;">{{ $order->created_at ? $order->created_at->format('F j, Y - g:i A') : 'N/A' }}</p>
    </div>
    <a href="{{ route('admin.orders') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i>Back
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
                        <span class="badge-status badge-{{ $order->status ?? 'pending' }}">{{ ucfirst(str_replace('_', ' ', $order->status ?? 'pending')) }}</span>
                        @if($order->is_urgent)
                            <span class="badge" style="background:#dc3545; color:white; font-size:.85rem; font-weight:600; padding:6px 12px;"><i class="fas fa-bolt me-1"></i>URGENT</span>
                        @endif
                    </div>
                </div>
                <div class="detail-col">
                    <span class="detail-label">Transaction Type</span>
                    <div class="detail-value mt-1">
                        @php
                            $txType = $order->transaction_type ?? 'exchange';
                        @endphp
                        @if($txType === 'exchange')
                            <span class="badge" style="background:#e8f4fc;color:#1a6db0;font-size:.85rem;font-weight:600;padding:6px 12px;">
                                <i class="fas fa-exchange-alt me-1"></i>Exchange
                            </span>
                        @elseif($txType === 'new_cylinder')
                            <span class="badge" style="background:#fff5e6;color:#e07d0a;font-size:.85rem;font-weight:600;padding:6px 12px;">
                                <i class="fas fa-plus-circle me-1"></i>New Cylinder
                            </span>
                        @elseif($txType === 'not_tank')
                            <span class="badge" style="background:#f1f5f9;color:#64748b;font-size:.85rem;font-weight:600;padding:6px 12px;">
                                <i class="fas fa-box me-1"></i>Non-Cylinder Item
                            </span>
                        @else
                            <span class="badge" style="background:#f1f5f9;color:#475569;font-size:.85rem;font-weight:600;padding:6px 12px;">
                                <i class="fas fa-box me-1"></i>{{ ucfirst(str_replace('_', ' ', $txType)) }}
                            </span>
                        @endif
                    </div>
                </div>
                @if($order->delivery)
                    <div class="detail-col">
                        <span class="detail-label">Assigned Rider</span>
                        <div class="detail-value">{{ $order->delivery->rider?->name ?? 'N/A' }}</div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Order Items -->
        <div class="detail-card">
            <h5><i class="fas fa-box"></i>Order Items</h5>
            <div class="table-responsive">
                <table class="item-table">
                    <thead>
                        <tr>
                            <th style="width:60px;">Item</th>
                            <th>Product Name</th>
                            <th style="width:80px;" class="text-center">Qty</th>
                            <th style="width:120px;" class="text-end">Price</th>
                            <th style="width:120px;" class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->orderItems as $item)
                            @php
                                $itemImg = $item->product?->resolved_image ?? $item->reward_image_url ?? asset('images/default-product.png');
                            @endphp
                            <tr>
                                <td>
                                    <div style="width:48px;height:48px;border-radius:10px;background:#f8f9fa;border:1px solid #eee;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                                        <img src="{{ $itemImg }}" alt="{{ $item->product_name }}" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.src='{{ asset('images/default-product.png') }}';">
                                    </div>
                                </td>
                                <td>
                                    <span class="item-name">{{ $item->product_name }}</span>
                                    @if($item->is_reward)
                                        <span class="reward-badge"><i class="fas fa-gift me-1"></i>REWARD</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                <td class="text-end">₱{{ number_format((float) ($item->price ?? 0), 2) }}</td>
                                <td class="text-end fw-bold">₱{{ number_format((float) ($item->subtotal ?? 0), 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">No items in this order</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Delivery Address -->
        <div class="detail-card">
            <h5><i class="fas fa-map-marker-alt"></i>Delivery Address</h5>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Recipient Name</span>
                    <div class="detail-value fw-bold">{{ $order->customer_name ?: ($order->user?->name ?? 'N/A') }}</div>
                </div>
                <div class="detail-col">
                    <span class="detail-label">Contact Number</span>
                    <div class="detail-value">
                        @if($order->contact_number)
                            <a href="tel:{{ $order->contact_number }}" class="text-decoration-none fw-semibold">
                                <i class="fas fa-phone-alt me-1 text-success"></i>{{ $order->contact_number }}
                            </a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-col" style="width:100%;">
                    <span class="detail-label">Complete Address</span>
                    <div class="detail-value p-3 rounded" style="background:#f8f9fa; border:1px solid #e9ecef;">
                        {{ $order->delivery_address ?: 'No address provided' }}
                    </div>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Delivery Notes / Landmarks</span>
                    <div class="detail-value">{{ $order->notes ?: 'No special instructions' }}</div>
                </div>
            </div>
            @if($order->latitude && $order->longitude)
                <div class="detail-row">
                    <div class="detail-col">
                        <span class="detail-label">GPS Location</span>
                        <div class="detail-value">
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $order->latitude }},{{ $order->longitude }}" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius:8px;">
                                <i class="fas fa-external-link-alt me-1"></i>View Pinned Location on Map ({{ $order->latitude }}, {{ $order->longitude }})
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Customer Information -->
        <div class="detail-card">
            <h5><i class="fas fa-user"></i>Customer Profile</h5>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Account Name</span>
                    <div class="detail-value fw-bold">{{ $order->user?->name ?? ($order->customer_name ?? 'Guest') }}</div>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Account Email</span>
                    <div class="detail-value">
                        @if($order->user?->email)
                            <a href="mailto:{{ $order->user->email }}" class="text-decoration-none">{{ $order->user->email }}</a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Primary Phone</span>
                    <div class="detail-value">
                        @if($order->contact_number)
                            <a href="tel:{{ $order->contact_number }}" class="text-decoration-none">{{ $order->contact_number }}</a>
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </div>
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
                $proofImageUrl = $order->payment?->proof_image_url ?? ($order->payment && filled($order->payment->proof_of_payment)
                    ? \Illuminate\Support\Facades\Storage::url(ltrim($order->payment->proof_of_payment, '/'))
                    : null);
                $paymentStatus = $order->payment->status ?? ($order->status === 'delivered' ? 'paid' : 'pending');
            @endphp
            
            <!-- Payment Proof Image - Top Section -->
            @if($proofImageUrl)
                <div class="detail-row" style="margin-bottom: 20px; padding-bottom: 20px; border-bottom: 1px solid #e9ecef;">
                    <div class="detail-col" style="width: 100%;">
                        <span class="detail-label">
                            <i class="fas fa-receipt" style="color: var(--gasgo-orange); margin-right: 6px;"></i>Proof of Payment
                        </span>
                        <div style="margin-top: 12px; text-align:center; background:#f8f9fa; padding:10px; border-radius:12px; border:1px solid #dee2e6;">
                            <a href="{{ $proofImageUrl }}" target="_blank" title="Click to view full image">
                                <img src="{{ $proofImageUrl }}" alt="Proof of Payment" class="proof-image" style="max-width: 100%; max-height:260px; object-fit:contain; border-radius:8px;">
                            </a>
                            <small class="d-block text-muted mt-2"><i class="fas fa-search-plus me-1"></i>Click image to open full size</small>
                        </div>
                    </div>
                </div>
            @endif
            
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Payment Method</span>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge {{ in_array($order->payment_method ?? '', ['gcash', 'online']) ? 'bg-info' : 'bg-success' }}" style="font-size:.85rem; padding:6px 12px;">
                            <i class="{{ in_array($order->payment_method ?? '', ['gcash', 'online']) ? 'fas fa-mobile-alt' : 'fas fa-money-bill-wave' }} me-1"></i>
                            {{ strtoupper($order->payment_method ?? 'CASH') }}
                        </span>
                        <span class="badge {{ $paymentStatus === 'paid' ? 'bg-success' : ($paymentStatus === 'failed' ? 'bg-danger' : 'bg-warning text-dark') }}" style="font-size:.85rem; padding:6px 12px;">
                            Status: {{ ucfirst($paymentStatus) }}
                        </span>
                    </div>
                </div>
                @if(!empty($selectedPaymentMethod['image_url']))
                    <div class="detail-col" style="flex:0 0 auto; min-width:auto;">
                        <div style="width:64px;height:64px;border:1px solid #dee2e6;border-radius:12px;padding:6px;background:#fff;overflow:hidden;display:flex;align-items:center;justify-content:center;">
                            <img src="{{ $selectedPaymentMethod['image_url'] }}" alt="{{ $selectedPaymentMethod['label'] ?? '' }}" style="width:100%;height:100%;object-fit:contain;" onerror="this.parentElement.parentElement.style.display='none';">
                        </div>
                    </div>
                @endif
            </div>

            @if($selectedPaymentMethod && (!empty($selectedPaymentMethod['account_name']) || !empty($selectedPaymentMethod['account_number'])))
                <div class="detail-row">
                    @if(!empty($selectedPaymentMethod['account_name']))
                        <div class="detail-col">
                            <span class="detail-label">Account Name</span>
                            <div class="detail-value">{{ $selectedPaymentMethod['account_name'] }}</div>
                        </div>
                    @endif
                    @if(!empty($selectedPaymentMethod['account_number']))
                        <div class="detail-col">
                            <span class="detail-label">Account Number</span>
                            <div class="detail-value">{{ $selectedPaymentMethod['account_number'] }}</div>
                        </div>
                    @endif
                </div>
            @endif
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Subtotal</span>
                    <div class="detail-value">₱{{ number_format((float) ($order->subtotal ?? 0), 2) }}</div>
                </div>
            </div>
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Delivery Fee</span>
                    <div class="detail-value">₱{{ number_format((float) ($order->delivery_fee ?? 0), 2) }}</div>
                </div>
            </div>
            @if(($order->discount ?? 0) > 0)
                <div class="detail-row">
                    <div class="detail-col">
                        <span class="detail-label">Discount Applied</span>
                        <div class="detail-value text-danger">-₱{{ number_format((float) $order->discount, 2) }}</div>
                    </div>
                </div>
            @endif
            <div class="detail-row">
                <div class="detail-col">
                    <span class="detail-label">Total Amount</span>
                    <div class="detail-value" style="font-size: 1.3rem; font-weight: 700; color: var(--gasgo-orange);">₱{{ number_format((float) ($order->total_amount ?? 0), 2) }}</div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        @if(($order->status ?? 'pending') === 'pending')
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
        @elseif(($order->status ?? '') === 'approved')
            <div class="detail-card" style="background: #f8f9fa; border: 2px dashed var(--gasgo-orange);">
                <h5 style="color: var(--gasgo-orange);"><i class="fas fa-motorcycle"></i>Assign Rider</h5>
                <p class="text-muted mb-3" style="font-size: .9rem;">Ready to assign this order to a rider?</p>
                <button class="btn btn-gasgo w-100 assign-btn" 
                    data-order-id="{{ $order->id }}" 
                    data-order-number="{{ $order->order_number ?? $order->id }}">
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
                        Assign a rider to order <strong id="assignOrderNumber">#{{ $order->order_number ?? $order->id }}</strong>:
                    </p>
                    @if($riders->count() > 0)
                        <div class="list-group">
                            @foreach($riders as $rider)
                                <label class="list-group-item d-flex align-items-center gap-3 mb-2" style="border-radius:12px;cursor:pointer;">
                                    <input type="radio" name="rider_id" value="{{ $rider->user_id }}" class="form-check-input" required>
                                    <div>
                                        <div class="fw-bold">{{ $rider->user?->name ?? 'Unknown' }}</div>
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
