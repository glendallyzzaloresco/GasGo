@extends('layouts.customer')

@section('title', 'Loyalty & Promos')
@section('nav-loyalty', 'active')

@section('styles')
    <style>
        /* ===== PAGE HEADER ===== */
        .page-header {
            background: linear-gradient(135deg, var(--gasgo-orange) 0%, #ff6b35 100%);
            color: white;
            padding: 50px 0 60px;
            margin-bottom: 0;
            position: relative;
        }

        .page-header::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 60px;
            background: #ffffff;
            clip-path: ellipse(55% 100% at 50% 100%);
        }

        /* ===== GUEST CTA BANNER ===== */
        .guest-cta-banner {
            background: linear-gradient(135deg, var(--gasgo-orange) 0%, #ff6b35 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 30px;
            margin-bottom: 50px;
            box-shadow: 0 10px 30px rgba(247, 148, 29, 0.2);
        }

        .guest-cta-banner h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.3;
        }

        .guest-cta-banner p {
            margin: 8px 0 0 0;
            opacity: 0.95;
            font-size: 0.95rem;
        }

        .guest-cta-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .guest-cta-buttons .btn {
            padding: 10px 24px;
            font-weight: 600;
            border-radius: 25px;
            font-size: 0.9rem;
        }

        .guest-cta-buttons .btn-primary {
            background: white;
            color: var(--gasgo-orange);
            border: none;
        }

        .guest-cta-buttons .btn-primary:hover {
            background: #f5f5f5;
        }

        .guest-cta-buttons .btn-outline {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .guest-cta-buttons .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .guest-cta-link {
            font-size: 0.9rem;
            margin-top: 12px;
        }

        .guest-cta-link a {
            color: white;
            text-decoration: none;
            opacity: 1;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 50px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.05);
            font-weight: 600;
            cursor: pointer;
            backdrop-filter: blur(6px);
        }

        .guest-cta-link a:hover {
            opacity: 1;
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.8);
            transform: translateX(-4px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .guest-cta-link a i {
            font-size: 1rem;
            transition: transform 0.3s ease;
        }

        .guest-cta-link a:hover i {
            transform: translateX(-3px);
        }

        /* ===== SECTION STYLES ===== */
        .section-padding {
            padding: 60px 0;
            position: relative;
            z-index: 2;
        }

        .section-title {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gasgo-blue);
            margin-bottom: 12px;
        }

        .section-subtitle {
            font-size: 1rem;
            color: #666;
            margin-bottom: 40px;
        }

        /* ===== PROMO CARDS ===== */
        .promo-card {
            background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
            border-radius: 24px;
            padding: 36px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, 0.1);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: 2px solid #f0f0f0;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .promo-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: -50%;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, transparent, rgba(247, 148, 29, 0.05));
            transform: skewX(-20deg);
            transition: all 0.4s ease;
            pointer-events: none;
        }

        .promo-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 20px 50px rgba(247, 148, 29, 0.25);
            border-color: var(--gasgo-orange);
            background: linear-gradient(135deg, #fff9f5 0%, #ffffff 100%);
        }

        .promo-card:hover::before {
            right: -20%;
        }

        .promo-card-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--gasgo-orange);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(247, 148, 29, 0.1), rgba(255, 107, 53, 0.05));
            border-radius: 20px;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        .promo-card:hover .promo-card-icon {
            transform: scale(1.15) rotate(5deg);
            background: linear-gradient(135deg, rgba(247, 148, 29, 0.2), rgba(255, 107, 53, 0.1));
        }

        .promo-card h5 {
            font-weight: 800;
            color: var(--gasgo-blue);
            margin-bottom: 18px;
            font-size: 1.3rem;
            line-height: 1.4;
            position: relative;
            z-index: 1;
        }

        .promo-badge {
            display: inline-block;
            padding: 8px 18px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 800;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            z-index: 1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .promo-badge.success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .promo-badge.info {
            background: linear-gradient(135deg, #17a2b8 0%, #00bcd4 100%);
            color: white;
        }

        .promo-rules {
            list-style: none;
            padding: 0;
            margin: 0;
            position: relative;
            z-index: 1;
        }

        .promo-rules li {
            padding: 12px 0;
            padding-left: 32px;
            position: relative;
            color: #333;
            font-size: 0.95rem;
            line-height: 1.6;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .promo-rules li:hover {
            color: var(--gasgo-orange);
            transform: translateX(4px);
        }

        .promo-rules li::before {
            content: '\f058';
            font-family: 'Font Awesome 5 Free', 'Font Awesome 7 Free', 'FontAwesome', sans-serif;
            font-weight: 900;
            color: var(--gasgo-orange);
            position: absolute;
            left: 0;
            top: 12px;
            font-size: 1.1rem;
            transition: all 0.2s ease;
        }

        .promo-rules li:hover::before {
            transform: scale(1.3);
        }

        /* ===== LOYALTY STEPS ===== */
        @keyframes stepBounce {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        @keyframes stepPulse {

            0%,
            100% {
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            }

            50% {
                box-shadow: 0 8px 30px rgba(247, 148, 29, 0.2);
            }
        }

        @keyframes iconFloat {

            0%,
            100% {
                transform: translateY(0px) rotate(0deg);
            }

            50% {
                transform: translateY(-8px) rotate(5deg);
            }
        }

        @keyframes numberPulse {

            0%,
            100% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }
        }

        @keyframes connectorFlow {
            0% {
                background: linear-gradient(90deg, var(--gasgo-orange), transparent);
            }

            50% {
                background: linear-gradient(90deg, transparent, var(--gasgo-orange));
            }

            100% {
                background: linear-gradient(90deg, var(--gasgo-orange), transparent);
            }
        }

        .loyalty-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .step-item {
            text-align: center;
            padding: 28px 20px;
            background: linear-gradient(135deg, #ffffff 0%, #fafbfc 100%);
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            overflow: hidden;
            border: 2px solid #f0f0f0;
            cursor: pointer;
        }

        .step-item::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(247, 148, 29, 0.1) 0%, transparent 70%);
            transform: scale(0);
            transition: transform 0.6s ease;
            z-index: 0;
        }

        .step-item:hover::before {
            transform: scale(1);
        }

        .step-item:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 15px 40px rgba(247, 148, 29, 0.2);
            border-color: var(--gasgo-orange);
            background: linear-gradient(135deg, #fff9f5 0%, #ffffff 100%);
        }

        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, var(--gasgo-orange), #ff6b35);
            color: white;
            border-radius: 50%;
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 15px;
            position: relative;
            z-index: 1;
            box-shadow: 0 6px 20px rgba(247, 148, 29, 0.3);
            transition: all 0.3s ease;
        }

        .step-item:hover .step-number {
            animation: numberPulse 0.6s ease infinite;
            box-shadow: 0 8px 28px rgba(247, 148, 29, 0.4);
        }

        .step-icon {
            font-size: 2.2rem;
            color: var(--gasgo-orange);
            margin: 12px 0 8px 0;
            position: relative;
            z-index: 1;
            display: inline-block;
            transition: all 0.3s ease;
        }

        .step-item:hover .step-icon {
            animation: iconFloat 0.8s ease infinite;
            color: #ff6b35;
            text-shadow: 0 4px 15px rgba(247, 148, 29, 0.3);
        }

        .step-item h6 {
            font-weight: 800;
            color: var(--gasgo-blue);
            margin-top: 12px;
            font-size: 1rem;
            position: relative;
            z-index: 1;
            transition: all 0.3s ease;
        }

        .step-item:hover h6 {
            color: var(--gasgo-orange);
        }

        .step-connector {
            position: absolute;
            top: 32px;
            right: -22px;
            width: 44px;
            height: 3px;
            background: linear-gradient(90deg, var(--gasgo-orange), transparent);
            display: none;
            border-radius: 2px;
            transition: all 0.3s ease;
            opacity: 0.6;
            animation: connectorFlow 2s ease-in-out infinite;
            z-index: 1;
        }

        .step-item:hover .step-connector {
            opacity: 1;
            width: 50px;
            height: 4px;
        }

        @media (min-width: 768px) {
            .step-connector {
                display: block;
            }

            .step-item:last-child .step-connector {
                display: none;
            }
        }

        /* ===== PROGRESS CARD ===== */
        .progress-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            margin: 30px 0;
        }

        .progress-card h5 {
            font-weight: 700;
            color: var(--gasgo-blue);
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .progress-card p {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 20px;
        }

        .progress-bar-gasgo {
            height: 14px;
            border-radius: 7px;
            background: #eee;
            overflow: hidden;
            margin-bottom: 20px;
        }

        .progress-bar-gasgo .fill {
            height: 100%;
            border-radius: 7px;
            background: linear-gradient(90deg, var(--gasgo-orange), #ff6b35);
            transition: width 0.6s ease;
            width: var(--fill-width, 0%);
        }

        .progress-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .progress-info-item {
            text-align: center;
        }

        .progress-info-item strong {
            display: block;
            font-size: 1.5rem;
            color: var(--gasgo-orange);
        }

        .progress-info-item small {
            color: #888;
            font-size: 0.8rem;
        }

        /* ===== STAMPS ===== */
        .stamp-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
        }

        .stamp {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            border: 2px dashed #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #ccc;
            transition: all 0.3s;
            font-weight: 700;
        }

        .stamp.filled {
            background: linear-gradient(135deg, var(--gasgo-orange), #ff6b35);
            border: none;
            color: white;
        }

        /* ===== REWARDS PREVIEW ===== */
        .rewards-preview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }

        .reward-item {
            background: linear-gradient(135deg, #f8f9fa, white);
            border: 2px solid #e0e0e0;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
            transition: all 0.3s;
        }

        .reward-item:hover {
            border-color: var(--gasgo-orange);
            box-shadow: 0 8px 25px rgba(247, 148, 29, 0.15);
        }

        .reward-icon {
            font-size: 2.5rem;
            color: var(--gasgo-orange);
            margin-bottom: 12px;
        }

        .reward-icon.earned {
            color: var(--gasgo-orange);
        }

        .reward-icon.inactive {
            opacity: 0.5;
        }

        .reward-item h6 {
            font-weight: 700;
            color: var(--gasgo-blue);
            margin-bottom: 8px;
        }

        .reward-requirement {
            font-size: 0.85rem;
            color: #888;
        }

        /* ===== AVAILABLE Fix the Loyalty & Promos logged-in progress widget so that points match delivered orders.

            Rules:
            - 1 loyalty point is earned per Delivered order.
            - If the user has 1 Delivered order, Points Earned and Available Points must both be 1.

            Bug:
            - UI shows Orders Completed = 1 but Points Earned = 0 and Available Points = 0.

            Tasks:
            1) Ensure points are computed from the same source as Orders Completed.
               - If there is no points ledger yet, set pointsEarned = deliveredOrdersCount and availablePoints = deliveredOrdersCount.
               - If a points ledger exists, ensure a +1 points transaction is created when an order becomes Delivered (idempotent per order).

            2) Update the progress bar calculation to use target = 100 points.
               - remaining = 100 - availablePoints
               - display “X more delivered orders to unlock voucher”

            3) Update the “No vouchers yet” message to match the same remaining value.

            Generate the corrected controller/service logic and the template updates.S ===== */
        .voucher-card {
            background: white;
            border: 2px solid var(--gasgo-orange);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            box-shadow: 0 4px 15px rgba(247, 148, 29, 0.1);
        }

        .voucher-info h6 {
            font-weight: 700;
            color: var(--gasgo-blue);
            margin-bottom: 5px;
        }

        .voucher-info p {
            font-size: 0.85rem;
            color: #888;
            margin: 0;
        }

        .voucher-action {
            display: flex;
            gap: 10px;
        }

        .voucher-action button {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }

        .voucher-action .btn-apply {
            background: var(--gasgo-orange);
            color: white;
        }

        .voucher-action .btn-apply:hover {
            opacity: 0.9;
        }

        /* ===== FAQ ACCORDION ===== */
        .faq-accordion {
            max-width: 800px;
            margin: 0 auto;
        }

        .accordion-button {
            background: white;
            border: 2px solid #e0e0e0;
            color: var(--gasgo-blue);
            font-weight: 600;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .accordion-button:not(.collapsed) {
            background: linear-gradient(135deg, var(--gasgo-orange), #ff6b35);
            color: white;
            border-color: var(--gasgo-orange);
            box-shadow: 0 4px 15px rgba(247, 148, 29, 0.2);
        }

        .accordion-button:hover {
            border-color: var(--gasgo-orange);
            background: linear-gradient(135deg, var(--gasgo-orange), #ff6b35);
            color: white;
        }

        .accordion-body {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-top: none;
            padding: 20px;
            border-radius: 0 0 12px 12px;
            margin-bottom: 10px;
            color: #555;
            line-height: 1.6;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .section-title {
                font-size: 1.5rem;
            }

            .guest-cta-banner {
                flex-direction: column;
                text-align: center;
                padding: 30px 20px;
            }

            .guest-cta-buttons {
                justify-content: center;
                width: 100%;
            }

            .progress-info {
                flex-direction: column;
            }

            .voucher-card {
                flex-direction: column;
                text-align: center;
            }

            .section-padding {
                padding: 40px 0;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Page Header -->
    <section class="page-header">
        <div class="container text-center">
            <h1 class="fw-bold" style="font-size: 2.5rem;">
                <i class="fas fa-gift me-2"></i>Loyalty & Promos
            </h1>
            <p class="mb-0" style="opacity:.9; font-size: 1.05rem;">
                Earn loyalty points with every order and unlock exclusive rewards
            </p>
        </div>
    </section>

    <div class="container section-padding">

        <!-- GUEST CTA BANNER -->
        @if ($isGuest)
            <div class="guest-cta-banner" data-aos="fade-up">
                <div>
                    <h3>Unlock Rewards & Exclusive Promos</h3>
                    <p>Login or register to track loyalty points and claim vouchers.</p>
                    <div class="guest-cta-link">
                        <a href="{{ url('/customer/product') }}"><i class="fas fa-arrow-left"></i>Continue browsing</a>
                    </div>
                </div>
                <div class="guest-cta-buttons">
                    <a href="{{ route('customer.login') }}" class="btn btn-primary">Register</a>
                    <a href="{{ route('customer.login') }}" class="btn btn-outline">Login</a>
                </div>
            </div>
        @else
            <!-- LOYALTY PROGRESS - TOP FOR LOGGED-IN USERS -->
            <div class="row justify-content-center mb-5" data-aos="fade-up">
                <div class="col-lg-8">
                    <div class="progress-card">
                        <h5>
                            <i class="fas fa-chart-line me-2" style="color: var(--gasgo-orange);"></i>
                            Your Loyalty Progress
                        </h5>

                        <div class="progress-info">
                            <div class="progress-info-item">
                                <strong>{{ $completedOrders }}</strong>
                                <small>Total Delivered Orders</small>
                            </div>
                            <div class="progress-info-item">
                                <strong>{{ $balance }}</strong>
                                <small>Points Earned</small>
                            </div>
                            <div class="progress-info-item">
                                <strong>{{ $balance }}</strong>
                                <small>Available Points</small>
                            </div>
                        </div>

                        <p class="text-muted mb-3" style="font-size: 0.9rem;">
                            Points are based on your total delivered spend on tank products only.
                        </p>

                        @if ($balance < $nextMilestone)
                            <p style="font-weight: 600; color: var(--gasgo-blue);">Progress to Unlock Voucher</p>
                            <div class="progress-bar-gasgo" style="--fill-width: {{ ($balance / $nextMilestone) * 100 }}%;">
                                <div class="fill"></div>
                            </div>
                            <p class="text-muted mb-3" style="font-size: 0.9rem;">
                                <strong>{{ $pointsToNextReward }} more points</strong> to unlock ₱{{ $nextReward }} OFF voucher
                            </p>
                        @else
                            <div
                                style="background: linear-gradient(135deg, var(--gasgo-orange), #ff6b35); color: white; padding: 20px; border-radius: 12px; text-align: center;">
                                <h6 class="mb-0" style="color: white;">
                                    <i class="fas fa-trophy me-2"></i>Congratulations! 🎉
                                </h6>
                                <p class="mb-0 mt-2" style="font-size: 0.95rem;">You've unlocked all loyalty rewards!</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- AVAILABLE VOUCHERS - Only for logged-in users -->
            <div class="my-5">
                <div data-aos="fade-up" class="mb-4">
                    <h2 class="section-title">
                        <i class="fas fa-ticket-alt me-2" style="color: var(--gasgo-orange);"></i>
                        Vouchers Available to Claim
                    </h2>
                </div>

                <div class="row g-4">
                    @forelse ($unlockedVouchers->where('isUnlocked', true) as $voucher)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="voucher-card"
                                style="background: linear-gradient(135deg, rgba(247, 148, 29, 0.08) 0%, rgba(15, 52, 96, 0.05) 100%); border: 2px solid var(--gasgo-orange); border-radius: 16px; padding: 20px; box-shadow: 0 4px 15px rgba(247, 148, 29, 0.1); transition: all 0.3s ease;">
                                <div
                                    style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                    <div>
                                        <i class="fas fa-tag"
                                            style="font-size: 1.8rem; color: var(--gasgo-orange); opacity: 0.8;"></i>
                                    </div>
                                    <span
                                        style="background: linear-gradient(135deg, #28a745, #20c997); color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                                        UNLOCKED
                                    </span>
                                </div>

                                <h6 style="font-weight: 700; margin-bottom: 4px; color: var(--gasgo-blue);">{{ $voucher->name }}
                                </h6>
                                <p style="color: #666; font-size: 0.9rem; margin-bottom: 12px;">{{ $voucher->description }}</p>

                                <div
                                    style="background: white; padding: 12px; border-radius: 12px; margin-bottom: 12px; border-left: 4px solid var(--gasgo-orange);">
                                    <div style="font-size: 1.5rem; font-weight: 700; color: var(--gasgo-orange);">
                                        ₱{{ number_format($voucher->discount_amount, 2) }}</div>
                                    <small style="color: #999;">Discount Value</small>
                                </div>

                                @if($voucher->isClaimed)
                                    <button type="button" class="btn" disabled
                                        style="width: 100%; background: #28a745; color: white; border: none; padding: 10px; border-radius: 10px; font-weight: 600; font-size: 0.9rem; cursor: not-allowed; opacity: 0.7;">
                                        <i class="fas fa-check me-1"></i> Claimed
                                    </button>
                                @else
                                    <form action="{{ route('customer.loyalty.claimVoucher') }}" method="POST" style="width: 100%;">
                                        @csrf
                                        <input type="hidden" name="voucher_id" value="{{ $voucher->id }}">
                                        <button type="submit" class="btn"
                                            style="width: 100%; background: linear-gradient(135deg, var(--gasgo-orange), #ff6b35); color: white; border: none; padding: 10px; border-radius: 10px; font-weight: 600; font-size: 0.9rem; transition: all 0.3s ease; cursor: pointer;">
                                            <i class="fas fa-arrow-right me-1"></i> Claim & Use at Checkout
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert"
                                style="background: linear-gradient(135deg, rgba(15, 52, 96, 0.05), rgba(247, 148, 29, 0.05)); border: 1px solid rgba(247, 148, 29, 0.2); border-radius: 12px; padding: 20px;"
                                role="alert" data-aos="fade-up">
                                <div style="display: flex; align-items: center; gap: 12px;">
                                    <i class="fas fa-info-circle" style="font-size: 1.2rem; color: var(--gasgo-orange);"></i>
                                    <div>
                                        <strong style="color: var(--gasgo-blue);">No vouchers unlocked yet!</strong>
                                        <p style="margin: 4px 0 0 0; font-size: 0.9rem; color: #666;">
                                            Earn <strong>{{ $pointsToNextReward }} more points</strong> to unlock your first
                                            voucher!
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforelse
                </div>
                @php $lockedVouchers = $unlockedVouchers->where('isUnlocked', false); @endphp
                @if ($lockedVouchers->count() > 0)
                    <div style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #f0f0f0;">
                        <h5 class="mb-3" style="color: var(--gasgo-blue); font-weight: 700;">
                            <i class="fas fa-lock me-2" style="color: #ccc;"></i>Vouchers You Can Unlock
                        </h5>
                        <div class="row">
                            @foreach ($lockedVouchers as $voucher)
                                <div class="col-md-6 col-lg-4 mb-4">
                                    <div class="voucher-card"
                                        style="background: linear-gradient(135deg, #f8f9fa 0%, #fcfcfc 100%); border: 2px dashed #ddd; border-radius: 16px; padding: 20px; opacity: 0.7; transition: all 0.3s ease;">
                                        <div
                                            style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                                            <div>
                                                <i class="fas fa-lock" style="font-size: 1.8rem; color: #999;"></i>
                                            </div>
                                            <span
                                                style="background: #e9ecef; color: #666; padding: 4px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">
                                                LOCKED
                                            </span>
                                        </div>

                                        <h6 style="font-weight: 700; margin-bottom: 4px; color: #999;">{{ $voucher->name }}</h6>
                                        <p style="color: #bbb; font-size: 0.9rem; margin-bottom: 12px;">{{ $voucher->description }}</p>

                                        <div
                                            style="background: #f0f0f0; padding: 12px; border-radius: 12px; margin-bottom: 12px; border-left: 4px solid #ddd;">
                                            <div style="font-size: 1.5rem; font-weight: 700; color: #999;">
                                                ₱{{ number_format($voucher->discount_amount, 2) }}</div>
                                            <small style="color: #bbb;">Discount Value</small>
                                        </div>

                                        <div style="background: rgba(247, 148, 29, 0.1); padding: 10px; border-radius: 8px;">
                                            <small style="color: var(--gasgo-orange); display: block; font-weight: 600;">
                                                <i class="fas fa-target me-1"></i>
                                                {{ max(0, $voucher->reward_points_required - $balance) }} more points to unlock
                                                (₱{{ number_format(max(0, $voucher->reward_points_required - $balance) * 100, 0) }}
                                                spend)
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- REWARDS PREVIEW / VOUCHER LADDER - For Logged-In Users -->
            <div class="my-5" data-aos="fade-up">
                <div class="mb-4">
                    <h2 class="section-title">
                        <i class="fas fa-gift me-2" style="color: var(--gasgo-orange);"></i>
                        Rewards You Can Earn
                    </h2>
                    <p class="section-subtitle">
                        See what you've earned and what's next
                    </p>
                </div>

                <div class="rewards-preview">
                    @forelse ($unlockedVouchers as $voucher)
                        <div class="reward-item" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                            <!-- LOGGED-IN: Personal Earned Rewards -->
                            <div class="reward-icon @if($voucher->isUnlocked) earned @else inactive @endif">
                                <i class="fas fa-tag"></i>
                            </div>
                            @if ($voucher->isUnlocked)
                                <span
                                    style="display: inline-block; padding: 6px 12px; background: rgba(40, 167, 69, 0.15); color: #28a745; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 10px;">
                                    ✓ Unlocked
                                </span>
                            @else
                                <span
                                    style="display: inline-block; padding: 6px 12px; background: rgba(0, 0, 0, 0.08); color: #666; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 10px;">
                                    Locked
                                </span>
                            @endif
                            <h6>{{ $voucher->name }}</h6>
                            <p class="reward-requirement">
                                <strong>₱{{ number_format($voucher->discount_amount, 2) }} OFF</strong> • Unlock at
                                {{ $voucher->reward_points_required }} points
                                (₱{{ number_format($voucher->reward_points_required * 100, 0) }} spend)
                                @if (!$voucher->isUnlocked)
                                    • {{ max(0, $voucher->reward_points_required - $balance) }} more points needed
                                    (₱{{ number_format(max(0, $voucher->reward_points_required - $balance) * 100, 0) }} spend)
                                @endif
                            </p>
                        </div>
                    @empty
                        <div style="grid-column: 1/-1; text-align: center; padding: 40px 20px;">
                            <i class="fas fa-inbox"
                                style="font-size: 3rem; color: var(--gasgo-orange); opacity: 0.5; margin-bottom: 20px;"></i>
                            <p style="color: #666;">No rewards available yet. Check back soon!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endif

        <!-- PROMOS TODAY -->
        <div class="my-5">
            <div data-aos="fade-up" class="mb-4">
                <h2 class="section-title">
                    <i class="fas fa-star me-2" style="color: var(--gasgo-orange);"></i>Promos Today
                </h2>
                <p class="section-subtitle">Special offers available for all customers</p>
            </div>

            <div class="row g-4">
                @foreach ($promos as $promo)
                    <div class="col-lg-6" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                        <div class="promo-card">
                            <i class="{{ $promo['icon'] }} promo-card-icon"></i>
                            <span class="promo-badge {{ $promo['badgeColor'] }}">{{ $promo['badge'] }}</span>
                            <h5>{{ $promo['title'] }}</h5>
                            <ul class="promo-rules">
                                @foreach ($promo['rules'] as $rule)
                                    <li>{{ $rule }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- LOYALTY PROGRAM STEPS -->
        <div class="my-5">
            <div data-aos="fade-up" class="mb-4">
                <h2 class="section-title">
                    <i class="fas fa-cogs me-2" style="color: var(--gasgo-orange);"></i>How Loyalty Works
                </h2>
                <p class="section-subtitle">Follow these simple steps to earn and redeem rewards</p>
            </div>

            <div class="loyalty-steps">
                @foreach ($loyaltySteps as $step)
                    <div class="step-item" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                        <div class="step-number">{{ $step['number'] }}</div>
                        <div class="step-icon">
                            <i class="{{ $step['icon'] }}"></i>
                        </div>
                        <h6>{{ $step['title'] }}</h6>
                        <div class="step-connector"></div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- REWARDS PREVIEW FOR GUESTS -->
        @if ($isGuest)
            <div class="my-5">
                <div data-aos="fade-up" class="mb-4">
                    <h2 class="section-title">
                        <i class="fas fa-gift me-2" style="color: var(--gasgo-orange);"></i>
                        Rewards Preview
                    </h2>
                    <p class="section-subtitle">
                        Unlock these vouchers when you start ordering
                    </p>
                </div>
                <div class="rewards-preview">
                    @foreach ($rewards as $reward)
                        <div class="reward-item" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                            <!-- GUEST: Tiered Voucher Ladder -->
                            <div class="reward-icon">
                                <i class="{{ $reward['icon'] }}"></i>
                            </div>
                            <div style="margin-bottom: 12px;">
                                <span
                                    style="display: inline-block; padding: 4px 12px; background: linear-gradient(135deg, var(--gasgo-orange), #ff6b35); color: white; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;">
                                    {{ $reward['tier'] }}
                                </span>
                            </div>
                            <h6>{{ $reward['title'] }}</h6>
                            <p class="reward-requirement">
                                {{ $reward['requirement'] }} @if(isset($reward['spendRequirement'])) •
                                ₱{{ number_format($reward['spendRequirement'], 0) }} spend @endif
                            </p>
                            <small style="color: #999; font-size: 0.75rem; display: block; margin-top: 10px;">Login to earn</small>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- FAQ SECTION -->
        <div class="my-5">
            <div data-aos="fade-up" class="mb-4">
                <h2 class="section-title">
                    <i class="fas fa-question-circle me-2" style="color: var(--gasgo-orange);"></i>Frequently Asked
                    Questions
                </h2>
                <p class="section-subtitle">Get answers to common questions</p>
            </div>

            <div class="faq-accordion" data-aos="fade-up">
                <div class="accordion" id="faqAccordion">
                    @foreach ($faqs as $faq)
                        <div class="accordion-item border-0 mb-2">
                            <h2 class="accordion-header">
                                <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button"
                                    data-bs-toggle="collapse" data-bs-target="#faq{{ $loop->index }}"
                                    aria-expanded="{{ $loop->first ? 'true' : 'false' }}" aria-controls="faq{{ $loop->index }}">
                                    {{ $faq['question'] }}
                                </button>
                            </h2>
                            <div id="faq{{ $loop->index }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
                                data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    {{ $faq['answer'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- CTA FOR ACTION -->
        @if ($isGuest)
            <div class="text-center mt-5 mb-3" data-aos="fade-up">
                <h3 style="color: var(--gasgo-blue); font-weight: 700;">Ready to Start Earning?</h3>
                <p style="color: #666; margin: 15px 0 25px;">Join thousands of happy
                    {{ trim($settings->brand_name_primary . ' ' . $settings->brand_name_accent) }} customers today.
                </p>
                <a href="{{ route('customer.login') }}" class="btn"
                    style="background: var(--gasgo-orange); color: white; padding: 12px 40px; border-radius: 25px; font-weight: 600; display: inline-block;">
                    <i class="fas fa-user-plus me-2"></i>Register or Login
                </a>
            </div>
        @else
            <div class="text-center mt-5 mb-3" data-aos="fade-up">
                <h3 style="color: var(--gasgo-blue); font-weight: 700;">Keep Earning!</h3>
                <p style="color: #666; margin: 15px 0 25px;">Place more orders and unlock more rewards.</p>
                <a href="{{ url('/customer/product') }}" class="btn"
                    style="background: var(--gasgo-orange); color: white; padding: 12px 40px; border-radius: 25px; font-weight: 600; display: inline-block;">
                    <i class="fas fa-shopping-cart me-2"></i>Browse Products
                </a>
            </div>
        @endif

    </div>

@endsection