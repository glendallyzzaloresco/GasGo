@extends('layouts.admin')

@section('title', 'GasGo Admin - Deliveries')
@section('nav-deliveries', 'active')
@section('page-title', 'Delivery Tracking')

@section('admin-styles')
<style>
    .map-placeholder {
        width:100%; height:350px; border-radius:16px; overflow:hidden;
        background:linear-gradient(135deg,var(--gasgo-blue-light),#d5e8f7);
        display:flex; align-items:center; justify-content:center; position:relative;
        box-shadow:0 4px 15px rgba(0,0,0,.06);
    }
    .map-placeholder .map-text {
        text-align:center; color:var(--gasgo-blue); z-index:2;
    }
    .map-placeholder .map-text i { font-size:3rem; margin-bottom:10px; display:block; }
    .delivery-item {
        background:#fff; border-radius:14px; padding:18px;
        box-shadow:0 2px 10px rgba(0,0,0,.05); transition:transform .2s; cursor:pointer;
        border-left:4px solid transparent;
    }
    .delivery-item:hover { transform:translateX(4px); }
    .delivery-item.active-delivery { border-left-color:var(--gasgo-orange); background:var(--gasgo-orange-light); }
    .delivery-item.completed-delivery { border-left-color:#27ae60; }
    .timeline-mini { display:flex; gap:4px; margin-top:8px; }
    .timeline-mini .step {
        flex:1; height:4px; border-radius:2px; background:#e0e0e0;
    }
    .timeline-mini .step.done { background:var(--gasgo-orange); }
    .timeline-mini .step.current { background:var(--gasgo-blue); animation:pulse 1.5s infinite; }
    @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:.5;} }
</style>
@endsection

@php
    $statusOrder = ['assigned', 'picked_up', 'out_for_delivery', 'delivered'];
    $statusLabels = [
        'assigned' => 'Assigned',
        'picked_up' => 'Picked Up',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'failed' => 'Failed'
    ];
    $statusBadgeClasses = [
        'assigned' => 'badge-assigned',
        'picked_up' => 'badge-assigned',
        'out_for_delivery' => 'badge-out_for_delivery',
        'delivered' => 'badge-delivered',
        'failed' => 'badge-cancelled'
    ];
    
    // Split deliveries into active and completed
    $activeDeliveries = $deliveries->filter(fn($d) => !in_array($d->status, ['delivered', 'failed']));
    $completedDeliveries = $deliveries->filter(fn($d) => in_array($d->status, ['delivered', 'failed']));
@endphp

@section('content')
<!-- Map Section -->
<div class="map-placeholder mb-4">
    <div class="map-text">
        <i class="fas fa-map-marked-alt"></i>
        <h5 class="fw-bold">Live Delivery Map</h5>
        <p style="font-size:.88rem;">Integrate with Google Maps or Leaflet.js for real-time tracking</p>
    </div>
</div>

<div class="row g-4">
    <!-- Active Deliveries -->
    <div class="col-lg-6">
        <h6 class="fw-bold mb-3" style="color:var(--gasgo-blue);"><i class="fas fa-shipping-fast me-2" style="color:var(--gasgo-orange);"></i>Active Deliveries <span class="badge bg-warning text-dark ms-1">{{ $activeDeliveries->count() }}</span></h6>
        <div class="d-flex flex-column gap-3">
            @forelse($activeDeliveries as $delivery)
                @php
                    $order = $delivery->order;
                    $statusIndex = array_search($delivery->status, $statusOrder);
                    $isActive = !in_array($delivery->status, ['delivered', 'failed']);
                @endphp
                <div class="delivery-item {{ $isActive ? 'active-delivery' : '' }}">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold" style="color:var(--gasgo-blue);">#{{ $order->order_number ?? 'N/A' }}</div>
                            <div style="font-size:.85rem;">{{ $order->user->name ?? 'Unknown' }} &bullet; 
                                @if($order->orderItems)
                                    @foreach($order->orderItems as $item)
                                        {{ $item->product->name ?? 'Product' }} ×{{ $item->quantity }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @endif
                            </div>
                            <div style="font-size:.8rem;color:#888;"><i class="fas fa-map-marker-alt me-1"></i>{{ $order->delivery_address ?? 'Address not provided' }}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge-status {{ $statusBadgeClasses[$delivery->status] ?? 'badge-assigned' }}">{{ $statusLabels[$delivery->status] ?? $delivery->status }}</span>
                            <div style="font-size:.78rem;color:#888;margin-top:4px;"><i class="fas fa-motorcycle me-1"></i>{{ $delivery->rider->name ?? 'Unassigned' }}</div>
                        </div>
                    </div>
                    <div class="timeline-mini">
                        @foreach($statusOrder as $idx => $status)
                            <div class="step {{ $idx < ($statusIndex + 1) ? 'done' : '' }} {{ $idx == $statusIndex ? 'current' : '' }}"></div>
                        @endforeach
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2" style="color:#ddd;"></i>
                    <p>No active deliveries at the moment.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Completed -->
    <div class="col-lg-6">
        <h6 class="fw-bold mb-3" style="color:var(--gasgo-blue);"><i class="fas fa-check-circle me-2 text-success"></i>Recently Completed</h6>
        <div class="d-flex flex-column gap-3">
            @forelse($completedDeliveries->take(5) as $delivery)
                @php
                    $order = $delivery->order;
                    $deliveryTime = $delivery->delivered_at ? $delivery->delivered_at->diffForHumans() : 'N/A';
                @endphp
                <div class="delivery-item completed-delivery">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-bold" style="color:var(--gasgo-blue);">#{{ $order->order_number ?? 'N/A' }}</div>
                            <div style="font-size:.85rem;">{{ $order->user->name ?? 'Unknown' }} &bullet; 
                                @if($order->orderItems)
                                    @foreach($order->orderItems as $item)
                                        {{ $item->product->name ?? 'Product' }} ×{{ $item->quantity }}{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                @endif
                            </div>
                            <div style="font-size:.8rem;color:#888;"><i class="fas fa-motorcycle me-1"></i>{{ $delivery->rider->name ?? 'Unknown' }} &bullet; {{ $deliveryTime }}</div>
                        </div>
                        <div class="text-end">
                            <span class="badge-status badge-{{ $delivery->status }}">{{ $statusLabels[$delivery->status] ?? ucfirst($delivery->status) }}</span>
                            <div style="font-size:.78rem;color:#888;margin-top:4px;">{{ $delivery->delivered_at ? $delivery->delivered_at->format('M d g:i A') : 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="timeline-mini">
                        <div class="step done"></div>
                        <div class="step done"></div>
                        <div class="step done"></div>
                        <div class="step done"></div>
                        <div class="step done"></div>
                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-inbox fa-2x mb-2" style="color:#ddd;"></i>
                    <p>No completed deliveries yet.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
