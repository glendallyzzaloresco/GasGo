@extends('layouts.rider')

@section('title', 'History')
@section('page-title', 'Delivery History')
@section('nav-history', 'active')

@section('content')
<!-- Summary Statistics -->
<div class="row g-3 mb-4">
    <div class="col-md-12">
        <div class="rider-card">
            <div class="d-flex align-items-center gap-3">
                <div class="card-icon green"><i class="fas fa-check-double"></i></div>
                <div>
                    <h3>{{ \App\Models\Delivery::where('rider_id', auth()->id())->where('status', 'delivered')->count() }}</h3>
                    <p>Completed</p>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- Completed Deliveries -->
<h5 class="fw-bold mb-3" style="color:var(--gasgo-blue);"><i class="fas fa-check-circle me-2" style="color:var(--gasgo-orange);"></i>Completed Deliveries</h5>

@forelse(\App\Models\Delivery::with('order.user')->where('rider_id', auth()->id())->where('status', 'delivered')->orderBy('delivered_at', 'desc')->limit(20)->get() as $delivery)
    <div class="rider-card mb-3">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <h6 class="fw-bold mb-1" style="color:var(--gasgo-blue);">Order #{{ $delivery->order->order_number }}</h6>
                <small class="text-muted">
                    {{ $delivery->delivered_at?->format('M d, h:i A') ?? 'N/A' }}
                    @if($delivery->delivered_at && $delivery->picked_up_at)
                        • <strong>{{ $delivery->delivered_at->diffInMinutes($delivery->picked_up_at) }}m</strong> delivery time
                    @endif
                </small>
            </div>
            <span class="badge-status badge-delivered">Delivered</span>
        </div>

        <div style="font-size:.88rem;" class="mb-2">
            <strong>{{ $delivery->order->user->name }}</strong>
            &middot;
            <span style="color:var(--gasgo-orange);font-weight:600;">₱{{ number_format($delivery->order->total_amount, 2) }}</span>
        </div>
        <div style="font-size:.82rem;color:#555;margin-bottom:8px;">
            Delivery Fee: ₱{{ number_format($delivery->order->delivery_fee, 2) }}
        </div>
        <div style="font-size:.85rem;color:#666;" class="mb-3">
            <i class="fas fa-map-marker-alt me-1" style="color:#888;"></i>{{ Str::limit($delivery->order->delivery_address, 50) }}
        </div>

        
    </div>
@empty
    <div class="text-center text-muted py-5">
        <i class="fas fa-box-open" style="font-size:3rem;color:#ccc;margin-bottom:15px;display:block;"></i>
        <span style="font-size:.9rem;">No completed deliveries yet. Start delivering to see your history!</span>
    </div>
@endforelse

@endsection