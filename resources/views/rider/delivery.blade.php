@extends('layouts.rider')

@section('title', 'GasGo Rider - Active Delivery')
@section('page-title', 'Active Delivery')

@section('rider-styles')
<style>
    .delivery-page {
        max-width: 980px;
        margin: 0 auto;
    }

    .delivery-page .rider-card {
        overflow: visible;
    }

    /* Location Badge */
    .rider-location-badge {
        display: inline-block;
        background: var(--gasgo-orange);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 10px;
    }

    /* Customer Section */
    .customer-section {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 20px;
        border: 1px solid rgba(26, 109, 176, 0.2);
    }

    .customer-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: var(--gasgo-blue);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .customer-info {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .customer-details {
        flex-grow: 1;
        min-width: 180px;
    }

    .order-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        flex-wrap: wrap;
    }

    .customer-name {
        font-weight: 600;
        color: var(--gasgo-blue);
        margin-bottom: 2px;
    }

    .customer-phone {
        font-size: 0.85rem;
        color: #666;
    }

    .contact-btn {
        background: var(--gasgo-blue);
        color: white;
        border: none;
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 1rem;
        cursor: pointer;
        transition: background 0.2s, transform 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(26, 109, 176, 0.25);
    }

    .contact-btn:hover {
        background: #1555a0;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(26, 109, 176, 0.35);
    }

    /* Section Headers */
    .section-header {
        color: var(--gasgo-blue);
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .section-header i {
        color: var(--gasgo-orange);
        font-size: 1.1rem;
    }

    /* Address Section */
    .address-section {
        margin-bottom: 20px;
    }

    .address-text {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 12px;
        line-height: 1.4;
    }

    /* Order Items */
    .items-container {
        background: linear-gradient(to bottom, #f8fbff 0%, #f0f6fb 100%);
        border-radius: 14px;
        padding: 16px;
        overflow: hidden;
        border: 1px solid #d5e8f7;
    }

    .item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px;
        border-bottom: 1px solid var(--admin-border);
        font-size: 0.9rem;
    }

    .item-row:last-child {
        border-bottom: none;
    }

    .item-quantity {
        font-weight: 600;
    }

    .item-price {
        font-weight: 600;
    }

    .total-row {
        background: rgba(26, 109, 176, 0.05);
        padding: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 2px solid var(--gasgo-blue);
        font-weight: 600;
    }

    .total-label {
        color: var(--gasgo-blue);
    }

    .total-amount {
        color: var(--gasgo-orange);
        font-size: 1.1rem;
    }

    /* Payment Section */
    .payment-section {
        padding: 16px;
        border-radius: 14px;
        font-size: 0.95rem;
        margin-bottom: 20px;
        font-weight: 600;
        border-left: 4px solid;
    }

    .payment-cash {
        background: linear-gradient(135deg, rgba(255, 107, 53, 0.12), rgba(255, 107, 53, 0.05));
        color: #e74c3c;
        border-left-color: #ff6b35;
    }

    .payment-gcash {
        background: linear-gradient(135deg, rgba(46, 204, 113, 0.12), rgba(46, 204, 113, 0.05));
        color: #27ae60;
        border-left-color: #27ae60;
    }

    /* Timeline/Progress */
    .step-timeline {
        position: relative;
        padding-left: 35px;
    }

    .step-timeline::before {
        content: '';
        position: absolute;
        left: 14px;
        top: 12px;
        bottom: -12px;
        width: 2px;
        background: linear-gradient(to bottom, var(--gasgo-orange), #e0e0e0);
    }

    .step-item {
        position: relative;
        margin-bottom: 28px;
    }

    .step-item .dot {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        position: absolute;
        left: -34px;
        top: -2px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        color: white;
        z-index: 2;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }

    .step-item .dot.done {
        background: var(--gasgo-orange);
    }

    .step-item .dot.current {
        background: var(--gasgo-blue);
        animation: pulse 1.5s infinite;
    }

    .step-item .dot.pending {
        background: #e8e8e8;
        color: #999;
    }

    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(26, 109, 176, 0.5);
        }
        50% {
            box-shadow: 0 0 0 10px rgba(26, 109, 176, 0);
        }
    }

    .step-label {
        font-weight: 700;
        font-size: 0.95rem;
        color: #333;
        margin-bottom: 4px;
    }

    .step-time {
        font-size: 0.8rem;
        color: #999;
        font-weight: 500;
    }

    /* Action Buttons */
    .action-btn {
        width: 100%;
        padding: 16px;
        border: none;
        border-radius: 14px;
        font-size: 0.95rem;
        font-weight: 700;
        color: white;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }

    .action-btn:active {
        transform: translateY(-1px);
    }

    .btn-primary-action {
        background: linear-gradient(135deg, var(--gasgo-orange), #ff6b35);
    }

    .btn-secondary-action {
        background: linear-gradient(135deg, #ff9800, #ffb74d);
    }

    .btn-success-action {
        background: linear-gradient(135deg, #27ae60, #2ecc71);
    }

    /* Action Buttons */
    .d-flex.flex-column.gap-2 {
        gap: 12px !important;
    }

    /* Improve card spacing */
    .rider-card {
        margin-bottom: 24px;
    }

    /* Modal Styling */
    .modal-content {
        border-radius: 16px;
        border: 1px solid var(--admin-border);
    }

    .modal-header {
        border-bottom: none;
        padding: 24px;
    }

    .modal-title {
        color: var(--gasgo-blue);
        font-weight: 700;
        font-size: 1rem;
    }

    .modal-body {
        padding: 0 24px 24px;
    }

    .form-label {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--gasgo-blue);
        margin-bottom: 8px;
    }

    .form-control {
        border-radius: 10px;
        border: 1px solid var(--admin-border);
        font-size: 0.9rem;
    }

    .form-control:focus {
        border-color: var(--gasgo-blue);
        box-shadow: 0 0 0 3px rgba(26, 109, 176, 0.1);
    }

    .form-text {
        font-size: 0.8rem;
        color: #888;
        margin-top: 4px;
    }

    .modal-footer {
        border-top: 1px solid var(--admin-border);
        padding: 16px 24px;
        gap: 8px;
    }

    .modal-btn-cancel {
        border-radius: 10px;
        color: #666;
        border: 1px solid #e0e0e0;
        background: white;
        padding: 10px 20px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .modal-btn-cancel:hover {
        background: #f5f5f5;
    }

    .modal-btn-submit {
        background: var(--gasgo-orange);
        color: white;
        border-radius: 10px;
        font-weight: 600;
        border: none;
        padding: 10px 20px;
        cursor: pointer;
        transition: background 0.2s;
    }

    .modal-btn-submit:hover {
        background: #e68a1a;
    }

    /* Alerts */
    .alert-custom {
        border-radius: 12px;
        border: none;
        padding: 16px;
        margin-top: 12px;
    }

    .alert-success {
        background: rgba(46, 204, 113, 0.1);
        color: #155724;
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        color: #721c24;
    }

    /* Toast Alert Animation */
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateX(-50%) translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(-50%) translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 576px) {
        .customer-info {
            flex-direction: column;
            text-align: center;
        }

        .action-btn {
            font-size: 0.9rem;
            padding: 12px;
        }

        .step-timeline {
            padding-left: 25px;
        }

        .step-item .dot {
            width: 20px;
            height: 20px;
            left: -25px;
        }

        .contact-btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('content')
<div class="delivery-page">

<!-- Order Info -->
<div class="rider-card mb-4">
    <div class="order-head mb-4">
        <div>
            <h6 class="mb-1" style="color:var(--gasgo-blue);font-weight:700;">Order #{{ $delivery->order->order_number }}</h6>
            <small class="text-muted">Assigned {{ $delivery->assigned_at?->diffForHumans() ?? 'recently' }}</small>
        </div>
        <span class="badge-status badge-{{ $delivery->status }}">{{ str_replace('_', ' ', ucfirst($delivery->status)) }}</span>
    </div>

    <!-- Customer Info -->
    <div class="customer-section">
        <div class="customer-info">
            <div class="customer-avatar">{{ strtoupper(substr($delivery->order->user->name, 0, 1)) }}</div>
            <div class="customer-details">
                <div class="customer-name">{{ $delivery->order->user->name }}</div>
                <div class="customer-phone">{{ $delivery->order->contact_number }}</div>
            </div>
            <a href="tel:{{ $delivery->order->contact_number }}" class="contact-btn">
                <i class="fas fa-phone"></i>
            </a>
        </div>
    </div>

    <!-- Delivery Address -->
    <div class="address-section">
        <div class="section-header">
            <i class="fas fa-map-marker-alt"></i>Delivery Address
        </div>
        <p class="address-text">{{ $delivery->order->delivery_address }}</p>
    </div>

    <!-- Customer Delivery Notes / Landmark -->
    <div class="address-section">
        <div class="section-header">
            <i class="fas fa-sticky-note"></i>Delivery Notes / Landmark
        </div>
        <p class="address-text mb-0">
            {{ $delivery->order->notes ?: 'No additional notes or landmark provided by customer.' }}
        </p>
    </div>

    <!-- Items -->
    <div class="mb-4">
        <h6 style="color:var(--gasgo-blue);font-weight:600;font-size:.9rem;"><i class="fas fa-box me-2" style="color:var(--gasgo-orange);"></i>Order Items</h6>
        <div style="background:var(--admin-bg);border-radius:10px;padding:12px;">
            @foreach($delivery->order->orderItems as $item)
                <div class="d-flex justify-content-between align-items-center py-2" style="border-bottom:1px solid var(--admin-border);font-size:.88rem;">
                    <span>{{ $item->product->name }} <strong>×{{ $item->quantity }}</strong></span>
                    <span class="fw-bold">₱{{ number_format($item->product->price * $item->quantity, 2) }}</span>
                </div>
            @endforeach
            @if($delivery->order->discount > 0)
                <div class="d-flex justify-content-between align-items-center py-2" style="font-size:.88rem;">
                    <span class="text-muted">Discount Applied</span>
                    <span class="fw-bold text-danger">-₱{{ number_format($delivery->order->discount, 2) }}</span>
                </div>
            @endif
            <div class="d-flex justify-content-between align-items-center py-2" style="font-size:.88rem;">
                <span class="text-muted">Delivery Fee</span>
                <span class="fw-bold">₱{{ number_format($delivery->order->delivery_fee, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center py-3" style="font-size:.95rem;border-top:2px solid var(--gasgo-blue);">
                <span class="fw-bold" style="color:var(--gasgo-blue);">Total Amount</span>
                <span class="fw-bold" style="color:var(--gasgo-orange);font-size:1.1rem;">₱{{ number_format($delivery->order->total_amount, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Payment -->
    @if($delivery->order->payment_method === 'cash')
        <div class="p-3" style="background: var(--gasgo-orange-light); border-radius:12px; font-size:.88rem;">
            <i class="fas fa-money-bill me-2" style="color: var(--gasgo-orange); font-weight:700;"></i>
            <strong>Cash on Delivery</strong>
            &middot; Collect <strong>₱{{ number_format($delivery->order->total_amount, 2) }}</strong>
        </div>
    @else
        <div class="p-3" style="background: #d4edda; border-radius:12px; font-size:.88rem;">
            <i class="fas fa-money-bill me-2" style="color: #155724; font-weight:700;"></i>
            <strong>Payment Received (GCash)</strong>
        </div>
    @endif
</div>

<!-- Delivery Progress -->
<div class="rider-card mb-4">
    <h6 style="color:var(--gasgo-blue);font-weight:600;margin-bottom:24px;"><i class="fas fa-route me-2" style="color:var(--gasgo-orange);"></i>Delivery Progress</h6>
    <div class="step-timeline">
        <div class="step-item">
            <div class="dot {{ in_array($delivery->status, ['out_for_delivery', 'delivered']) ? 'done' : 'current' }}">
                <i class="fas fa-{{ in_array($delivery->status, ['out_for_delivery', 'delivered']) ? 'check' : 'circle' }}"></i>
            </div>
            <div class="step-label">Order Assigned</div>
            <div class="step-time">{{ $delivery->assigned_at?->format('h:i A') ?? '—' }}</div>
        </div>
        <div class="step-item">
            <div class="dot {{ $delivery->status === 'delivered' ? 'done' : ($delivery->status === 'out_for_delivery' ? 'current' : 'pending') }}">
                <i class="fas fa-{{ in_array($delivery->status, ['out_for_delivery', 'delivered']) ? 'check' : 'circle' }}"></i>
            </div>
            <div class="step-label">On the Way</div>
            <div class="step-time">{{ in_array($delivery->status, ['out_for_delivery', 'delivered']) ? 'In transit' : '—' }}</div>
        </div>
        <div class="step-item">
            <div class="dot {{ $delivery->status === 'delivered' ? 'done' : 'pending' }}">
                <i class="fas fa-{{ $delivery->status === 'delivered' ? 'check' : 'circle' }}"></i>
            </div>
            <div class="step-label">Delivered</div>
            <div class="step-time">{{ $delivery->delivered_at?->format('h:i A') ?? '—' }}</div>
        </div>
    </div>
</div>

<!-- Action Buttons -->
@if($delivery->status !== 'delivered' && $delivery->status !== 'failed')
    <div class="d-flex flex-column gap-2">
        @if($delivery->status === 'assigned' || $delivery->status === 'picked_up')
            <button class="action-btn" style="background:linear-gradient(135deg,#27ae60,#2ecc71);" onclick="startDeliveryAndGoToMap()">
                <i class="fas fa-route me-2"></i>Start Delivery & Open Live Map
            </button>
        @elseif($delivery->status === 'out_for_delivery')
            <!-- Link to Live Route Map while on delivery -->
            <a href="{{ url('/rider/route/live-map') }}" class="action-btn mb-2" style="background:linear-gradient(135deg,#2196f3,#42a5f5);text-decoration:none;">
                <i class="fas fa-satellite-dish me-2"></i>Open Live Route Map
            </a>
            <button class="action-btn" style="background:linear-gradient(135deg,#27ae60,#2ecc71);" onclick="updateDeliveryStatus('delivered')">
                <i class="fas fa-check-circle me-2"></i>Mark as Delivered
            </button>
            <button class="action-btn" style="background:linear-gradient(135deg,#dc3545,#e74c3c);" onclick="updateDeliveryStatus('failed')">
                <i class="fas fa-times-circle me-2"></i>Failed Delivery
            </button>
        @endif
        
    </div>
@else
    <div class="alert alert-{{ $delivery->status === 'delivered' ? 'success' : 'danger' }} text-center mt-3" style="border-radius:12px;">
        <i class="fas fa-{{ $delivery->status === 'delivered' ? 'check-circle' : 'times-circle' }} me-2"></i>
        <strong>{{ $delivery->status === 'delivered' ? 'Delivery Complete' : 'Delivery Failed' }}</strong>
    </div>
@endif

</div>



@endsection

@section('scripts')
<script>
    // Helper function to show alerts
    function showAlert(type, message) {
        // Remove any existing alerts
        const existingAlerts = document.querySelectorAll('.custom-alert-toast');
        existingAlerts.forEach(alert => alert.remove());

        // Create alert element
        const alertDiv = document.createElement('div');
        alertDiv.className = 'custom-alert-toast';
        alertDiv.innerHTML = `
            <div style="
                position: fixed;
                top: 80px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 9999;
                padding: 16px 24px;
                border-radius: 12px;
                box-shadow: 0 4px 20px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                gap: 10px;
                font-weight: 600;
                font-size: 0.9rem;
                animation: slideDown 0.3s ease;
                background: ${type === 'success' ? '#d4edda' : '#f8d7da'};
                color: ${type === 'success' ? '#155724' : '#721c24'};
                border: 1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'};
            ">
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            </div>
        `;

        document.body.appendChild(alertDiv);

        // Auto remove after 3 seconds
        setTimeout(() => alertDiv.remove(), 3000);
    }

    // Start delivery and redirect to Live Route Map
    function startDeliveryAndGoToMap() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const statusUrl = "{{ route('rider.delivery.status', $delivery->id) }}";

        // Disable button to prevent double-click
        event.target.disabled = true;
        event.target.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Starting...';

        fetch(statusUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ status: 'out_for_delivery' })
        })
        .then(async response => {
            if (!response.ok) {
                throw new Error('Failed to update status');
            }
            return response.json();
        })
        .then(() => {
            showAlert('success', 'Delivery started! Opening Live Route Map...');

            // Redirect to Live Route Map
            setTimeout(() => {
                window.location.href = "{{ url('/rider/route/live-map') }}";
            }, 1000);
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Failed to start delivery. Please try again.');
            event.target.disabled = false;
            event.target.innerHTML = '<i class="fas fa-route me-2"></i>Start Delivery & Open Live Map';
        });
    }

    function updateDeliveryStatus(status) {
        if (confirm('Update delivery status to: ' + status.replace('_', ' ').toUpperCase() + '?')) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            const statusUrl = "{{ route('rider.delivery.status', $delivery->id) }}";

            fetch(statusUrl, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    status: status
                })
            })
            .then(async response => {
                const contentType = response.headers.get('content-type') || '';
                const payload = contentType.includes('application/json')
                    ? await response.json()
                    : { message: 'Unexpected server response. Please refresh and try again.' };

                if (!response.ok) {
                    throw new Error(payload.message || 'Failed to update');
                }

                return payload;
            })
            .then(() => {
                showAlert('success', 'Status updated successfully!');
                setTimeout(() => location.reload(), 1000);
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', error.message || 'Failed to update status. Please try again.');
            });
        }
    }

    // Handle proof form submission
    document.getElementById('proofForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const proofUrl = "{{ route('rider.delivery.proof', $delivery->id) }}";

        fetch(proofUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            showAlert('success', data.message || 'Proof uploaded successfully!');
            setTimeout(() => location.reload(), 1000);
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Failed to upload proof. Please try again.');
        });
    });
</script>
@endsection
