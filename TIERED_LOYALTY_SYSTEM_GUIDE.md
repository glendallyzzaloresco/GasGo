# Tiered Loyalty System Implementation Guide

## Overview
This document outlines the implementation of a tiered loyalty rewards system for GasGo LPG orders.

---

## What's New

### Tier A: Bulk Orders (≥ 10 Items)
- **Trigger**: Customer orders 10 or more LPG tanks
- **Reward**: 1 Free LPG Tank automatically added to order
- **Display**: Shows with "FREE" badge in order summary
- **Admin Notification**: ✅ REWARD INCLUDED flag

### Tier B: Small Orders (1-9 Items)
- **Trigger**: Customer orders between 1 and 9 LPG tanks
- **Reward**: 1 Free Small Freebie (randomly Dish Washer Paste OR Cloth Hanger Set)
- **Display**: Shows with "FREE" badge in order summary
- **Admin Notification**: ✅ REWARD INCLUDED flag

---

## Database Changes

### Migration: `2026_03_16_000001_add_is_reward_to_order_items_table.php`

Added column to `order_items` table:
- **is_reward** (boolean, default: false) - Marks items as loyalty rewards

```bash
# Run migration
php artisan migrate
```

### New Seeded Products

Three new products were added to the database:
1. **Free LPG Tank (Reward)** - Product ID: 6
   - Price: ₱0.00
   - Description: Complimentary LPG Tank for bulk orders
   - Stock: 999 (unlimited)

2. **Dish Washer Paste (Freebie)** - Product ID: 7
   - Price: ₱0.00
   - Description: Free small freebie for small orders
   - Stock: 999 (unlimited)

3. **Cloth Hanger Set (Freebie)** - Product ID: 8
   - Price: ₱0.00
   - Description: Free small freebie for small orders
   - Stock: 999 (unlimited)

```bash
# Seed new products
php artisan db:seed
```

---

## Model Updates

### OrderItem Model (`app/Models/OrderItem.php`)
- Added `is_reward` to $fillable array
- Added `is_reward` to casts (as boolean)

### Order Model
- No changes required (relationships already configured)

---

## Controller Updates

### OrderController (`app/Http/Controllers/OrderController.php`)

#### `checkout()` Method
- Updated reward preview to show tiered system logic
- Bulk rewards: Shows "1 Free LPG Tank"
- Small rewards: Shows "1 Free Freebie (Paste or Hanger)"

#### `store()` Method
- Implemented tiered loyalty logic:
  - **If quantity >= 10**: Add free LPG tank (Product ID 6)
  - **If quantity 1-9**: Randomly alternate between freebie products (IDs 7 or 8)
- All reward items marked with `is_reward = true`
- Admin notification triggered for orders with rewards
- Stock deduction only applies to paid items (not rewards)

---

## Notification System

### OrderPlacedNotification (`app/Notifications/OrderPlacedNotification.php`)

Sends email and database notification to admin when order is placed:
- Displays order details
- Flags orders with rewards: "⭐ **REWARD INCLUDED** - Pack the freebie with this order!"
- Includes action link to view order

**Trigger**: Automatically sent when order has reward items

```php
// Example usage in OrderController:
if ($hasRewardItems) {
    $adminUser = \App\Models\User::where('role', 'admin')->first();
    if ($adminUser) {
        $adminUser->notify(new \App\Notifications\OrderPlacedNotification($order, true));
    }
}
```

---

## View Updates

### Customer Order Views (`resources/views/customer/orders.blade.php`)

#### Reward Item Display
- Reward items show with green background highlighting
- "FREE" badge with gift icon for reward items
- Shows price as ₱0.00 for rewards
- Distinguishes reward items from paid items

#### Reorder Logic
- Reorder button excludes reward items
- Only paid items can be reordered

### Admin Dashboard (`resources/views/admin/orders.blade.php`)

#### New "Rewards" Column
- Shows "REWARD INCLUDED" badge for orders with rewards
- Lists all reward items included in the order
- Green badge for easy visual identification
- Helps fulfillment team pack correct freebies

---

## How It Works - Flow Diagram

```
Customer Adds Items to Cart
        ↓
Customer Proceeds to Checkout
        ↓
Check Order Quantity:
  ├─ If >= 10: Show "1 Free LPG Tank" preview
  ├─ If 1-9: Show "1 Free Freebie" preview
  └─ If 0: Empty cart
        ↓
Customer Places Order
        ↓
OrderController.store() executes:
  1. Validate items and stock
  2. Create Order record
  3. Add paid items to order_items (is_reward = false)
  4. Add reward item to order_items (is_reward = true)
  5. Deduct stock only for paid items
  6. Clear customer cart
  7. Send admin notification
        ↓
Order Confirmation:
  ├─ Customer sees order with FREE badge on rewards
  └─ Admin sees REWARD INCLUDED flag and notification
```

---

## Admin Features

### Admin Dashboard
- New "Rewards" column in orders table
- Quick visual identification via green "REWARD INCLUDED" badge
- Reward item details visible on hover

### Admin Notification
- Email notification with order details
- Database notification visible in admin panel
- Subject line includes "[REWARD INCLUDED]" flag
- Includes action button to view order

### Fulfillment Workflow
1. Admin views order with "REWARD INCLUDED" badge
2. Sees which specific reward items to pack
3. Includes freebie with shipment
4. Customer receives order with surprise gift

---

## Customer Experience

### Checkout Page
- Displays reward preview before placing order
- Shows "You'll receive: 1 Free LPG Tank" (or freebie)
- Encourages bulk ordering

### Order Confirmation
- Email shows complete order including rewards
- Shows "FREE" badge on reward items

### My Orders Page
- Reward items highlighted with green background
- "FREE" badge clearly visible
- Quantity shows "x1" with "₱0.00"
- Enhances perceived value

---

## Configuration

### Reward Product IDs
The system uses hardcoded product IDs. If you change the products or order of seeding:

**Update in `OrderController@store()`:**
```php
// Tier A - Free LPG Tank
$rewardItem = [
    'product_id' => 6,  // ← Update if needed
    ...
];

// Tier B - Freebies
$freebieId = (rand(1, 2) === 1) ? 7 : 8;  // ← Update if needed
```

### Tier Thresholds
To modify tier triggers, edit `OrderController@store()`:

```php
if ($quantity >= 10) {              // ← Change bulk threshold
    // Bulk tier logic
} elseif ($quantity >= 1 && $quantity <= 9) {  // ← Change small threshold
    // Small tier logic
}
```

---

## Testing Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Seed products: `php artisan db:seed`
- [ ] Add 10+ items to cart → Check "Free LPG Tank" preview
- [ ] Add 1-9 items to cart → Check "Free Freebie" preview
- [ ] Place order with 10+ items → Verify reward in order
- [ ] Check admin dashboard → Verify "REWARD INCLUDED" badge
- [ ] Check admin notifications → Verify email received
- [ ] Customer order page → Check "FREE" badge displays correctly
- [ ] Test reorder button → Verify rewards not included

---

## Troubleshooting

### Rewards Not Showing
1. Verify migration was run: `php artisan migrate:status`
2. Check seeded products exist: `SELECT * FROM products WHERE price = 0;`
3. Verify `is_reward` column exists: `DESCRIBE order_items;`

### Admin Notifications Not Sent
1. Check mail configuration in `.env`
2. Verify admin user exists with correct role
3. Check if queued notifications are being processed: `php artisan queue:work`

### Wrong Reward Item ID
1. Check product IDs in database: `SELECT id, name, price FROM products;`
2. Update hardcoded IDs in `OrderController@store()`

---

## Future Enhancements

1. **Configurable Rewards**: Admin panel to set tier thresholds and reward items
2. **Loyalty Points**: Track customer loyalty points earned from purchases
3. **Tier History**: Show customer their tier status and progress
4. **Bulk Pricing**: Implement automatic discounts for bulk orders
5. **Reward Preferences**: Let customers choose their freebie preference
6. **Email Confirmation**: Send confirmation showing reward items to customer

---

## Files Modified

1. ✅ `database/migrations/2026_03_16_000001_add_is_reward_to_order_items_table.php` (NEW)
2. ✅ `app/Models/OrderItem.php` - Updated fillable and casts
3. ✅ `app/Models/Order.php` - No changes
4. ✅ `app/Http/Controllers/OrderController.php` - Updated checkout() and store()
5. ✅ `app/Notifications/OrderPlacedNotification.php` (NEW)
6. ✅ `database/seeders/DatabaseSeeder.php` - Added freebie products
7. ✅ `resources/views/customer/orders.blade.php` - Added reward display with FREE badge
8. ✅ `resources/views/admin/orders.blade.php` - Added Rewards column with REWARD INCLUDED flag

---

## Support

For questions or issues, refer to the Laravel and GasGo documentation, or contact the development team.
