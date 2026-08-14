@extends('layouts.customer')

@section('title', 'Checkout')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 100%);
        color: white; padding: 50px 0 60px; margin-bottom: -30px; position: relative;
    }
    .page-header::after {
        content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 60px;
        background: #ffffff; clip-path: ellipse(55% 100% at 50% 100%);
    }
    .checkout-card {
        background: white; border-radius: 20px; padding: 30px;
        box-shadow: 0 8px 30px rgba(0,0,0,.08); margin-bottom: 24px;
    }
    .checkout-card h5 { font-weight: 700; color: var(--gasgo-blue); margin-bottom: 20px; }
    .checkout-card h5 i { color: var(--gasgo-orange); margin-right: 8px; }
    .form-label { font-weight: 600; font-size: .9rem; color: #555; }
    .payment-option {
        border: 2px solid #eee; border-radius: 14px; padding: 16px 20px; cursor: pointer;
        display: flex; align-items: center; gap: 14px; transition: all .25s;
    }
    .payment-option:hover, .payment-option.selected {
        border-color: var(--gasgo-orange); background: var(--gasgo-orange-light);
    }
    .payment-option.disabled {
        opacity: .55;
        cursor: not-allowed;
    }
    .payment-option input[type="radio"] {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }
    .payment-option .pay-icon {
        width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem; color: white;
    }
    .payment-option .pay-icon.cash { background: linear-gradient(135deg, #27ae60, #2ecc71); }
    .payment-option .pay-icon.gcash { background: linear-gradient(135deg, #007dfe, #00b0ff); }

    .freebie-option {
        border: 2px solid #eee;
        border-radius: 14px;
        padding: 14px;
        height: 100%;
        cursor: pointer;
        transition: all .25s;
        position: relative;
    }
    .freebie-option:hover {
        border-color: var(--gasgo-orange);
        background: var(--gasgo-orange-light);
    }
    .freebie-option:has(input:disabled):hover {
        border-color: #eee;
        background: transparent;
    }
    .freebie-option input[type="radio"] {
        position: absolute;
        top: 12px;
        right: 12px;
    }
    .freebie-option input[type="radio"]:disabled {
        cursor: not-allowed;
    }
    .freebie-option.selected {
        border-color: var(--gasgo-orange);
        background: var(--gasgo-orange-light);
    }
    .freebie-image-wrapper { height: 220px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px; background: #f8f9fa; border-radius: 10px; }
    .freebie-image-wrapper img { max-width: 180px; max-height: 180px; object-fit: contain; }
    .freebie-title { font-weight: 700; color: #333; font-size: .95rem; }
    .freebie-desc { color: #666; font-size: .82rem; margin-bottom: 6px; }
    .freebie-stock { color: #1e7e34; font-size: .8rem; font-weight: 600; }

    /* Summary sidebar */
    .order-summary {
        background: white; border-radius: 20px; padding: 28px;
        box-shadow: 0 8px 30px rgba(0,0,0,.08); position: sticky; top: 100px;
    }
    .order-summary h5 { font-weight: 700; color: var(--gasgo-blue); border-bottom: 2px solid #f0f0f0; padding-bottom: 14px; }
    .summary-item { display: flex; justify-content: space-between; padding: 8px 0; font-size: .9rem; }
    .summary-item.total {
        font-size: 1.15rem; font-weight: 700; border-top: 2px solid var(--gasgo-orange);
        margin-top: 8px; padding-top: 14px;
    }
    .summary-item.total .val { color: var(--gasgo-orange); }
    .order-item-mini { display: flex; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f8f8f8; align-items: center; }
    .order-item-mini img { width: 46px; height: 46px; border-radius: 8px; object-fit: contain; background: #fff; }
    .order-item-mini .name { font-weight: 600; font-size: .85rem; color: #333; }
    .order-item-mini .qty { font-size: .78rem; color: #888; }
    .order-item-mini.disabled { opacity: 0.6; }
    .order-item-mini.disabled .name { color: #aaa; }
    .order-item-mini.disabled .qty { color: #ccc; }

    /* Map styles */
    #checkoutMap { height: 300px; border-radius: 14px; border: 2px solid #eee; z-index: 1; }
    .map-search-wrap { position: relative; margin-bottom: 12px; }
    .map-search-wrap input {
        width: 100%; padding: 10px 42px 10px 14px; border: 2px solid #eee;
        border-radius: 12px; font-size: .9rem; outline: none; transition: border-color .25s;
    }
    .map-search-wrap input:focus { border-color: var(--gasgo-orange); }
    .map-search-wrap .search-btn {
        position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
        background: var(--gasgo-orange); color: white; border: none; border-radius: 8px;
        width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
        cursor: pointer; font-size: .85rem;
    }
    .map-search-wrap .search-btn.loading {
        opacity: .85;
        cursor: wait;
    }
    .map-hint { font-size: .78rem; color: #888; margin-top: 8px; }
    .map-search-results {
        position: absolute; top: 100%; left: 0; right: 0; background: white;
        border: 1px solid #eee; border-radius: 10px; max-height: 200px; overflow-y: auto;
        z-index: 10; display: none; box-shadow: 0 4px 16px rgba(0,0,0,.1);
    }
    .map-search-results .result-item {
        padding: 10px 14px; cursor: pointer; font-size: .85rem; border-bottom: 1px solid #f5f5f5;
    }
    .map-search-results .result-item:hover { background: var(--gasgo-orange-light); }

    @media (max-width: 767.98px) {
        .checkout-card {
            padding: 20px 16px;
            border-radius: 16px;
        }
        .order-summary {
            position: static;
            padding: 20px 16px;
            margin-top: 16px;
        }
        #checkoutMap {
            height: 240px;
        }
        .payment-option {
            padding: 14px 16px;
            gap: 12px;
        }
        .freebie-image-wrapper {
            height: 160px;
        }
        .freebie-image-wrapper img {
            max-width: 130px;
            max-height: 130px;
        }
    }
</style>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endsection

@section('content')
<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold"><i class="fas fa-clipboard-check me-2"></i>Checkout</h1>
        <p class="mb-0" style="opacity:.9;">Complete your order details</p>
    </div>
</section>

{{-- Guest Alert --}}
@guest
<section class="container">
    <div class="alert alert-info alert-dismissible fade show mt-4" role="alert" style="border-radius: 16px; border: none; background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); padding: 24px;">
        <div style="display: flex; gap: 16px; align-items: start;">
            <i class="fas fa-info-circle" style="color: #1a6db0; font-size: 1.3rem; margin-top: 4px;"></i>
            <div>
                <h5 style="color: #1a6db0; margin-bottom: 8px; font-weight: 700;">Login or Register to Place Order</h5>
                <p style="color: #555; margin-bottom: 12px;">To complete your purchase, please log in to your account or create a new one. This helps us securely process your order and track your delivery.</p>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="{{ url('/customer/loginRegistration?tab=login&redirect=checkout') }}" class="btn" style="background: var(--gasgo-blue); color: white; border-radius: 8px; font-weight: 600; padding: 10px 20px;">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                    <a href="{{ url('/customer/loginRegistration?tab=register&redirect=checkout') }}" class="btn" style="background: var(--gasgo-orange); color: white; border-radius: 8px; font-weight: 600; padding: 10px 20px;">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </a>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
</section>
@endguest

<section class="container section-padding" style="position:relative;z-index:2;">
    {{-- Show checkout form only for authenticated users --}}
    @auth
    @php
        $smallRewardCount = (int) ($rewardPreview['small_reward_count'] ?? 0);
        $freebieChoices = $availableFreebies ?? collect();
        $paymentMethods = collect($homepageSettings->availablePaymentMethods())
            ->filter(fn ($method) => ($method['enabled'] ?? true))
            ->values();
        $selectedPaymentMethodKey = old('payment_method', $paymentMethods->first()['key'] ?? 'cash');
        
        $resolveImageUrl = function (?string $path): ?string {
            if (! $path) {
                return null;
            }
            $normalized = ltrim($path, '/');
            if (str_starts_with($normalized, 'http://') || str_starts_with($normalized, 'https://')) {
                return $path;
            }
            if (str_starts_with($normalized, 'images/')) {
                return asset($normalized);
            }

            if (str_starts_with($normalized, 'storage/')) {
                $normalized = substr($normalized, 8);
            }

            return \Illuminate\Support\Facades\Storage::url($normalized);
        };
    @endphp
    <form action="{{ route('customer.order.store') }}" method="POST" id="checkoutForm" enctype="multipart/form-data">
        @csrf
        @if (session('success'))
            <div class="alert alert-success" style="margin-bottom: 18px;">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger" style="margin-bottom: 18px;">
                {{ session('error') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger" style="margin-bottom: 18px;">
                <ul style="margin: 0; padding-left: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Delivery Address -->
                <div class="checkout-card">
                    <h5><i class="fas fa-map-marker-alt"></i>Delivery Address</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control form-control-gasgo" name="customer_name" value="{{ Auth::user()->name }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-control form-control-gasgo" name="contact_number" value="{{ old('contact_number', Auth::user()->phone) }}" placeholder="09XX XXX XXXX" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Complete Address</label>
                            <textarea class="form-control form-control-gasgo" name="delivery_address" rows="3" placeholder="House/Unit No., Street, Barangay, City/Municipality" required>{{ trim(old('delivery_address', Auth::user()->address ?? '')) }}</textarea>
                            <small class="text-muted d-block mt-1">
                                <i class="fas fa-info-circle me-1"></i>This address from your profile will appear. You can modify it for this order.
                            </small>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Delivery Notes (optional)</label>
                            <input type="text" class="form-control form-control-gasgo" name="notes" value="{{ old('notes') }}" placeholder="Landmark, gate color, etc.">
                        </div>
                        <div class="col-12 mt-2">
                            <label class="form-label"><i class="fas fa-map me-1" style="color:var(--gasgo-orange)"></i>Pin Your Location <span style="color: #e74c3c; font-weight: 600;">*</span></label>
                            <div class="map-search-wrap">
                                <input type="text" id="mapSearch" placeholder="Search address or place..." autocomplete="off">
                                    <button type="button" class="search-btn" id="mapSearchBtn"><i class="fas fa-search" id="mapSearchBtnIcon"></i></button>
                                <div class="map-search-results" id="searchResults"></div>
                            </div>
                            <div id="checkoutMap"></div>
                            <div class="d-flex justify-content-between align-items-center" style="margin-top: 12px;">
                                <p class="map-hint mb-0"><i class="fas fa-info-circle me-1"></i>Click on the map or drag the pin to set your exact delivery location</p>
                                      <button type="button" id="useMyLocationBtn" class="btn btn-sm mt-1" style="background:var(--gasgo-blue);color:white;border-radius:8px;font-size:.78rem;"><i class="fas fa-crosshairs me-1"></i>Use My Location</button>
                            </div>
                            <div class="alert alert-info mt-3 mb-3" role="alert" style="border-radius: 12px;">
                                <i class="fas fa-map-pin me-2"></i>
                                <strong>Location required:</strong> Please pin your exact delivery location on the map before placing your order.
                            </div>
                            <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                            <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                            <input type="hidden" name="address_full" id="addressFull" value="{{ old('address_full') }}">
                            <input type="hidden" name="user_pinned" id="userPinnedFlag" value="{{ old('user_pinned', 0) }}">
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="checkout-card">
                    <h5><i class="fas fa-credit-card"></i>Payment Method</h5>
                    <div class="row g-3">
                        @foreach ($paymentMethods as $method)
                        <div class="col-md-6">
                            <label
                                class="payment-option {{ $selectedPaymentMethodKey === $method['key'] ? 'selected' : '' }}"
                                data-payment-method="{{ $method['key'] }}"
                                data-label="{{ $method['label'] }}"
                                data-description="{{ $method['description'] ?? '' }}"
                                data-instructions="{{ $method['instructions'] ?? '' }}"
                                data-account-name="{{ $method['account_name'] ?? '' }}"
                                data-account-number="{{ $method['account_number'] ?? '' }}"
                                data-requires-proof="{{ !empty($method['requires_proof']) ? '1' : '0' }}"
                                style="position:relative;"
                            >
                                <input type="radio" name="payment_method" value="{{ $method['key'] }}" {{ $selectedPaymentMethodKey === $method['key'] ? 'checked' : '' }}>
                                @if(!empty($method['image_url']))
                                    <div class="pay-icon" style="background:white;overflow:hidden;padding:4px;">
                                        <img src="{{ $method['image_url'] }}" alt="{{ $method['label'] }}" style="width:100%;height:100%;object-fit:contain;">
                                    </div>
                                @else
                                    <div class="pay-icon {{ $method['color'] ?? 'info' }}"><i class="{{ $method['icon'] ?? 'fas fa-credit-card' }}"></i></div>
                                @endif
                                <div>
                                    <div class="fw-bold">{{ $method['label'] }}</div>
                                    <small class="text-muted">{{ $method['description'] ?: 'Select this method to see payment instructions.' }}</small>
                                </div>
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <div id="paymentMethodDetails" style="display:none; margin-top:20px; padding:16px; background:#e7fff0; border-radius:12px; border-left:4px solid #007dfe;">
                        <h6 class="fw-bold mb-2" style="color:#007dfe;"><i class="fas fa-info-circle me-2"></i><span id="paymentMethodDetailsTitle">Payment Details</span></h6>
                        <p class="text-muted mb-2" id="paymentMethodDetailsDescription" style="font-size:.9rem;"></p>
                        <div class="p-3 bg-white rounded mb-3" id="paymentMethodDetailsMeta" style="border:1px solid #c8f4dd; display:none;"></div>
                        <div class="alert alert-info mb-0" id="paymentMethodDetailsInstructions" style="font-size:.85rem; display:none;"></div>
                    </div>

                    <div id="paymentProofSection" style="display:none; margin-top:20px;">
                        <div class="form-group">
                            <label class="form-label fw-bold">Upload Proof of Payment <span class="text-danger">*</span> <small style="font-weight:400;"> (Only required for methods that need proof)</small></label>
                            <p class="text-muted small mb-2">Upload a screenshot or photo of your payment transaction.</p>
                            <div class="mb-2">
                                <input type="file" name="proof_of_payment" id="proofOfPayment" class="form-control form-control-gasgo @error('proof_of_payment') is-invalid @enderror" accept="image/*" data-required="false">
                                <small class="text-muted d-block mt-1">Accepted formats: JPG, PNG, GIF (Max 5MB)</small>
                                @error('proof_of_payment')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="proofPreview" style="margin-top:10px;"></div>
                        </div>
                    </div>
                </div>

                <!-- Urgent Order Option -->
                <div class="checkout-card" style="background: linear-gradient(135deg, rgba(247, 148, 29, 0.05) 0%, rgba(33, 150, 243, 0.05) 100%); border: 2px solid #f0f0f0;">
                    <h5><i class="fas fa-bolt" style="color: var(--gasgo-orange);"></i>Delivery Options</h5>
                    @if(!empty($hasCylinderProducts))
                        <div class="mb-3">
                            <label class="form-label">Cylinder Transaction Type</label>
                            <select class="form-select form-control-gasgo" name="transaction_type" required>
                                <option value="exchange" {{ old('transaction_type', 'exchange') === 'exchange' ? 'selected' : '' }}>Exchange</option>
                                <option value="new_cylinder" {{ old('transaction_type') === 'new_cylinder' ? 'selected' : '' }}>New Cylinder</option>
                            </select>
                            <small class="text-muted d-block mt-1">
                                Applies only to cylinder products. Non-cylinder items will be deducted normally.
                            </small>
                        </div>
                    @else
                        <input type="hidden" name="transaction_type" value="not_tank">
                    @endif
                    <div style="display: flex; align-items: center; gap: 12px; padding: 16px; background: white; border-radius: 12px; border: 1px solid #eee;">
                        <input 
                            type="checkbox" 
                            id="isUrgent" 
                            name="is_urgent" 
                            class="form-check-input" 
                            style="width: 24px; height: 24px; cursor: pointer; accent-color: var(--gasgo-orange);"
                            value="1"
                        >
                        <div style="flex: 1;">
                            <label for="isUrgent" style="cursor: pointer; margin: 0; font-weight: 600; color: #333; display: block;">Mark Order as Urgent <i class="fas fa-rocket" style="color: var(--gasgo-orange); margin-left: 8px;"></i></label>
                            <small style="color: #666; display: block; margin-top: 4px;">Prioritize your delivery for faster service</small>
                        </div>
                    </div>
                </div>

                <!-- ===== AVAILABLE VOUCHERS SECTION ===== -->
                @php
                    $availableVouchers = $availableVouchers ?? collect();
                @endphp
                @if ($availableVouchers->count() > 0)
                <div class="checkout-card">
                    <h5><i class="fas fa-ticket-alt me-2" style="color: var(--gasgo-orange);"></i>Your Available Vouchers</h5>
                    <p class="text-muted mb-3" style="font-size:.88rem;">
                        Select a voucher to apply a discount to your order
                    </p>

                    <div id="vouchersContainer">
                        @foreach ($availableVouchers as $voucher)
                        <div class="voucher-item mb-3" data-voucher-id="{{ $voucher->id }}" data-discount="{{ $voucher->discount_amount }}" style="border: 2px solid #eee; border-radius: 14px; padding: 16px; transition: all 0.25s;">
                            <div class="d-flex justify-content-between align-items-start">
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="font-weight: 800; color: var(--gasgo-orange); font-size: 1.8rem;">
                                            ₱{{ (int) $voucher->discount_amount }}
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: var(--gasgo-blue);">OFF Voucher</div>
                                            <small style="color: #888; font-size: 0.8rem;">
                                                Expires in <strong>{{ (int) $voucher->isDaysUntilExpiry() }}</strong> day{{ (int) $voucher->isDaysUntilExpiry() === 1 ? '' : 's' }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="btn voucher-apply-btn" 
                                        style="background: var(--gasgo-orange); color: white; border: none; border-radius: 8px; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;"
                                            data-voucher-id="{{ $voucher->id }}" data-discount="{{ $voucher->discount_amount }}">
                                    <i class="fas fa-check-circle me-1"></i>Apply Voucher
                                </button>
                            </div>
                            <div id="appliedBadge-{{ $voucher->id }}" style="display: none; color: #27ae60; font-size: 0.8rem; font-weight: 600; margin-top: 10px;">
                                <i class="fas fa-check-circle me-1"></i>✓ Applied
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <input type="hidden" name="voucher_id" id="voucherId" value="">
                </div>
                @endif

                <!-- ===== FREEBIE SECTION ===== -->
                @if ($availableFreebies->isNotEmpty())
                    <div id="freebiesSection" class="checkout-card">
                        <h5><i class="fas fa-gift"></i>Select Your Freebie</h5>
                        <p class="text-muted mb-3" style="font-size:.88rem;">
                            You can choose <strong>1 freebie item</strong> from below at no extra cost!
                        </p>
                        
                        <!-- Freebie requirement hint -->
                        @if ($lockedFreebies->isNotEmpty())
                            <div style="background: rgba(0,123,254,0.08); border-left: 4px solid #007dfe; padding: 10px 12px; border-radius: 8px; margin-bottom: 16px;">
                                <small style="color: #007dfe; display: block;"><i class="fas fa-info-circle me-1"></i>Some freebies require a minimum number of items ordered. Add more items to unlock them!</small>
                            </div>
                        @endif

                        @if ($errors->has('selected_freebie_id'))
                            <div class="alert alert-danger py-2 px-3" style="font-size:.85rem;">
                                {{ $errors->first('selected_freebie_id') }}
                            </div>
                        @endif

                        @if ($freebieChoices->isEmpty())
                            <div class="alert alert-warning mb-0">
                                No freebies are currently available. Please try again later.
                            </div>
                        @else
                            <div class="row g-3">
                                @foreach ($freebieChoices as $freebie)
                                    @php
                                        $freebieImageUrl = $resolveImageUrl($freebie->image);
                                        $pointsRequired = $freebie->reward_points_required ?? 0;
                                        $isUnlocked = $pointsRequired <= $totalCheckoutItems;
                                        $itemsNeeded = $pointsRequired - $totalCheckoutItems;
                                        $isSelected = (string) old('selected_freebie_id') === (string) $freebie->id;
                                    @endphp
                                    <div class="col-lg-4 col-md-6">
                                        @if($isSelected)
                                        <label class="freebie-option selected" @if(!$isUnlocked) style="opacity: 0.6; cursor: not-allowed; position: relative;" @endif>
                                        @else
                                        <label class="freebie-option" @if(!$isUnlocked) style="opacity: 0.6; cursor: not-allowed; position: relative;" @endif>
                                        @endif
                                            @if(!$isUnlocked)
                                                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.05); border-radius: 10px; z-index: 1; display: flex; align-items: center; justify-content: center;">
                                                    <div style="background: rgba(0,0,0,0.8); color: white; padding: 8px 12px; border-radius: 6px; font-size: 0.75rem; text-align: center; font-weight: 600;">
                                                        <i class="fas fa-lock me-1"></i>Need {{ $itemsNeeded }} more item{{ $itemsNeeded !== 1 ? 's' : '' }}
                                                    </div>
                                                </div>
                                            @endif
                                            <input
                                                type="radio"
                                                name="selected_freebie_id"
                                                value="{{ $freebie->id }}"
                                                {{ (string) old('selected_freebie_id') === (string) $freebie->id ? 'checked' : '' }}
                                                {{ !$isUnlocked ? 'disabled' : '' }}
                                            >
                                            @if($freebieImageUrl)
                                                <div class="freebie-image-wrapper">
                                                    <img src="{{ $freebieImageUrl }}" alt="{{ $freebie->name }}">
                                                </div>
                                            @endif
                                            <div class="freebie-title">{{ $freebie->name }}</div>
                                            <div class="freebie-desc">{{ $freebie->description ?: 'Complimentary reward item' }}</div>
                                            <div class="freebie-stock">
                                                {{ $freebie->stock }} available
                                                @if($pointsRequired > 0)
                                                    <br>
                                                    <small style="color: #f79429; font-weight: 600;">
                                                        <i class="fas fa-star me-1"></i>Requires {{ $pointsRequired }} item{{ $pointsRequired !== 1 ? 's' : '' }}
                                                    </small>
                                                @else
                                                    <br>
                                                    <small style="color: #27ae60; font-weight: 600;">
                                                        <i class="fas fa-check-circle me-1"></i>Free to claim anytime
                                                    </small>
                                                @endif
                                            </div>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-lg-4">
                <div class="order-summary">
                    <h5><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                    @foreach ($cartItems as $item)
                    <div class="order-item-mini" data-cart-id="{{ $item->id }}" data-product-id="{{ $item->product_id }}" data-price="{{ $item->product->price * $item->quantity }}" data-is-buy-now="false">
                        @if($item->product->resolved_image)
                            <img src="{{ $item->product->resolved_image }}" alt="{{ $item->product->name }}">
                        @else
                            <span class="text-muted small">No image available</span>
                        @endif
                        <div class="flex-grow-1">
                            <div class="name">{{ $item->product->name }}</div>
                            <div class="qty">Qty: {{ $item->quantity }} &times; ₱{{ number_format($item->product->price, 2) }}</div>
                        </div>
                        <div class="fw-bold" style="font-size:.9rem;">₱{{ number_format($item->product->price * $item->quantity, 2) }}</div>
                    </div>
                    @endforeach
                    <div class="summary-item mt-3"><span>Subtotal</span><span id="summarySubtotal">₱{{ number_format($subtotal, 2) }}</span></div>
                    <div class="summary-item"><span>Delivery Fee</span><span id="summaryDeliveryFee" data-value="{{ number_format($deliveryFee, 2, '.', '') }}">₱{{ number_format($deliveryFee, 2) }}</span></div>
                    
                    <!-- Voucher Discount Line Item (shown conditionally) -->
                    <div id="discountSummaryRow" class="summary-item" style="display: none; color: #27ae60;">
                        <span><i class="fas fa-tag me-1" style="color:var(--gasgo-orange);"></i>Voucher Discount</span>
                        <span id="discountAmount" style="font-weight: 700; color: #27ae60; font-size: 1rem;">-₱0.00</span>
                    </div>
                    
                    <div class="summary-item total"><span>Total</span><span class="val" id="summaryTotal">₱{{ number_format($subtotal + $deliveryFee, 2) }}</span></div>
                    
                    <input type="hidden" id="selectedCartIds" name="selected_cart_ids" value="{{ $cartItems->pluck('id')->implode(',') }}">
                    <input type="hidden" id="selectedProductIds" name="selected_product_ids" value="{{ $cartItems->pluck('product_id')->implode(',') }}">
                    
                    <div id="checkoutErrors" style="display: none; background: #fee; border: 1px solid #fcc; color: #c33; padding: 12px; border-radius: 8px; margin-bottom: 12px; font-size: 0.9rem;"></div>
                                    <div id="checkoutDebug" style="display:none; background:#eef; border:1px solid #cce; color:#114; padding:8px; border-radius:6px; font-size:0.8rem; margin-bottom:12px;">
                                        <strong>DEBUG</strong>: <span id="debugMsg">init</span>
                                        <div style="margin-top:6px">Lat: <span id="dbgLat">-</span> &nbsp; Lng: <span id="dbgLng">-</span> &nbsp; Pinned: <span id="dbgPinned">0</span></div>
                                    </div>
                    
                    <button type="submit" id="placeOrderBtn" class="btn btn-gasgo w-100 mt-3" style="cursor: pointer;">
                        <i class="fas fa-check-circle me-2"></i><span>Place Order</span>
                    </button>
                    <a href="{{ route('customer.cart') }}" class="btn btn-gasgo-outline w-100 mt-2" style="padding:12px;">
                        <i class="fas fa-arrow-left me-2"></i>Back to Cart
                    </a>
                </div>
            </div>
        </div>
    </form>
    @endauth
</section>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function selectPayment(el, method) {
    document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    const radio = el.querySelector('input[type="radio"]');
    if (radio && !radio.disabled) {
        radio.checked = true;
    }

    const paymentMethodDetails = document.getElementById('paymentMethodDetails');
    const paymentMethodDetailsTitle = document.getElementById('paymentMethodDetailsTitle');
    const paymentMethodDetailsDescription = document.getElementById('paymentMethodDetailsDescription');
    const paymentMethodDetailsMeta = document.getElementById('paymentMethodDetailsMeta');
    const paymentMethodDetailsInstructions = document.getElementById('paymentMethodDetailsInstructions');
    const paymentProofSection = document.getElementById('paymentProofSection');
    const proofInput = document.getElementById('proofOfPayment');
    const imageUrl = el.dataset.imageUrl || '';
    const requiresProof = el.dataset.requiresProof === '1';
    const label = el.dataset.label || method;
    const description = el.dataset.description || '';
    const instructions = el.dataset.instructions || '';
    const accountName = el.dataset.accountName || '';
    const accountNumber = el.dataset.accountNumber || '';

    if (paymentMethodDetails && paymentMethodDetailsTitle && paymentMethodDetailsDescription && paymentMethodDetailsMeta && paymentMethodDetailsInstructions) {
        paymentMethodDetails.style.display = method ? 'block' : 'none';
        paymentMethodDetailsTitle.textContent = label;
        paymentMethodDetailsDescription.textContent = description || 'Review the payment instructions below before placing your order.';

        paymentMethodDetailsMeta.innerHTML = '';
        const hasMeta = accountName || accountNumber;
        if (hasMeta) {
            paymentMethodDetailsMeta.style.display = 'block';
            if (imageUrl) {
                const imageWrap = document.createElement('div');
                imageWrap.style.marginBottom = '10px';
                const img = document.createElement('img');
                img.src = imageUrl;
                img.alt = label;
                img.style.maxWidth = '90px';
                img.style.maxHeight = '90px';
                img.style.objectFit = 'contain';
                img.style.border = '1px solid #ddd';
                img.style.borderRadius = '8px';
                img.style.padding = '4px';
                img.style.background = '#fff';
                imageWrap.appendChild(img);
                paymentMethodDetailsMeta.appendChild(imageWrap);
            }
            if (accountName) {
                const nameWrap = document.createElement('div');
                const nameLabel = document.createElement('span');
                nameLabel.className = 'text-muted small';
                nameLabel.textContent = 'Account Name:';
                const nameValue = document.createElement('div');
                nameValue.className = 'fw-bold';
                nameValue.style.color = '#007dfe';
                nameValue.textContent = accountName;
                nameWrap.appendChild(nameLabel);
                nameWrap.appendChild(nameValue);
                paymentMethodDetailsMeta.appendChild(nameWrap);
            }
            if (accountNumber) {
                const numberWrap = document.createElement('div');
                numberWrap.style.marginTop = '10px';
                const numberLabel = document.createElement('span');
                numberLabel.className = 'text-muted small';
                numberLabel.textContent = 'Account Number / Reference:';
                const numberValue = document.createElement('div');
                numberValue.className = 'fw-bold';
                numberValue.style.color = '#007dfe';
                numberValue.style.fontSize = '1.1rem';
                numberValue.textContent = accountNumber;
                numberWrap.appendChild(numberLabel);
                numberWrap.appendChild(numberValue);
                paymentMethodDetailsMeta.appendChild(numberWrap);
            }
        } else {
            paymentMethodDetailsMeta.style.display = 'none';
        }

        if (instructions) {
            paymentMethodDetailsInstructions.style.display = 'block';
            paymentMethodDetailsInstructions.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>' + instructions;
        } else if (requiresProof) {
            paymentMethodDetailsInstructions.style.display = 'block';
            paymentMethodDetailsInstructions.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>After payment, please upload a screenshot or photo of your proof of payment below.';
        } else {
            paymentMethodDetailsInstructions.style.display = 'none';
        }
    }
    
    if (requiresProof) {
        if (paymentProofSection) paymentProofSection.style.display = 'block';
        if (proofInput) {
            proofInput.setAttribute('required', 'required');
            proofInput.setAttribute('data-required', 'true');
        }
    } else {
        if (paymentProofSection) paymentProofSection.style.display = 'none';
        if (proofInput) {
            proofInput.removeAttribute('required');
            proofInput.setAttribute('data-required', 'false');
        }
    }
}

function validateCheckout() {
    try {
        const errorDiv = document.getElementById('checkoutErrors');
        let errors = [];

        const selectedCartIds = document.getElementById('selectedCartIds')?.value || '';
        const selectedPaymentOption = document.querySelector('.payment-option.selected') || document.querySelector('input[name="payment_method"]:checked')?.closest('.payment-option');
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked')?.value || 'cash';
        const proofInput = document.getElementById('proofOfPayment');
        const customerName = (document.querySelector('[name="customer_name"]')?.value || '').trim();
        const contactNumber = (document.querySelector('[name="contact_number"]')?.value || '').trim();
        const deliveryAddress = (document.querySelector('[name="delivery_address"]')?.value || '').trim();
        const latitude = (document.getElementById('latitude')?.value || '').trim();
        const longitude = (document.getElementById('longitude')?.value || '').trim();

        console.log('Validation check:', {
            customerName, contactNumber, deliveryAddress, latitude, longitude, selectedCartIds, paymentMethod
        });

        // Check all required fields
        if (!customerName) {
            errors.push('⚠️ Please enter your full name.');
        }

        if (!contactNumber) {
            errors.push('⚠️ Please enter your contact number.');
        }

        if (!deliveryAddress) {
            errors.push('⚠️ Please enter your delivery address.');
        }

        // Location is required for all payment methods
        if (!latitude || !longitude) {
            errors.push('📍 Please pin your location on the map before placing order.');
        }

        // Check if items exist
        if (!selectedCartIds) {
            errors.push('⚠️ Your cart is empty. Please add items before placing an order.');
        }

        // Check proof upload if the selected method requires it
        if (selectedPaymentOption && selectedPaymentOption.dataset.requiresProof === '1') {
            if (!proofInput || !proofInput.files || proofInput.files.length === 0) {
                errors.push('⚠️ Please upload proof of payment for the selected payment method.');
            } else {
                const file = proofInput.files[0];
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                if (!validTypes.includes(file.type)) {
                    errors.push('⚠️ Please upload a valid image file (JPG, PNG, or GIF).');
                }
                if (file.size > 5 * 1024 * 1024) {
                    errors.push('⚠️ File size must not exceed 5MB.');
                }
            }
        }
        
        // Display errors if any
        if (errors.length > 0) {
            console.log('Validation errors:', errors);
            if (errorDiv) {
                errorDiv.innerHTML = errors.join('<br>');
                errorDiv.style.display = 'block';
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                alert(errors.join('\n'));
            }
            return false;
        }
        
        // Clear error display
        if (errorDiv) {
            errorDiv.style.display = 'none';
            errorDiv.innerHTML = '';
        }
        
        console.log('Validation passed!');
        return true;
    } catch (err) {
        console.error('Validation error:', err);
        alert('An error occurred during validation: ' + err.message);
        return false;
    }
}

// Handle proof of payment file preview
function setupProofInputPreview() {
    const proofInput = document.getElementById('proofOfPayment');
    if (proofInput) {
        proofInput.addEventListener('change', function() {
            const file = this.files[0];
            const preview = document.getElementById('proofPreview');
            
            if (!preview) return;
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = '<div class="mt-2"><img src="' + e.target.result + '" style="max-width:200px; max-height:200px; border-radius:8px; border:1px solid #ddd;" alt="Preview"></div>';
                };
                reader.readAsDataURL(file);
            } else {
                preview.innerHTML = '';
            }
        });
    }
}

const defaultLat = 16.0433;
const defaultLng = 120.3654;
const locationSearchUrl = "{{ route('geocode.search') }}";
const locationReverseUrl = "{{ route('geocode.reverse') }}";

let map, marker;
let userPinnedLocation = false; // when true, do not auto-reposition map/address
function setUserPinnedFlag() {
    const el = document.getElementById('userPinnedFlag');
    if (el) el.value = userPinnedLocation ? '1' : '0';
}
let searchTimeout;
let searchAbortController = null;
let reverseAbortController = null;
const searchCache = new Map();
const reverseCache = new Map();

function formatPinnedLocation(lat, lng) {
    return 'Pinned location (' + lat.toFixed(6) + ', ' + lng.toFixed(6) + ')';
}

function normalizeBarangayQuery(query) {
    return query
        .replace(/\bbrgy\.?\b/ig, 'barangay')
        .replace(/\bbrg\.?\b/ig, 'barangay')
        .replace(/\s+/g, ' ')
        .trim();
}

function getAddressContext() {
    const address = document.querySelector('[name="delivery_address"]').value.trim();
    if (!address) {
        return '';
    }
    const parts = address.split(',').map(part => part.trim()).filter(Boolean);
    return parts.slice(-2).join(', ');
}

function buildSearchQueries(inputQuery) {
    const base = inputQuery.replace(/\s+/g, ' ').trim();
    const normalized = normalizeBarangayQuery(base);
    const context = getAddressContext();
    const querySet = new Set([base, normalized, normalized + ', Philippines']);

    if (!/\bbarangay\b|\bbrgy\b/i.test(base)) {
        querySet.add('Barangay ' + base);
        querySet.add('Barangay ' + base + ', Philippines');
    }

    if (context) {
        querySet.add(base + ', ' + context);
        querySet.add(normalized + ', ' + context);
        querySet.add(normalized + ', ' + context + ', Philippines');
    }

    return Array.from(querySet).filter(Boolean);
}

function composeMapLabel(street, suburb, city, full) {
    const parts = [street, suburb, city].filter(Boolean);
    if (parts.length > 0) {
        return parts.join(', ');
    }

    if (full) {
        return full.split(',').slice(0, 3).join(', ').trim();
    }

    return '';
}



function setMapSearchButtonLoading(isLoading) {
    const btn = document.getElementById('mapSearchBtn');
    const icon = document.getElementById('mapSearchBtnIcon');
    if (!btn || !icon) {
        return;
    }
    btn.classList.toggle('loading', isLoading);
    icon.className = isLoading ? 'fas fa-spinner fa-spin' : 'fas fa-search';
}

function updateLocationFields(mapLabel, fullAddress, lat, lng, onlyIfEmpty = false, updateDeliveryAddress = false) {
    document.getElementById('mapSearch').value = mapLabel || fullAddress || '';
    document.getElementById('addressFull').value = fullAddress || mapLabel || '';

    if (typeof lat === 'number' && typeof lng === 'number') {
        document.getElementById('latitude').value = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
    }

    // update debug panel if present
    try {
        const dbgLat = document.getElementById('dbgLat');
        const dbgLng = document.getElementById('dbgLng');
        const dbgPinned = document.getElementById('dbgPinned');
        if (dbgLat) dbgLat.textContent = (lat ? lat.toFixed(7) : document.getElementById('latitude').value || '-');
        if (dbgLng) dbgLng.textContent = (lng ? lng.toFixed(7) : document.getElementById('longitude').value || '-');
        if (dbgPinned) dbgPinned.textContent = userPinnedLocation ? '1' : '0';
    } catch (err) {
        console.debug('Debug panel update failed', err);
    }

    // Only update delivery address if explicitly requested or if it's empty
    const deliveryAddress = document.querySelector('[name="delivery_address"]');
    if (updateDeliveryAddress && (!onlyIfEmpty || !deliveryAddress.value.trim())) {
        deliveryAddress.value = fullAddress || mapLabel || '';
    }
}

function parseReversePayload(payload, lat, lng) {
    const street = payload.street || null;
    const suburb = payload.suburb || payload.address?.barangay || payload.address?.suburb || payload.address?.village || null;
    const city = payload.city || payload.address?.city || payload.address?.town || payload.address?.municipality || null;
    const state = payload.address?.state || payload.address?.province || null;
    
    // Compose clean full address with only essential parts (street, barangay, city, state)
    const cleanParts = [street, suburb, city, state].filter(Boolean);
    const full = cleanParts.length > 0 ? cleanParts.join(', ') : formatPinnedLocation(lat, lng);
    
    const mapLabel = composeMapLabel(street, suburb, city, full) || full;
    return { street, suburb, city, full, mapLabel };
}

function reverseGeocode(lat, lng, zoomLevel = null, updateDeliveryAddress = false) {
    const zoom = Math.max(5, Math.min(18, Number.isFinite(zoomLevel) ? Math.round(zoomLevel) : 18));
    const key = lat.toFixed(5) + ':' + lng.toFixed(5) + ':' + zoom;
    setMapSearchButtonLoading(true);

    if (reverseCache.has(key)) {
        const cached = reverseCache.get(key);
        updateLocationFields(cached.mapLabel, cached.full, lat, lng, false, updateDeliveryAddress);
        setMapSearchButtonLoading(false);
        return;
    }

    if (reverseAbortController) {
        reverseAbortController.abort();
    }
    reverseAbortController = new AbortController();

    const params = new URLSearchParams({ lat: String(lat), lng: String(lng), zoom: String(zoom) });
    fetch(locationReverseUrl + '?' + params.toString(), { signal: reverseAbortController.signal })
        .then(r => r.json())
        .then(data => {
            const parsed = parseReversePayload(data, lat, lng);
            reverseCache.set(key, parsed);
            updateLocationFields(parsed.mapLabel, parsed.full, lat, lng, false, updateDeliveryAddress);
        })
        .catch(error => {
            if (error && error.name === 'AbortError') {
                return;
            }
            const fallback = formatPinnedLocation(lat, lng);
            updateLocationFields(fallback, fallback, lat, lng, true, updateDeliveryAddress);
        })
        .finally(() => setMapSearchButtonLoading(false));
}

function scoreResult(result, query) {
    const name = (result.display_name || '').toLowerCase();
    const q = query.toLowerCase();
    let score = 0;

    if (name.startsWith(q)) score += 60;
    if (name.includes(q)) score += 30;
    if (/\bbarangay\b/.test(name)) score += 10;

    const center = map.getCenter();
    const lat = parseFloat(result.lat);
    const lng = parseFloat(result.lon);
    if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
        score -= Math.hypot(center.lat - lat, center.lng - lng) * 120;
    }

    return score;
}

function applySearchResult(result) {
    const lat = parseFloat(result.lat);
    const lng = parseFloat(result.lon);
    map.setView([lat, lng], 17);
    marker.setLatLng([lat, lng]);
    // Selecting a search result is considered an explicit user action
    userPinnedLocation = true;
    setUserPinnedFlag();
    reverseGeocode(lat, lng, 17, true);  // Update delivery address when applying search result
    document.getElementById('searchResults').style.display = 'none';
}

async function fetchSearchCandidates(query) {
    if (searchCache.has(query)) {
        return searchCache.get(query);
    }

    const bounds = map.getBounds();
    const params = new URLSearchParams({
        q: query,
        left: String(bounds.getWest()),
        top: String(bounds.getNorth()),
        right: String(bounds.getEast()),
        bottom: String(bounds.getSouth()),
        limit: '8'
    });

    try {
        const response = await fetch(locationSearchUrl + '?' + params.toString(), { signal: searchAbortController?.signal });
        if (!response.ok) {
            console.warn('Search API error:', response.status, response.statusText);
            return [];
        }

        const payload = await response.json();
        const results = Array.isArray(payload.results) ? payload.results : [];
        searchCache.set(query, results);
        return results;
    } catch (error) {
        if (error.name === 'AbortError') {
            return [];
        }
        console.error('Search fetch error:', error);
        return [];
    }
}

async function searchAddress(autoSelectFirst = false) {
    const query = document.getElementById('mapSearch').value.trim();
    if (query.length < 3) {
        document.getElementById('searchResults').style.display = 'none';
        return;
    }

    if (searchAbortController) {
        searchAbortController.abort();
    }
    searchAbortController = new AbortController();

    const container = document.getElementById('searchResults');
    container.innerHTML = '<div class="result-item text-muted">Searching...</div>';
    container.style.display = 'block';

    try {
        const variants = buildSearchQueries(query);
        const settled = await Promise.allSettled(variants.map(fetchSearchCandidates));
        const merged = [];

        settled.forEach(item => {
            if (item.status === 'fulfilled' && Array.isArray(item.value)) {
                merged.push(...item.value);
            }
        });

        const unique = [];
        const seen = new Set();
        merged.forEach(item => {
            const key = item.place_id || item.display_name;
            if (!seen.has(key)) {
                seen.add(key);
                unique.push(item);
            }
        });

        const ranked = unique.sort((a, b) => scoreResult(b, query) - scoreResult(a, query)).slice(0, 6);
        container.innerHTML = '';

        if (ranked.length === 0) {
            container.innerHTML = '<div class="result-item text-muted">No results found. Try adding city/municipality.</div>';
            container.style.display = 'block';
            return;
        }

        if (autoSelectFirst) {
            applySearchResult(ranked[0]);
            return;
        }

        ranked.forEach(result => {
            const item = document.createElement('div');
            item.className = 'result-item';
            item.textContent = result.display_name;
            item.addEventListener('click', function () {
                applySearchResult(result);
            });
            container.appendChild(item);
        });

        container.style.display = 'block';
    } catch (error) {
        if (error && error.name === 'AbortError') {
            return;
        }
        container.innerHTML = '<div class="result-item text-muted">Search unavailable. Please pin directly on map.</div>';
        container.style.display = 'block';
    }
}

function initMap() {
    map = L.map('checkoutMap').setView([defaultLat, defaultLng], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

    marker.on('dragend', function () {
        const pos = marker.getLatLng();
        // User dragged the pin -> mark as user-pinned to avoid auto overrides
        userPinnedLocation = true;
        setUserPinnedFlag();
        reverseGeocode(pos.lat, pos.lng, map.getZoom());
    });

    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        // User clicked on map to set pin
        userPinnedLocation = true;
        setUserPinnedFlag();
        reverseGeocode(e.latlng.lat, e.latlng.lng, map.getZoom());
    });

    map.on('zoomend', function () {
        const pos = marker.getLatLng();
        reverseGeocode(pos.lat, pos.lng, map.getZoom());
    });

    const existingLat = document.getElementById('latitude').value;
    const existingLng = document.getElementById('longitude').value;

    if (existingLat && existingLng) {
        const lat = parseFloat(existingLat);
        const lng = parseFloat(existingLng);
        map.setView([lat, lng], 16);
        marker.setLatLng([lat, lng]);
        // Treat pre-filled coordinates as user-pinned to avoid auto overrides
        userPinnedLocation = true;
        setUserPinnedFlag();
        // Don't call reverseGeocode here to preserve address field
    } else {
        // On initial load, just set default view without modifying address
        map.setView([defaultLat, defaultLng], 14);
        marker.setLatLng([defaultLat, defaultLng]);
    }
}

function useMyLocation() {
    if (!navigator.geolocation) {
        alert('Geolocation is not supported by your browser.');
        return;
    }

    navigator.geolocation.getCurrentPosition(
        function (position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            map.setView([lat, lng], 17);
            marker.setLatLng([lat, lng]);
            // Using 'Use My Location' is a deliberate user action
            userPinnedLocation = true;
            setUserPinnedFlag();
            document.getElementById('mapSearch').value = 'Getting your address...';
            reverseGeocode(lat, lng, 17, true);  // Update delivery address when using geolocation
        },
        function () {
            alert('Unable to get your location. Please allow location access.');
        },
        { enableHighAccuracy: true }
    );
}

function resetPinnedLocation() {
    map.setView([defaultLat, defaultLng], 14);
    marker.setLatLng([defaultLat, defaultLng]);
    document.getElementById('mapSearch').value = '';
    document.getElementById('searchResults').style.display = 'none';
    document.getElementById('latitude').value = '';
    document.getElementById('longitude').value = '';
    document.getElementById('addressFull').value = '';
    userPinnedLocation = false;
    setUserPinnedFlag();
}

// Geocode customer's default address when page loads
async function geocodeDefaultAddress() {
    const deliveryAddress = document.querySelector('[name="delivery_address"]').value.trim();
    
    console.log('Geocoding address:', deliveryAddress);
    
    // If latitude/longitude already exist (from previous submission), skip auto-geocoding
    const hasStoredCoordinates = document.getElementById('latitude').value && document.getElementById('longitude').value;
    // If user has already pinned a location, do not auto-geocode or reposition
    if (userPinnedLocation) {
        console.log('Skipping auto-geocode because userPinnedLocation is true');
        return;
    }
    if (hasStoredCoordinates || !deliveryAddress) {
        console.log('Skipping geocode - stored coords:', hasStoredCoordinates, 'address:', deliveryAddress);
        return;
    }

    try {
        // Build search queries for the address (includes variations and context)
        const variants = buildSearchQueries(deliveryAddress);
        console.log('Search variants:', variants);
        
        const settled = await Promise.allSettled(variants.map(fetchSearchCandidates));
        const merged = [];

        settled.forEach(item => {
            if (item.status === 'fulfilled' && Array.isArray(item.value)) {
                merged.push(...item.value);
            }
        });

        console.log('Merged results count:', merged.length);
        
        if (merged.length === 0) {
            console.log('No results found');
            return;
        }

        // Rank results
        const unique = [];
        const seen = new Set();
        merged.forEach(item => {
            const key = item.place_id || item.display_name;
            if (!seen.has(key)) {
                seen.add(key);
                unique.push(item);
            }
        });

        const ranked = unique.sort((a, b) => scoreResult(b, deliveryAddress) - scoreResult(a, deliveryAddress));
        console.log('Top result:', ranked[0]);
        
        if (ranked.length > 0) {
            const result = ranked[0];
            const lat = parseFloat(result.lat);
            const lng = parseFloat(result.lon);
            
            console.log('Positioning map to:', lat, lng);
            
            // Position map at the address
            map.setView([lat, lng], 17);
            marker.setLatLng([lat, lng]);
            
            // Set hidden coordinate fields
            document.getElementById('latitude').value = lat.toFixed(7);
            document.getElementById('longitude').value = lng.toFixed(7);
            document.getElementById('addressFull').value = result.display_name || '';
            
            console.log('Map positioned successfully');
        }
    } catch (error) {
        console.error('Auto-geocode error:', error);
    }
}

document.addEventListener('DOMContentLoaded', async function () {
    
    // Setup proof of payment file preview
    setupProofInputPreview();
    
    initMap();
    
    // Wait a bit for map to fully render, then geocode customer's default address
    setTimeout(() => {
        geocodeDefaultAddress();
    }, 500);

    const searchInput = document.getElementById('mapSearch');
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        if (this.value.trim().length >= 3) {
            searchTimeout = setTimeout(searchAddress, 400);
        } else {
            document.getElementById('searchResults').style.display = 'none';
        }
    });

    searchInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            searchAddress(true);
        }
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.map-search-wrap')) {
            document.getElementById('searchResults').style.display = 'none';
        }
    });

    const freebieOptions = document.querySelectorAll('.freebie-option');
    freebieOptions.forEach(option => {
        const radio = option.querySelector('input[type="radio"]');
        option.addEventListener('click', function () {
            freebieOptions.forEach(o => o.classList.remove('selected'));
            option.classList.add('selected');
            if (radio) {
                radio.checked = true;
            }
        });
    });

    let selectedVoucherId = null;
    let selectedVoucherDiscount = 0;

    const deliveryFee = parseFloat(document.getElementById('summaryDeliveryFee').dataset.value || '0');

    // Cart item selection handling - now includes all items by default (no checkbox selection)
    function updateSelectedItems() {
        // Get all cart items
        const allItems = document.querySelectorAll('.order-item-mini');
        const selectedIds = Array.from(allItems).map(item => item.dataset.cartId).join(',');
        document.getElementById('selectedCartIds').value = selectedIds;

        // Update totals - sum all items
        let selectedSubtotal = 0;
        allItems.forEach(item => {
            const priceText = item.dataset.price || '0';
            selectedSubtotal += parseFloat(priceText);
        });

        document.getElementById('summarySubtotal').textContent = '₱' + selectedSubtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        if (selectedVoucherDiscount > 0) {
            updateOrderSummaryWithDiscount(selectedVoucherDiscount);
        } else {
            const total = selectedSubtotal + deliveryFee;
            document.getElementById('summaryTotal').textContent = '₱' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        }
    }

    // Initialize selected items
    updateSelectedItems();

    // Form submission with validation
    const checkoutForm = document.getElementById('checkoutForm');
    const placeOrderBtn = document.getElementById('placeOrderBtn');

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function (e) {
            console.log('Form submission initiated');
            const isValid = validateCheckout();
            console.log('Checkout validation result:', isValid);

            if (!isValid) {
                e.preventDefault();
                e.stopPropagation();
                const errorDiv = document.getElementById('checkoutErrors');
                if (errorDiv) {
                    errorDiv.style.display = 'block';
                }
                return false;
            }

            setUserPinnedFlag();

            if (placeOrderBtn) {
                placeOrderBtn.disabled = true;
                placeOrderBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Placing Order...';
            }

            return true;
        });
    }

    // ===== VOUCHER & FREEBIE MANAGEMENT =====
    const originalSubtotal = parseFloat(document.getElementById('summarySubtotal').textContent.replace('₱', '').replace(/,/g, ''));

    window.applyVoucher = function(voucherId, discountAmount) {
        // Convert string parameters to numbers
        voucherId = parseInt(voucherId);
        discountAmount = parseFloat(discountAmount);
        
        const vouchersContainer = document.getElementById('vouchersContainer');
        
        // If this voucher is already applied, remove it
        if (selectedVoucherId === voucherId) {
            removeVoucher();
            return;
        }
        
        // Clear previously applied voucher UI
        document.querySelectorAll('.voucher-item').forEach(item => {
            const vid = item.dataset.voucherId;
            const badge = document.getElementById('appliedBadge-' + vid);
            const btn = item.querySelector('.voucher-apply-btn');
            if (badge) badge.style.display = 'none';
            if (btn) {
                btn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Apply Voucher';
                btn.style.background = 'var(--gasgo-orange)';
            }
        });
        
        // Set this voucher as selected
        selectedVoucherId = voucherId;
        selectedVoucherDiscount = discountAmount;
        
        // Update form field
        document.getElementById('voucherId').value = voucherId;
        
        // Update UI for this voucher
        const voucherItem = document.querySelector(`[data-voucher-id="${voucherId}"]`);
        if (voucherItem) {
            const badge = document.getElementById('appliedBadge-' + voucherId);
            const btn = voucherItem.querySelector('.voucher-apply-btn');
            
            if (badge) badge.style.display = 'block';
            if (btn) {
                btn.innerHTML = '<i class="fas fa-times-circle me-1"></i>Remove';
                btn.style.background = '#e74c3c';
            }
        }
        
        // Update order summary with discount
        updateOrderSummaryWithDiscount(discountAmount);
        
        console.log('Voucher applied: ID=' + voucherId + ', Discount=₱' + discountAmount);
    };

    window.removeVoucher = function() {
        selectedVoucherId = null;
        selectedVoucherDiscount = 0;
        
        // Clear form field
        document.getElementById('voucherId').value = '';
        
        // Reset all voucher buttons
        document.querySelectorAll('.voucher-item').forEach(item => {
            const vid = item.dataset.voucherId;
            const badge = document.getElementById('appliedBadge-' + vid);
            const btn = item.querySelector('.voucher-apply-btn');
            if (badge) badge.style.display = 'none';
            if (btn) {
                btn.innerHTML = '<i class="fas fa-check-circle me-1"></i>Apply Voucher';
                btn.style.background = 'var(--gasgo-orange)';
            }
        });
        
        // Remove discount from order summary
        updateOrderSummaryWithDiscount(0);
        
        console.log('Voucher removed');
    };

    function hideFreebiesSection() {
        const freebiesSection = document.getElementById('freebiesSection');
        const disabledNote = document.getElementById('freebieDisabledNote');
        
        if (freebiesSection) freebiesSection.style.display = 'none';
        if (disabledNote) disabledNote.style.display = 'block';
    }

    function showFreebiesSection() {
        const freebiesSection = document.getElementById('freebiesSection');
        const disabledNote = document.getElementById('freebieDisabledNote');
        
        if (freebiesSection) freebiesSection.style.display = 'block';
        if (disabledNote) disabledNote.style.display = 'none';
    }

    function clearSelectedFreebie() {
        document.querySelectorAll('.freebie-option').forEach(option => {
            option.classList.remove('selected');
            const radio = option.querySelector('input[type="radio"]');
            if (radio) radio.checked = false;
        });
        const inputField = document.querySelector('input[name="selected_freebie_id"]');
        if (inputField) inputField.value = '';
    }

    function updateOrderSummaryWithDiscount(discountAmount) {
        const subtotalText = document.getElementById('summarySubtotal').textContent;
        const subtotal = parseFloat(subtotalText.replace('₱', '').replace(/,/g, ''));
        
        const total = subtotal + deliveryFee - discountAmount;
        
        const discountRow = document.getElementById('discountSummaryRow');
        
        if (discountAmount > 0) {
            // Show discount row
            if (discountRow) discountRow.style.display = 'flex';
            document.getElementById('discountAmount').textContent = '-₱' + discountAmount.toFixed(2);
        } else {
            // Hide discount row
            if (discountRow) discountRow.style.display = 'none';
        }
        
        // Update total
        document.getElementById('summaryTotal').textContent = '₱' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    // Prevent freebie selection when voucher is applied
    document.querySelectorAll('.freebie-option').forEach(option => {
        const radio = option.querySelector('input[type="radio"]');
        if (radio) {
            radio.addEventListener('change', function() {
                if (selectedVoucherId !== null) {
                    alert('Please remove the applied voucher first to select a freebie.');
                    this.checked = false;
                    option.classList.remove('selected');
                }
            });
        }
    });

        // Payment method selection - handle clicks on payment options
        document.querySelectorAll('.payment-option').forEach(option => {
            option.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                if (radio && radio.disabled) {
                    return;
                }

                const method = this.dataset.paymentMethod;
                selectPayment(this, method);
            });

            const radio = option.querySelector('input[type="radio"]');
            if (radio) {
                radio.addEventListener('change', function() {
                    if (this.disabled) {
                        return;
                    }

                    selectPayment(option, this.value);
                });
            }
        });

        const initialSelectedPayment = document.querySelector('.payment-option.selected');
        if (initialSelectedPayment) {
            selectPayment(initialSelectedPayment, initialSelectedPayment.dataset.paymentMethod);
        }

        // Map search button
        const mapSearchBtn = document.getElementById('mapSearchBtn');
        if (mapSearchBtn) {
            mapSearchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                searchAddress();
            });
        }

        // Use my location button
            const useLocationBtn = document.getElementById('useMyLocationBtn');
        if (useLocationBtn) {
            useLocationBtn.addEventListener('click', function(e) {
                e.preventDefault();
                useMyLocation();
            });
        }

            // Voucher apply buttons - event delegation
            document.addEventListener('click', function(e) {
                if (e.target.closest('.voucher-apply-btn')) {
                    e.preventDefault();
                    const btn = e.target.closest('.voucher-apply-btn');
                    const voucherId = btn.dataset.voucherId;
                    const discount = btn.dataset.discount;
                    if (voucherId && discount) {
                        applyVoucher(voucherId, discount);
                    }
                }
            });
});
</script>
@endpush

