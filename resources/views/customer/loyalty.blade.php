@extends('layouts.customer')

@section('title', 'GasGo - Loyalty Rewards')
@section('nav-loyalty', 'active')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, var(--gasgo-orange) 0%, #ff6b35 100%);
        color: white; padding: 50px 0 60px; margin-bottom: -30px; position: relative;
    }
    .page-header::after {
        content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 60px;
        background: #f8f9fa; clip-path: ellipse(55% 100% at 50% 100%);
    }

    /* Progress */
    .progress-card {
        background: white; border-radius: 20px; padding: 28px;
        box-shadow: 0 8px 30px rgba(0,0,0,.08);
    }
    .progress-bar-gasgo {
        height: 12px; border-radius: 6px; background: #eee; overflow: hidden;
    }
    .progress-bar-gasgo .fill {
        height: 100%; border-radius: 6px;
        background: linear-gradient(90deg, var(--gasgo-orange), #ff6b35);
        transition: width .6s;
        width: var(--fill-width, 0%);
    }

    /* Stamps */
    .stamp-grid { display: flex; flex-wrap: wrap; gap: 10px; }
    .stamp {
        width: 52px; height: 52px; border-radius: 50%;
        border: 2px dashed #ddd; display: flex; align-items: center; justify-content: center;
        font-size: 1rem; color: #ccc; transition: all .3s;
    }
    .stamp.filled {
        background: linear-gradient(135deg, var(--gasgo-orange), #ff6b35);
        border: none; color: white;
    }

</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold"><i class="fas fa-gift me-2"></i>Loyalty & Rewards</h1>
        <p class="mb-0" style="opacity:.9;">Complete orders and unlock your FREE LPG Tank reward</p>
    </div>
</section>

<section class="container section-padding" style="position:relative;z-index:2;">
    @php
        $isGuest = !Auth::check();
        $completedOrders = Auth::check() ? \App\Models\Order::where('user_id', Auth::id())->where('status', 'delivered')->count() : 0;
        $stampCount = $completedOrders % 10;
        $targetOrders = 10;
        $remainingOrders = $targetOrders - $stampCount;
        $orderProgressPercent = min(100, ($stampCount / $targetOrders) * 100);
    @endphp

    @if ($isGuest)
        <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4" role="alert" data-aos="fade-up">
            <div>
                <strong>Guest Mode:</strong> Log in to track completed orders and unlock your free LPG tank reward.
            </div>
            <a href="{{ route('customer.login') }}" class="btn btn-gasgo btn-sm">Log In / Register</a>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7" data-aos="fade-up">
            <div class="progress-card h-100 d-flex flex-column justify-content-center">
                <h5 class="fw-bold mb-3" style="color:var(--gasgo-blue);"><i class="fas fa-chart-line me-2" style="color:var(--gasgo-orange);"></i>Progress to Free LPG Tank</h5>
                <p class="text-muted mb-2">{{ $stampCount }} / {{ $targetOrders }} completed orders</p>
                <div class="progress-bar-gasgo mb-3" style="--fill-width: {{ $orderProgressPercent }}%;">
                    <div class="fill"></div>
                </div>
                <p class="text-muted mb-4" style="font-size:.85rem;">Just <strong style="color:var(--gasgo-orange);">{{ $remainingOrders }} more order{{ $remainingOrders === 1 ? '' : 's' }}</strong> to go!</p>

                <h6 class="fw-bold mb-2" style="color:var(--gasgo-blue);">Order Stamps</h6>
                <div class="stamp-grid">
                    @for ($i = 1; $i <= 10; $i++)
                        <div class="stamp {{ $i <= $stampCount ? 'filled' : '' }}">
                            @if ($i <= $stampCount)
                                <i class="fas fa-fire"></i>
                            @else
                                {{ $i }}
                            @endif
                        </div>
                    @endfor
                </div>
                <p class="text-muted mt-2" style="font-size:.8rem;">Complete 10 orders to get a <strong>FREE LPG Tank!</strong></p>
            </div>
        </div>
    </div>

</section>
@endsection
