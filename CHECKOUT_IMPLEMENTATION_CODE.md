# Checkout Page - Implementation Code Snippets

## FILE: resources/views/customer/checkout.blade.php

### ADD AFTER Payment Method Section (around line 270):

```blade
<!-- ===== LOYALTY MILESTONE BANNER ===== -->
@php
    $milestoneInfo = $milestoneInfo ?? null;
@endphp
@if ($milestoneInfo && $milestoneInfo['isClosestToMilestone'])
<div class="checkout-card" style="background: linear-gradient(135deg, var(--gasgo-orange) 0%, #ff6b35 100%); color: white; border: none;">
    <div style="display: flex; gap: 16px; align-items: center;">
        <i class="fas fa-trophy" style="font-size: 2rem; opacity: 0.9;"></i>
        <div>
            <h5 style="color: white; margin-bottom: 8px; font-weight: 800;">
                <i class="fas fa-star me-2"></i>Almost There! Complete Your Next Milestone
            </h5>
            <p style="margin-bottom: 0; opacity: 0.95; font-size: 0.95rem;">
                This order will complete your <strong>{{ $milestoneInfo['nextMilestone'] }}th delivered order</strong> once delivered. 
                Your <strong>₱{{ $milestoneInfo['nextRewardAmount'] }} OFF voucher</strong> will be issued immediately after delivery!
            </p>
            <small style="display: block; margin-top: 8px; opacity: 0.85;">
                📌 Make sure your delivery is completed to unlock this reward.
            </small>
        </div>
    </div>
</div>
@endif

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
        <div class="voucher-item mb-3" data-voucher-id="{{ $voucher->id }}" data-discount="{{ $voucher->discount_amount }}">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div style="flex: 1;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div style="font-weight: 800; color: var(--gasgo-orange); font-size: 1.8rem;">
                            ₱{{ (int) $voucher->discount_amount }}
                        </div>
                        <div>
                            <div style="font-weight: 700; color: var(--gasgo-blue);">OFF Voucher</div>
                            <small style="color: #888; font-size: 0.8rem;">
                                Expires in <strong>{{ $voucher->isDaysUntilExpiry() }}</strong> day{{ $voucher->isDaysUntilExpiry() === 1 ? '' : 's' }}
                            </small>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn voucher-apply-btn" 
                        style="background: var(--gasgo-orange); color: white; border: none; border-radius: 8px; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.3s ease;"
                        onclick="applyVoucher({{ $voucher->id }}, {{ $voucher->discount_amount }})">
                    <i class="fas fa-check-circle me-1"></i>Apply Voucher
                </button>
            </div>
            <div id="appliedBadge-{{ $voucher->id }}" style="display: none; color: #27ae60; font-size: 0.8rem; font-weight: 600;">
                <i class="fas fa-check-circle me-1"></i>✓ Applied
            </div>
        </div>
        @endforeach
    </div>

    <input type="hidden" name="voucher_id" id="voucherId" value="">
</div>
@endif

<!-- ===== FREEBIE SECTION - WITH DISABLED NOTE ===== -->
@if ($smallRewardCount > 0)
    <!-- Freebie Warning (shown when voucher is applied) -->
    <div id="freebieDisabledNote" style="display: none; background: rgba(247, 148, 29, 0.1); border-left: 4px solid var(--gasgo-orange); padding: 16px; border-radius: 8px; margin-bottom: 24px;">
        <div style="display: flex; gap: 12px; align-items: flex-start;">
            <i class="fas fa-info-circle" style="color: var(--gasgo-orange); margin-top: 2px; flex-shrink: 0;"></i>
            <div>
                <strong style="color: var(--gasgo-blue); display: block; margin-bottom: 4px;">Freebies Not Available</strong>
                <p style="margin: 0; font-size: 0.9rem; color: #666;">
                    Freebies cannot be combined with vouchers. Your voucher discount will be applied to the order total instead.
                </p>
            </div>
        </div>
    </div>

    <div id="freebiesSection" class="checkout-card">
        <h5><i class="fas fa-gift"></i>Select Your Freebie</h5>
        <p class="text-muted mb-3" style="font-size:.88rem;">
            You can choose <strong>1 freebie item</strong> from below at no extra cost!
        </p>

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
                    @endphp
                    <div class="col-lg-4 col-md-6">
                        <label class="freebie-option {{ (string) old('selected_freebie_id') === (string) $freebie->id ? 'selected' : '' }}" id="freebie-{{ $freebie->id }}">
                            <input
                                type="radio"
                                name="selected_freebie_id"
                                value="{{ $freebie->id }}"
                                class="freebie-radio"
                                {{ (string) old('selected_freebie_id') === (string) $freebie->id ? 'checked' : '' }}
                            >
                            @if($freebieImageUrl)
                                <div class="freebie-image-wrapper">
                                    <img src="{{ $freebieImageUrl }}" alt="{{ $freebie->name }}">
                                </div>
                            @endif
                            <div class="freebie-title">{{ $freebie->name }}</div>
                            <div class="freebie-desc">{{ $freebie->description ?: 'Complimentary reward item' }}</div>
                            <div class="freebie-stock">{{ $freebie->stock }} available</div>
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endif
```

---

## FILE: resources/views/customer/checkout.blade.php

### MODIFY ORDER SUMMARY SECTION (around line 360):

```blade
<!-- Order Summary Sidebar -->
<div class="col-lg-4">
    <div class="order-summary">
        <h5><i class="fas fa-receipt me-2"></i>Order Summary</h5>
        <p class="text-muted" style="font-size:.85rem; margin-bottom:12px;">Select items to include in your order:</p>
        
        @foreach ($cartItems as $item)
        <div class="order-item-mini" data-cart-id="{{ $item->id }}" data-price="{{ $item->product->price * $item->quantity }}">
            <input 
                type="checkbox" 
                class="cart-item-checkbox" 
                value="{{ $item->id }}" 
                checked
                data-price="{{ $item->product->price * $item->quantity }}"
            >
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
        
        <!-- NEW: Voucher Discount Line Item (shown conditionally) -->
        <div id="discountSummaryRow" class="summary-item" style="display: none; color: #27ae60;">
            <span><i class="fas fa-tag me-1" style="color:var(--gasgo-orange);"></i>Voucher Discount</span>
            <span id="discountAmount" style="font-weight: 700; color: #27ae60; font-size: 1rem;">-₱0.00</span>
        </div>
        
        <div class="summary-item total"><span>Total</span><span class="val" id="summaryTotal">₱{{ number_format($subtotal, 2) }}</span></div>
        
        <input type="hidden" id="selectedCartIds" name="selected_cart_ids" value="">
        
        <button type="submit" class="btn btn-gasgo w-100 mt-3">
            <i class="fas fa-check-circle me-2"></i>Place Order
        </button>
        <a href="{{ route('customer.cart') }}" class="btn btn-gasgo-outline w-100 mt-2" style="padding:12px;">
            <i class="fas fa-arrow-left me-2"></i>Back to Cart
        </a>
    </div>
</div>
```

---

## FILE: resources/views/customer/checkout.blade.php

### ADD TO SCRIPTS SECTION (at end, before closing @push):

```javascript
<!-- ===== VOUCHER & FREEBIE MANAGEMENT ===== -->
<script>
let selectedVoucherId = null;
let selectedVoucherDiscount = 0;

function applyVoucher(voucherId, discountAmount) {
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
    
    // Hide freebies section
    hideFreebiesSection();
    
    // Update order summary with discount
    updateOrderSummaryWithDiscount(discountAmount);
    
    // Clear selected freebie
    clearSelectedFreebie();
    
    console.log('Voucher applied: ID=' + voucherId + ', Discount=₱' + discountAmount);
}

function removeVoucher() {
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
    
    // Show freebies section again
    showFreebiesSection();
    
    // Remove discount from order summary
    updateOrderSummaryWithDiscount(0);
    
    console.log('Voucher removed');
}

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
    document.querySelectorAll('.freebie-radio').forEach(radio => {
        radio.checked = false;
    });
    document.querySelectorAll('.freebie-option').forEach(option => {
        option.classList.remove('selected');
    });
    document.getElementById('selected_freebie_id').value = '';
}

function updateOrderSummaryWithDiscount(discountAmount) {
    const subtotalText = document.getElementById('summarySubtotal').textContent;
    const subtotal = parseFloat(subtotalText.replace('₱', '').replace(',', ''));
    
    const total = subtotal - discountAmount;
    
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
    document.getElementById('summaryTotal').textContent = '₱' + total.toFixed(2);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Set up freebie radio change listener to prevent selection when voucher applied
    document.querySelectorAll('.freebie-radio').forEach(radio => {
        radio.addEventListener('change', function() {
            if (selectedVoucherId !== null) {
                alert('Please remove the applied voucher first to select a freebie.');
                this.checked = false;
            }
        });
    });
});
</script>
```

---

## FILE: app/Http/Controllers/CheckoutController.php

### ADD TO INDEX METHOD:

```php
public function index()
{
    $user = Auth::user();
    $cartItems = Cart::where('user_id', $user->id)->with('product')->get();
    
    // ... existing code ...
    
    // Get available vouchers for this user
    $availableVouchers = UserVoucher::where('user_id', $user->id)
        ->where('is_used', false)
        ->where('expires_at', '>', now())
        ->with('reward')
        ->get();
    
    // Get milestone information
    $deliveredOrdersCount = Order::where('user_id', $user->id)
        ->where('status', 'delivered')
        ->count();
    
    $milestoneInfo = $this->calculateMilestoneInfo($deliveredOrdersCount);
    
    return view('customer.checkout', [
        'cartItems' => $cartItems,
        'subtotal' => $subtotal,
        'smallRewardCount' => $rewardCount,
        'availableFreebies' => $freebies,
        'availableVouchers' => $availableVouchers,
        'milestoneInfo' => $milestoneInfo,
        // ... other existing variables ...
    ]);
}

private function calculateMilestoneInfo($deliveredCount)
{
    $milestones = [10, 20, 30];
    $rewards = [10 => 30, 20 => 50, 30 => 100];
    
    foreach ($milestones as $milestone) {
        if ($deliveredCount == $milestone - 1) {
            // Customer is 1 order away from milestone
            return [
                'isClosestToMilestone' => true,
                'nextMilestone' => $milestone,
                'nextRewardAmount' => $rewards[$milestone],
                'ordersRemaining' => 1,
            ];
        }
    }
    
    return ['isClosestToMilestone' => false];
}
```

### ADD TO STORE METHOD:

```php
public function store(Request $request)
{
    $user = Auth::user();
    
    $validated = $request->validate([
        'delivery_address' => 'required|string',
        'contact_number' => 'required|string|regex:/^(\+63|0)[0-9]{10}$/',
        'payment_method' => 'required|in:cash,gcash',
        'selected_cart_ids' => 'required|array',
        'selected_freebie_id' => 'nullable|exists:freebies,id',
        'voucher_id' => 'nullable|exists:user_vouchers,id',
        'latitude' => 'nullable|numeric',
        'longitude' => 'nullable|numeric',
        'proof_of_payment' => 'required_if:payment_method,gcash|image|max:5120',
        // ... other validations ...
    ]);
    
    // Mutually exclusive: freebie XOR voucher
    if ($validated['voucher_id'] && $validated['selected_freebie_id']) {
        // Prefer voucher, clear freebie
        $validated['selected_freebie_id'] = null;
    }
    
    // Create order
    $order = Order::create([
        'user_id' => $user->id,
        'delivery_address' => $validated['delivery_address'],
        'contact_number' => $validated['contact_number'],
        'payment_method' => $validated['payment_method'],
        'freebie_id' => $validated['selected_freebie_id'],
        'voucher_id' => $validated['voucher_id'],
        // ... other fields ...
    ]);
    
    // If voucher was used, mark it as used
    if ($validated['voucher_id']) {
        UserVoucher::find($validated['voucher_id'])->update(['is_used' => true]);
    }
    
    return redirect()->route('customer.order-confirmation', $order->id);
}
```

---

## FILE: app/Models/Order.php

### ADD TO ORDER MODEL:

```php
public function voucher()
{
    return $this->belongsTo(UserVoucher::class, 'voucher_id');
}

public function freebie()
{
    return $this->belongsTo(Freebie::class, 'freebie_id');
}
```

---

## FILE: app/Observers/OrderObserver.php (NEW FILE if doesn't exist)

### ADD VOUCHER ISSUANCE LOGIC:

```php
<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\UserVoucher;
use App\Notifications\VoucherIssuedNotification;

class OrderObserver
{
    public function updated(Order $order)
    {
        // When order status changes to 'delivered', check for milestone rewards
        if ($order->wasChanged('status') && $order->status === 'delivered') {
            $this->issueVoucherIfMilestoneReached($order);
        }
    }

    private function issueVoucherIfMilestoneReached(Order $order)
    {
        $user = $order->user;
        
        // Count all delivered orders for this user
        $deliveredCount = Order::where('user_id', $user->id)
            ->where('status', 'delivered')
            ->count();
        
        // Define milestones
        $milestones = [
            10 => ['reward_id' => 1, 'discount' => 30],
            20 => ['reward_id' => 2, 'discount' => 50],
            30 => ['reward_id' => 3, 'discount' => 100],
        ];
        
        // Check if any milestone was reached
        foreach ($milestones as $threshold => $rewardData) {
            if ($deliveredCount == $threshold) {
                // IDEMPOTENT: Check if voucher already issued for this milestone
                $existingVoucher = UserVoucher::where('user_id', $user->id)
                    ->where('reward_id', $rewardData['reward_id'])
                    ->where('order_id', $order->id)
                    ->first();
                
                if (!$existingVoucher) {
                    // Create the voucher
                    $voucher = UserVoucher::create([
                        'user_id' => $user->id,
                        'reward_id' => $rewardData['reward_id'],
                        'voucher_name' => '₱' . $rewardData['discount'] . ' OFF Voucher',
                        'discount_amount' => $rewardData['discount'],
                        'description' => 'Earned at ' . $deliveredCount . ' delivered orders',
                        'order_id' => $order->id,
                        'is_used' => false,
                        'expires_at' => now()->addDays(30),
                        'issued_at' => now(),
                    ]);
                    
                    // Optional: Send notification to customer
                    try {
                        $user->notify(new VoucherIssuedNotification($rewardData['discount'], $voucher));
                    } catch (\Exception $e) {
                        // Log notification failure silently
                        \Log::warning('Failed to send voucher notification for user ' . $user->id);
                    }
                }
                
                break;
            }
        }
    }
}
```

### REGISTER OBSERVER IN AppServiceProvider:

```php
public function boot()
{
    Order::observe(\App\Observers\OrderObserver::class);
}
```

---

## FILE: database/migrations/[timestamp]_add_voucher_fields_to_orders_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('voucher_id')->nullable()->constrained('user_vouchers')->nullOnDelete();
            $table->foreignId('freebie_id')->nullable()->constrained('freebies')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeignIdFor('user_vouchers');
            $table->dropForeignIdFor('freebies');
        });
    }
};
```

