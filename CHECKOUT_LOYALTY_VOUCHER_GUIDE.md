# Checkout Page - Loyalty Milestone & Voucher Integration Guide

## A) UPDATED CHECKOUT PAGE LAYOUT (Top-to-Bottom)

```
1. ✓ Delivery Address Section (existing)
   
2. ✓ Payment Method Section (existing)

3. [NEW] MILESTONE BANNER (Conditional - appears if customer is 1-2 orders away from milestone)
   - Positioned right after Payment Method, before Vouchers
   - Full-width banner with orange gradient background
   
4. [NEW] AVAILABLE VOUCHERS SECTION (Conditional - appears if customer has 1+ available vouchers)
   - Positioned after Milestone Banner (or Payment if no milestone)
   - Before Freebie section
   - "Apply Voucher" button adjacent to each voucher
   
5. ✓ SELECT YOUR FREEBIE Section (existing - conditional on availability)
   - [MODIFIED] Hide section when voucher is applied
   - Show/hide handled by JavaScript
   
6. ✓ Order Summary Sidebar (sticky right panel)
   - [MODIFIED] Add line item: "Voucher Discount: -₱XX" (when applied)
   - [MODIFIED] Update total calculation to subtract discount
```

---

## B) UI TEXT/COPY & BANNER COPY

### MILESTONE BANNER - When Customer is Near Milestone:

**Heading:** "(Recommended placement for heading inside banner)**
```
"🎉 Almost There! Complete Your Next Milestone"
```

**Body Text:**
```
"This order will complete your [10/20/30]th delivered order once delivered. 
Your ₱[30/50/100] OFF voucher will be issued immediately after delivery!
Make sure your delivery is completed to unlock this reward."
```

**Styling:**
- Background: Linear gradient (var(--gasgo-orange) to #ff6b35)
- Text color: White
- Left border: 4px solid rgba(255,255,255,0.3)
- Padding: 24px
- Border-radius: 16px
- Icon: 🎉 or <i class="fas fa-trophy"></i>

---

### AVAILABLE VOUCHERS SECTION HEADING:

```
"💳 Your Available Vouchers"
```

**Helper text (optional):**
```
"Select a voucher to apply a discount to your order"
```

---

### INDIVIDUAL VOUCHER ITEM CARD:

**Card Layout:**
```
[Discount Icon - ₱] | ₱30 OFF Voucher      | [Apply Button]
                    | Expires: 28 Apr 2026  | (or "Remove" if applied)
```

**Copy - Voucher Title:**
```
"₱{amount} OFF Voucher"
```

**Copy - Expiry Note:**
```
"Expires: {date}" or "Expires in {days} days"
```

**Copy - Apply Button:**
```
"Apply Voucher"   (default state)
"Remove"          (when this specific voucher is applied)
```

**Copy - Selection Indicator:**
```
"✓ Applied"  (badge shown on applied voucher)
```

---

### FREEBIE SECTION - HELPER NOTE (when voucher is applied):

**Position:** Below the "Select Your Freebie" heading, in an alert box

**Copy:**
```
"ℹ️ Freebies cannot be combined with vouchers. 
   Your voucher discount will be applied to the order total instead."
```

**Styling:**
- Background: rgba(247, 148, 29, 0.1)  
- Border-left: 4px solid var(--gasgo-orange)
- Text color: #666
- Font-size: 0.9rem
- Padding: 16px

---

## C) CONDITIONAL LOGIC - PSEUDOCODE

### Case 1: Customer has 9 delivered orders, no available voucher yet
```
if (completed_orders == 9 OR completed_orders == 19 OR completed_orders == 29) {
    // Show milestone banner
    nextMilestone = completed_orders + 1
    nextRewardAmount = getRewardAmount(nextMilestone)  // ₱30, ₱50, or ₱100
    showMilestoneBanner(nextMilestone, nextRewardAmount)
    
    // Show freebies section
    showFreebiesSection()
    hideVoucherSection()
    hideFreebieDisabledNote()
}
```

### Case 2: Customer has 1+ available vouchers, voucher NOT applied
```
if (availableVouchers.count > 0 AND selectedVoucherId == null) {
    showVoucherSection()
    showFreebiesSection()
    hideFreebieDisabledNote()
    hideMilestoneBanner()
    
    // Render each available voucher card with Apply button
    availableVouchers.forEach(voucher => {
        renderVoucherCard(voucher, isApplied=false)
        // Button text: "Apply Voucher"
        // Button action: applyVoucher(voucher.id)
    })
}
```

### Case 3: Voucher is applied
```
if (selectedVoucherId != null) {
    selectedVoucher = getVoucher(selectedVoucherId)
    
    // Hide freebies section completely
    hideFreebiesSection()
    
    // Show freebie disabled note
    showFreebieDisabledNote()
    
    // Clear any pre-selected freebie
    clearSelectedFreebie()
    
    // Remove freebie from POST data
    document.getElementById('selected_freebie_id').value = ''
    
    // Show the selected voucher with "Remove" button
    showVoucherSection()
    renderVoucherCard(selectedVoucher, isApplied=true)
    // Button text: "Remove"
    // Button action: removeVoucher()
    
    // Apply discount to order total
    discountAmount = selectedVoucher.discount_amount
    newTotal = subtotal - discountAmount
    updateOrderSummary(newTotal, discountAmount)
}
```

### Case 4: Voucher removed (user clicks Remove button)
```
if (removeVoucherClicked) {
    selectedVoucherId = null
    
    // Show freebies section again
    showFreebiesSection()
    hideFreebieDisabledNote()
    
    // Remove from form
    document.getElementById('voucher_id').value = ''
    
    // Revert order summary
    discountAmount = 0
    newTotal = subtotal
    updateOrderSummary(newTotal, discountAmount)
    
    // Show all available vouchers again for re-selection
    showVoucherSection()
    availableVouchers.forEach(voucher => {
        renderVoucherCard(voucher, isApplied=false)
    })
}
```

---

## D) BACKEND - VOUCHER ISSUANCE EVENT

### Trigger: Order Status Changed to "Delivered"

**Location:** `app/Jobs/MarkOrderAsDelivered.php` or `OrderObserver::updated()`

**Logic:**

```php
// In your Observer or Job that handles order status update to "delivered"

public function whenOrderMarkedDelivered(Order $order) {
    $user = $order->user;
    
    // Count delivered orders (excluding this one if already marked delivered)
    $deliveredCount = Order::where('user_id', $user->id)
        ->where('status', 'delivered')  
        ->count();
    
    // Define milestone thresholds
    $milestones = [
        10 => ['reward_id' => 1, 'discount' => 30],
        20 => ['reward_id' => 2, 'discount' => 50],
        30 => ['reward_id' => 3, 'discount' => 100],
    ];
    
    // Check if this order pushes user to a milestone
    foreach ($milestones as $threshold => $rewardData) {
        if ($deliveredCount == $threshold) {
            // IDEMPOTENT: Prevent duplicate voucher creation
            $existingVoucher = UserVoucher::where('user_id', $user->id)
                ->where('reward_id', $rewardData['reward_id'])
                ->where('order_id', $order->id)
                ->first();
            
            if (!$existingVoucher) {
                // Create voucher
                UserVoucher::create([
                    'user_id' => $user->id,
                    'reward_id' => $rewardData['reward_id'],
                    'voucher_name' => '₱' . $rewardData['discount'] . ' OFF Voucher',
                    'discount_amount' => $rewardData['discount'],
                    'description' => 'Earned at ' . $deliveredCount . ' delivered orders',
                    'order_id' => $order->id,
                    'is_used' => false,
                    'expires_at' => now()->addDays(30),  // Valid for 30 days
                    'issued_at' => now(),
                ]);
                
                // Optional: Send customer notification
                $user->notify(new VoucherIssuedNotification($rewardData['discount']));
            }
            break;
        }
    }
}
```

**Database Fields (UserVoucher table):**
```sql
- id
- user_id (FK)
- reward_id (FK) - links to Reward model
- voucher_name (string) - "₱30 OFF Voucher", etc
- discount_amount (decimal) - 30.00, 50.00, 100.00
- description (text)
- order_id (FK) - order that triggered the milestone
- is_used (boolean) - false until applied to checkout
- expires_at (timestamp) - delivery_date + 30 days
- issued_at (timestamp) - when voucher was created
- created_at, updated_at
```

---

## E) ORDER SUMMARY - DISCOUNT DISPLAY

### Order Summary Line Items (sticky right panel):

**Current StateStructure:**
```
[Line] Subtotal          ₱X,XXX.00
[Line] Voucher Discount   -₱30.00    ← [NEW] Only shown when applied
─────────────────────────────────────
[Bold] Total             ₱X,XXX.00   ← Updated to reflect discount
```

**JavaScript to Update Summary:**

```javascript
function updateOrderSummary(subtotal, discountAmount = 0) {
    const total = subtotal - discountAmount;
    
    document.getElementById('summarySubtotal').textContent = '₱' + 
        parseFloat(subtotal).toFixed(2);
    
    // If discount, create/show discount line item
    let discountRow = document.getElementById('discountSummaryRow');
    
    if (discountAmount > 0) {
        if (!discountRow) {
            // Create discount row if it doesn't exist
            const summaryDiv = document.querySelector('.order-summary');
            const subtotalRow = summaryDiv.querySelector('.summary-item:not(.total)');
            
            discountRow = document.createElement('div');
            discountRow.id = 'discountSummaryRow';
            discountRow.className = 'summary-item';
            discountRow.style.color = '#27ae60'; // Green for discount
            discountRow.innerHTML = `
                <span><i class="fas fa-tag me-1" style="color:var(--gasgo-orange);"></i>Voucher Discount</span>
                <span id="discountAmount">-₱${discountAmount.toFixed(2)}</span>
            `;
            subtotalRow.parentNode.insertBefore(discountRow, subtotalRow.nextSibling);
        } else {
            // Update existing discount row
            document.getElementById('discountAmount').textContent = 
                '-₱' + discountAmount.toFixed(2);
        }
    } else {
        // Remove discount row if no discount
        if (discountRow) {
            discountRow.remove();
        }
    }
    
    // Update total
    document.getElementById('summaryTotal').textContent = '₱' + 
        total.toFixed(2);
}
```

---

## F) FORM FIELD UPDATES

### Add to Checkout Form:

```html
<!-- Hidden field for selected voucher -->
<input type="hidden" name="voucher_id" id="voucherId" value="">

<!-- This should already exist for freebie -->
<input type="hidden" name="selected_freebie_id" id="selectedFreebieId" value="">
```

### In Controller (checkout store action):

```php
public function store(Request $request) {
    $validated = $request->validate([
        'delivery_address' => 'required|string',
        'contact_number' => 'required|string',
        'payment_method' => 'required|in:cash,gcash',
        'voucher_id' => 'nullable|exists:user_vouchers,id',
        'selected_freebie_id' => 'nullable|exists:freebies,id',
        'selected_cart_ids' => 'required|array',
        // ... other validations
    ]);
    
    // If both voucher AND freebie are selected, reject freebie
    if ($validated['voucher_id'] && $validated['selected_freebie_id']) {
        $validated['selected_freebie_id'] = null;
    }
    
    // Proceed with order creation
    // ...
}
```

---

## G) STYLING CONSISTENCY

### Color Scheme:
- Primary: `var(--gasgo-blue)` (#0f3460)
- Accent: `var(--gasgo-orange)` (#f7941d)
- Success/Green: #27ae60
- Background: #f8f9fa
- Card backgrounds: white

### Rounded Corners:
- Large cards: 20px (existing)
- Buttons: 8-10px
- Badges: 25px (pill-shaped)

### Font Weights:
- Headings (h5, h6): 700
- Labels: 600
- Body text: 400

---

## H) IMPLEMENTATION CHECKLIST

Backend:
- [ ] Add `voucher_id` field to Order model (nullable FK)
- [ ] Add `Order::getAvailableVouchersForCheckout()` method
- [ ] Add `Order::getClosestMilestone()` method returning next milestone info
- [ ] Create OrderObserver or Job to issue vouchers when order marked Delivered
- [ ] Update Order creation to handle `voucher_id` and `selected_freebie_id` (mutually exclusive)
- [ ] Create migration for UserVoucher table (if not exists)

Frontend (checkout.blade.php):
- [ ] Update controller to pass `availableVouchers` and `milestoneInfo` to view
- [ ] Add Milestone Banner section (with @if conditional)
- [ ] Add Available Vouchers section (with @foreach for vouchers)
- [ ] Add Freebie Disabled Note (with display hidden by default)
- [ ] Update Order Summary to render discount line item dynamically
- [ ] Add form fields: `voucher_id` and clear `selected_freebie_id` when voucher applied

JavaScript:
- [ ] Add `applyVoucher(voucherId)` function
- [ ] Add `removeVoucher()` function  
- [ ] Add `toggleFreebieSection()` function
- [ ] Update `updateOrderSummary()` to handle discount display

---

## I) TESTING SCENARIOS

1. **Guest User**: No milestone banner, no voucher section ✓
2. **User with 8 delivered orders**: Show milestone banner "Almost at 10" ✓
3. **User with 10 delivered orders (but voucher not yet issued)**: Show milestone banner for next tier (20) ✓
4. **User with 1 available voucher**: Show voucher selector, show freebies, can apply ✓
5. **User applies voucher**: Hide freebies, show discount in summary, show "Remove" button ✓
6. **User removes voucher**: Show freebies again, remove discount from summary ✓
7. **User tries to select freebie + voucher**: Backend rejects, frontend prevents both selectable ✓
8. **Order placed with voucher**: Voucher marked as used, discount applied to order ✓
9. **Order delivered, reaches milestone**: New voucher created with 30-day expiry ✓

