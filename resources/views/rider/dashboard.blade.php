@extends('layouts.rider')

@section('title', 'GasGo Rider - Dashboard')
@section('page-title', 'Dashboard')
@section('nav-dashboard', 'active')

@section('content')
@if(count($activeDeliveries) > 0)
<div class="alert alert-info alert-dismissible fade show mb-4" role="alert" style="border-radius: 14px; border: none; background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%); padding: 16px 20px; box-shadow: 0 4px 12px rgba(33, 150, 243, 0.25);">
    <div style="display: flex; gap: 12px; align-items: start;">
        <i class="fas fa-truck" style="color: #1565c0; font-size: 1.3rem; margin-top: 2px;"></i>
        <div>
            <h6 class="mb-1" style="color: #1565c0; font-weight: 700;"><i class="fas fa-map-marker-alt me-2"></i>You have {{ count($activeDeliveries) }} active delivery{{ count($activeDeliveries) > 1 ? 'ies' : '' }} right now</h6>
            <small style="color: #666;">Track and update delivery status on the live route map.</small>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
@endif

<!-- Status Toggle -->
<div class="rider-card text-center mb-4">
    <p class="text-muted mb-3" style="font-size:.85rem;">Your availability status</p>
    <div class="d-flex gap-2 justify-content-center flex-wrap">   
        <button class="btn btn-sm @if(auth()->user()->rider?->availability === 'available') btn-success @else btn-outline-secondary @endif" onclick="setStatus(this,'available')" style="border-radius:20px;"><i class="fas fa-check-circle me-1"></i>Available</button>
        <button class="btn btn-sm @if(auth()->user()->rider?->availability === 'busy') btn-warning @else btn-outline-secondary @endif" onclick="setStatus(this,'busy')" style="border-radius:20px;"><i class="fas fa-clock me-1"></i>Busy</button>
        <button class="btn btn-sm @if(auth()->user()->rider?->availability === 'returning') btn-info @else btn-outline-secondary @endif" onclick="setStatus(this,'returning')" style="border-radius:20px;"><i class="fas fa-store me-1"></i>Returning to Store</button>
        <button class="btn btn-sm @if(auth()->user()->rider?->availability === 'offline') btn-secondary @else btn-outline-secondary @endif" onclick="setStatus(this,'offline')" style="border-radius:20px;"><i class="fas fa-moon me-1"></i>Offline</button>
    </div>
</div>

<!-- Today's Stats -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-sm-5">
        <a href="{{ url('/rider/route/live-map') }}" class="text-decoration-none">
            <div class="rider-card" style="cursor:pointer;">
                <div class="d-flex align-items-center gap-3">
                    <div class="card-icon blue"><i class="fas fa-shipping-fast"></i></div>
                    <div>
                        <h3>{{ count($activeDeliveries) }}</h3>
                        <p>Active Deliveries</p>
                    </div>
                </div>
                
            </div>
        </a>
    </div>
    <div class="col-md-6 col-sm-5">
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

@endsection

@section('scripts')
<script>
    function setStatus(btn, status) {
        // Update button states - target all status buttons
        document.querySelectorAll('.rider-card.text-center button').forEach(b => {
            b.className = 'btn btn-sm btn-outline-secondary';
            b.style.borderRadius = '20px';
        });
        
        // Highlight selected button
        const btnClass = status === 'available' ? 'btn-success' : 
                         status === 'busy' ? 'btn-warning' : 
                         status === 'returning' ? 'btn-info' : 'btn-secondary';
        btn.className = 'btn btn-sm ' + btnClass;
        btn.style.borderRadius = '20px';
        
        // Save availability status
        const profileUrl = "{{ route('rider.profile.update') }}";
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) {
            console.error('CSRF token not found');
            alert('Security token not found. Please refresh the page.');
            return;
        }
        
        fetch(profileUrl, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                availability: status
            })
        })
        .then(response => response.json().then(data => ({ response, data })))
        .then(({ response, data }) => {
            if (!response.ok) {
                const errorMsg = data.message || data.error || 'Failed to update status';
                throw new Error(errorMsg);
            }
            console.log('Status updated:', data);
            // Show success message
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-success alert-dismissible fade show';
            alertDiv.innerHTML = '<i class="fas fa-check-circle me-2"></i>Status updated successfully.<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            document.body.insertAdjacentElement('afterbegin', alertDiv);
            setTimeout(() => alertDiv.remove(), 3000);
        })
        .catch(error => {
            console.error('Error updating status:', error);
            alert('Failed to update status: ' + error.message);
            // Reset button to previous state on error
            location.reload();
        });
    }
</script>
@endsection
