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

    /* Loyalty Card */
    .loyalty-card-display {
        background: linear-gradient(135deg, #1a1a2e 0%, #16213e 80%, var(--gasgo-orange) 100%);
        border-radius: 24px; padding: 36px; color: white; position: relative; overflow: hidden;
    }
    .loyalty-card-display::before {
        content: ''; position: absolute; top: -60px; right: -60px;
        width: 200px; height: 200px; border-radius: 50%;
        background: rgba(247,148,29,.15);
    }
    .loyalty-card-display .card-number { letter-spacing: 3px; font-size: 1.1rem; opacity: .8; }
    .loyalty-card-display .points-display { font-size: 2.2rem; font-weight: 800; }
    .loyalty-card-display .points-label { font-size: .85rem; opacity: .7; }

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

    /* Rewards */
    .reward-card {
        background: white; border-radius: 16px; padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,.06); height: 100%;
        border-top: 4px solid transparent; transition: all .3s;
    }
    .reward-card:hover { border-top-color: var(--gasgo-orange); transform: translateY(-4px); }
    .reward-icon {
        width: 60px; height: 60px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem; color: white; margin-bottom: 14px;
    }
    .reward-icon.discount { background: linear-gradient(135deg, var(--gasgo-orange), #ff6b35); }
    .reward-icon.free-item { background: linear-gradient(135deg, #27ae60, #2ecc71); }
    .reward-icon.free-delivery { background: linear-gradient(135deg, var(--gasgo-blue), #2196f3); }
    .points-needed { font-size: .82rem; color: var(--gasgo-orange); font-weight: 600; }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold"><i class="fas fa-gift me-2"></i>Loyalty & Rewards</h1>
        <p class="mb-0" style="opacity:.9;">Earn points with every order and unlock amazing rewards</p>
    </div>
</section>

<section class="container section-padding" style="position:relative;z-index:2;">
    @php
        $isGuest = !Auth::check();
        $completedOrders = Auth::check() ? \App\Models\Order::where('user_id', Auth::id())->where('status', 'delivered')->count() : 0;
        $stampCount = $completedOrders % 10;
        $currentBalance = $balance ?? 0;
    @endphp

    @if ($isGuest)
        <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4" role="alert" data-aos="fade-up">
            <div>
                <strong>Guest Mode:</strong> You can browse rewards now. Log in to track your points and redeem perks.
            </div>
            <a href="{{ route('customer.login') }}" class="btn btn-gasgo btn-sm">Log In / Register</a>
        </div>
    @endif

    <div class="row g-4">
        <!-- Loyalty Card -->
        <div class="col-lg-6" data-aos="fade-right">
            <div class="loyalty-card-display">
                <div class="d-flex justify-content-between align-items-start mb-4">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="fas fa-crown me-2" style="color:var(--gasgo-orange);"></i>Digital Loyalty Card</h5>
                        <div class="card-number">**** **** **** {{ Auth::check() ? str_pad(Auth::id(), 4, '0', STR_PAD_LEFT) : '0000' }}</div>
                    </div>
                    <img src="{{ asset('images/gasgo_logo-removebg-preview.png') }}" alt="GasGo" style="height:40px;opacity:.8;">
                </div>
                <div class="points-display">{{ $balance ?? 0 }} <span class="points-label">Points</span></div>
                <div class="mt-2" style="opacity:.7;font-size:.85rem;">Total Orders Completed: <strong>{{ $completedOrders }}</strong></div>
            </div>
        </div>

        <!-- Progress -->
        <div class="col-lg-6" data-aos="fade-left">
            <div class="progress-card h-100 d-flex flex-column justify-content-center">
                @php
                    $nextRewardTarget = 200;
                    $remaining = max(0, $nextRewardTarget - $currentBalance);
                    $progressPercent = $nextRewardTarget > 0 ? min(100, ($currentBalance / $nextRewardTarget) * 100) : 0;
                @endphp
                <h5 class="fw-bold mb-3" style="color:var(--gasgo-blue);"><i class="fas fa-chart-line me-2" style="color:var(--gasgo-orange);"></i>Progress to Next Reward</h5>
                <p class="text-muted mb-2">{{ $currentBalance }} / {{ $nextRewardTarget }} points to unlock <strong>Free Delivery</strong></p>
                <div class="progress-bar-gasgo mb-3" style="--fill-width: {{ $progressPercent }}%;">
                    <div class="fill"></div>
                </div>
                <p class="text-muted mb-4" style="font-size:.85rem;">Just <strong style="color:var(--gasgo-orange);">{{ $remaining }} more points</strong> to go!</p>

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

    <!-- Available Rewards -->
    <div class="mt-5">
        <div class="text-center mb-4" data-aos="fade-up">
            <h2 class="section-title" style="font-size:2rem;">Available Rewards</h2>
            <p class="section-subtitle">Redeem your points for exciting rewards</p>
        </div>
        <div class="row g-4">
            @php
                $rewards = [
                    ['name' => 'Free Delivery', 'desc' => 'Get free delivery on your next order', 'cost' => 200, 'icon' => 'fa-truck', 'class' => 'free-delivery'],
                    ['name' => '₱100 Discount', 'desc' => '₱100 off your next LPG purchase', 'cost' => 500, 'icon' => 'fa-percent', 'class' => 'discount'],
                    ['name' => 'Free LPG 2kg', 'desc' => 'Get a free 2kg LPG tank', 'cost' => 1000, 'icon' => 'fa-fire', 'class' => 'free-item'],
                ];
            @endphp
            @foreach ($rewards as $i => $reward)
            <div class="col-lg-4 col-md-6" data-aos="fade-up" data-aos-delay="{{ ($i + 1) * 100 }}">
                <div class="reward-card text-center">
                    <div class="reward-icon {{ $reward['class'] }} mx-auto"><i class="fas {{ $reward['icon'] }}"></i></div>
                    <h5 class="fw-bold">{{ $reward['name'] }}</h5>
                    <p class="text-muted small">{{ $reward['desc'] }}</p>
                    <p class="points-needed"><i class="fas fa-star me-1"></i>{{ $reward['cost'] }} Points</p>
                    @if ($isGuest)
                        <a href="{{ route('customer.login') }}" class="btn btn-gasgo-outline btn-sm w-100">Log in to Redeem</a>
                    @elseif ($currentBalance >= $reward['cost'])
                        <button class="btn btn-gasgo btn-sm w-100">Redeem Now</button>
                    @else
                        <button class="btn btn-gasgo-outline btn-sm w-100" disabled>{{ $reward['cost'] - $currentBalance }} Points Needed</button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>

</section>
@endsection
