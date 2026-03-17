@extends('layouts.rider')

@section('title', 'GasGo Rider - Dashboard')
@section('page-title', 'Dashboard')
@section('nav-dashboard', 'active')

@section('content')
<!-- Status Toggle -->
<div class="rider-card text-center mb-4">
    <p class="text-muted mb-3" style="font-size:.85rem;">Your availability status</p>
    <div class="d-flex gap-2 justify-content-center">   
        <button class="btn btn-sm @if(auth()->user()->rider?->availability === 'available') btn-success @else btn-outline-secondary @endif" onclick="setStatus(this,'available')" style="border-radius:20px;"><i class="fas fa-check-circle me-1"></i>Available</button>
        <button class="btn btn-sm @if(auth()->user()->rider?->availability === 'busy') btn-warning @else btn-outline-secondary @endif" onclick="setStatus(this,'busy')" style="border-radius:20px;"><i class="fas fa-clock me-1"></i>Busy</button>
        <button class="btn btn-sm @if(auth()->user()->rider?->availability === 'offline') btn-secondary @else btn-outline-secondary @endif" onclick="setStatus(this,'offline')" style="border-radius:20px;"><i class="fas fa-moon me-1"></i>Offline</button>
    </div>
</div>

<!-- Today's Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-4 col-sm-6">
        <a href="{{ url('/rider/route/live-map') }}" class="text-decoration-none">
            <div class="rider-card" style="cursor:pointer;">
                <div class="d-flex align-items-center gap-3">
                    <div class="card-icon blue"><i class="fas fa-shipping-fast"></i></div>
                    <div>
                        <h3>{{ count($activeDeliveries) }}</h3>
                        <p>Active Deliveries</p>
                    </div>
                </div>
                @if(count($activeDeliveries) > 0)
                <div class="mt-2 pt-2" style="border-top:1px solid var(--admin-border);">
                    <small style="color:var(--gasgo-blue);font-weight:600;"><i class="fas fa-external-link-alt me-1"></i>Open Live Route Map</small>
                </div>
                @endif
            </div>
        </a>
    </div>
    <div class="col-md-4 col-sm-6">
        <div class="rider-card">
            <div class="d-flex align-items-center gap-3">
                <div class="card-icon green"><i class="fas fa-check-double"></i></div>
                <div>
                    <h3>{{ $completedCount }}</h3>
                    <p>Completed Today</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Action: Go to Live Route Map (if has active deliveries) -->
@if(count($activeDeliveries) > 0)
<div class="rider-card mb-4" style="background:linear-gradient(135deg, #1a2744 0%, #243656 100%);border:none;">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <h6 class="mb-1" style="color:white;font-weight:700;"><i class="fas fa-truck me-2" style="color:var(--gasgo-orange);"></i>You have {{ count($activeDeliveries) }} active {{ Str::plural('delivery', count($activeDeliveries)) }}</h6>
            <small style="color:rgba(255,255,255,0.7);">Track and manage your deliveries in real-time</small>
        </div>
        <a href="{{ url('/rider/route/live-map') }}" class="btn" style="background:var(--gasgo-orange);color:white;font-weight:700;border-radius:10px;padding:12px 24px;">
            <i class="fas fa-satellite-dish me-2"></i>Open Live Route Map
        </a>
    </div>
</div>
@endif

<!-- Available Orders Section -->
@if($availableOrders && count($availableOrders) > 0)
<div class="mb-4">
    <h5 class="fw-bold mb-3" style="color:var(--gasgo-blue);">
        <i class="fas fa-shopping-bag me-2" style="color:var(--gasgo-orange);"></i>Available Orders
        <span class="badge" style="background:var(--gasgo-orange);color:white;font-size:.75rem;padding:4px 10px;border-radius:12px;">{{ count($availableOrders) }} new</span>
    </h5>
    <p class="text-muted mb-3" style="font-size:.85rem;">
        <i class="fas fa-info-circle me-1"></i>Accept orders to add them to your active deliveries
    </p>

    @foreach($availableOrders as $order)
        <div class="rider-card mb-3 available-order-card" id="order-card-{{ $order->id }}">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Order #{{ $order->order_number }}</h6>
                    <small class="text-muted">Placed {{ $order->created_at->diffForHumans() }}</small>
                </div>
                <span class="badge-status badge-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
            </div>

            <div class="mb-3">
                <div style="font-size:.88rem;" class="mb-2">
                    <i class="fas fa-user me-2" style="color:var(--gasgo-blue);"></i><strong>{{ $order->user->name }}</strong>
                </div>
                <div style="font-size:.85rem;" class="mb-2">
                    <i class="fas fa-map-marker-alt me-2" style="color:var(--gasgo-orange);"></i>
                    <span>{{ Str::limit($order->delivery_address, 60) }}</span>
                </div>
                <div style="font-size:.85rem;" class="mb-2">
                    <i class="fas fa-box me-2" style="color:#888;"></i>
                    @forelse($order->orderItems as $item)
                        {{ $item->product ? $item->product->name : $item->product_name }} ×{{ $item->quantity }}@if(!$loop->last), @endif
                    @empty
                        No items
                    @endforelse
                    &middot; <strong style="color:var(--gasgo-orange);">₱{{ number_format($order->total_amount, 2) }}</strong>
                </div>
                <div style="font-size:.85rem;">
                    @if($order->payment_method === 'cash')
                        <i class="fas fa-money-bill me-2" style="color: #27ae60;"></i>
                    @else
                        <i class="fas fa-credit-card me-2" style="color: #2196f3;"></i>
                    @endif
                    {{ $order->payment_method === 'cash' ? 'Cash on Delivery' : 'Paid Online' }}
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button onclick="acceptOrder({{ $order->id }})" class="btn flex-grow-1 btn-action accept-order-btn"
                        style="background:#27ae60;color:#fff;">
                    <i class="fas fa-check-circle me-1"></i>Accept Order
                </button>
                <a href="tel:{{ $order->contact_number }}" class="btn btn-action"
                   style="background:var(--gasgo-blue-light);color:var(--gasgo-blue);">
                    <i class="fas fa-phone"></i>
                </a>
            </div>
        </div>
    @endforeach
</div>
@endif

@endsection

@section('scripts')
<script>
    function acceptOrder(orderId) {
        const btn = event.target.closest('button');
        const card = document.getElementById(`order-card-${orderId}`);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        if (!csrfToken) {
            alert('Security token not found. Please refresh the page.');
            return;
        }

        // Disable button and show loading
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Accepting...';

        fetch(`/rider/orders/${orderId}/accept`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                card.style.background = '#d4edda';
                card.innerHTML = `
                    <div class="text-center py-3">
                        <i class="fas fa-check-circle" style="font-size:2rem;color:#27ae60;"></i>
                        <p class="mt-2 mb-0" style="color:#155724;font-weight:600;">Order Accepted!</p>
                        <small class="text-muted">Redirecting to delivery details...</small>
                    </div>
                `;

                // Redirect to delivery page
                setTimeout(() => {
                    window.location.href = `/rider/delivery/${data.delivery_id}`;
                }, 1500);
            } else {
                // Show error
                alert(data.message || 'Failed to accept order. Please try again.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Accept Order';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Accept Order';
        });
    }

    function setStatus(btn, status) {
        // Update button states - target all status buttons
        document.querySelectorAll('.rider-card.text-center button').forEach(b => {
            b.className = 'btn btn-sm btn-outline-secondary';
            b.style.borderRadius = '20px';
        });
        
        // Highlight selected button
        btn.className = 'btn btn-sm ' + (status === 'available' ? 'btn-success' : status === 'busy' ? 'btn-warning' : 'btn-secondary');
        btn.style.borderRadius = '20px';
        
        // Save availability status
        const profileUrl = "{{ route('rider.profile.update') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            console.error('CSRF token not found');
            return;
        }
        
        fetch(profileUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                _method: 'PUT',
                availability: status
            })
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            console.log('Status updated:', data);
        })
        .catch(error => {
            console.error('Error updating status:', error);
            alert('Failed to update status. Please try again.');
        });
    }
</script>
@endsection
