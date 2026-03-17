# Quick Setup Guide - Tiered Loyalty System

## Installation Steps

### Step 1: Run Migration
This adds the `is_reward` column to the `order_items` table:

```bash
php artisan migrate
```

**Expected Output:**
```
Migrating: 2026_03_16_000001_add_is_reward_to_order_items_table
Migrated:  2026_03_16_000001_add_is_reward_to_order_items_table
```

---

### Step 2: Seed Freebie Products
This adds three new products (Free LPG Tank, Dish Washer Paste, Cloth Hanger Set):

```bash
php artisan db:seed
```

Or seed only the new products:
```bash
php artisan db:seed --class=DatabaseSeeder
```

**Verify in database:**
```sql
SELECT id, name, price, stock FROM products WHERE price = 0;
```

Expected Results:
```
| id | name                          | price | stock |
|----|-------------------------------|-------|-------|
| 6  | Free LPG Tank (Reward)        | 0.00  | 999   |
| 7  | Dish Washer Paste (Freebie)   | 0.00  | 999   |
| 8  | Cloth Hanger Set (Freebie)    | 0.00  | 999   |
```

---

### Step 3: Clear Cache (Optional but Recommended)
```bash
php artisan cache:clear
php artisan view:clear
php artisan config:clear
```

---

## Testing the System

### Test Scenario 1: Bulk Order Reward (Tier A)
1. Login as customer
2. Add **10 or more** LPG tanks to cart
3. Click "Proceed to Checkout"
4. **Expected**: See "1 Free LPG Tank" in reward preview
5. Place order
6. **Check order page**: Should see reward item with "FREE" badge
7. **Check admin**: Should see "REWARD INCLUDED" badge

### Test Scenario 2: Small Order Reward (Tier B)
1. Login as customer
2. Add **5 items** (between 1-9) to cart
3. Click "Proceed to Checkout"
4. **Expected**: See "1 Free Freebie (Paste or Hanger)" preview
5. Place order
6. **Check order page**: Should see freebie with "FREE" badge
7. **Check admin**: Should see "REWARD INCLUDED" badge

### Test Scenario 3: No Reward
1. Add items to cart
2. Proceed to checkout
3. **Expected**: No reward preview
4. Place order
5. **Result**: No reward items, no "REWARD INCLUDED" badge

---

## Admin Features

### Check Admin Notifications
1. Login as admin
2. Check notifications panel
3. **Should see**: Email notification for "REWARD INCLUDED" orders

### View Orders with Rewards
1. Go to Admin → Orders
2. Look for green "REWARD INCLUDED" badge in Rewards column
3. Click to see which reward items are included
4. Use this info when packing orders

---

## Troubleshooting

### Migration Failed: "Column already exists"
**Solution**: This column shouldn't already exist. Double-check your database:
```sql
DESCRIBE order_items;
```

If `is_reward` already exists, you may need to rollback and re-run:
```bash
php artisan migrate:rollback
php artisan migrate
```

---

### Admin Notifications Not Received
**Check 1**: Verify admin user email
```sql
SELECT id, email, role FROM users WHERE role = 'admin';
```

**Check 2**: Verify mail configuration in `.env`
```
MAIL_DRIVER=...
MAIL_FROM_ADDRESS=...
```

**Check 3**: If using queue, process jobs:
```bash
php artisan queue:work
```

---

### Freebie Products Not in Database
**Solution**: Re-seed the database:
```bash
php artisan db:seed --class=DatabaseSeeder
```

Or manually create products:
```sql
INSERT INTO products (name, description, price, stock, weight, is_active, created_at, updated_at) VALUES
('Free LPG Tank (Reward)', 'Complimentary LPG Tank - Loyalty Reward', 0.00, 999, '11kg', 1, NOW(), NOW()),
('Dish Washer Paste (Freebie)', 'Free Dish Washer Paste - Small Order Loyalty Reward', 0.00, 999, '0.2kg', 1, NOW(), NOW()),
('Cloth Hanger Set (Freebie)', 'Free Cloth Hanger Set - Small Order Loyalty Reward', 0.00, 999, '0.1kg', 1, NOW(), NOW());
```

---

## Files Changed Summary

| File | Changes |
|------|---------|
| `database/migrations/2026_03_16_000001_add_is_reward_to_order_items_table.php` | NEW - Migration file |
| `app/Models/OrderItem.php` | Added `is_reward` to fillable & casts |
| `app/Http/Controllers/OrderController.php` | Updated checkout() & store() with tier logic |
| `app/Notifications/OrderPlacedNotification.php` | NEW - Admin notification |
| `database/seeders/DatabaseSeeder.php` | Added 3 freebie products |
| `resources/views/customer/orders.blade.php` | Added FREE badge & reward highlighting |
| `resources/views/admin/orders.blade.php` | Added Rewards column & REWARD INCLUDED flag |

---

## System Flow Overview

```
Order Placement Process:
┌─────────────────────────────────────┐
│ Customer Orders 10+ items           │
│ (Or 1-9 items)                      │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│ Checkout preview shows:             │
│ "1 Free LPG Tank" (or Freebie)      │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│ Place Order                         │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│ OrderController.store():            │
│ ✓ Add paid items                   │
│ ✓ Add reward item (is_reward=true) │
│ ✓ Send admin notification          │
│ ✓ Clear cart                       │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│ Admin receives notification:        │
│ "REWARD INCLUDED - Pack freebie!"  │
└──────────────┬──────────────────────┘
               ↓
┌─────────────────────────────────────┐
│ Customer sees order with FREE badge │
│ Admin sees REWARD INCLUDED flag    │
└─────────────────────────────────────┘
```

---

## Configuration Options

### To Change Tier Thresholds
Edit `app/Http/Controllers/OrderController.php` - `store()` method:

```php
if ($quantity >= 10) {      // ← Change this number for bulk threshold
    // Tier A logic
} elseif ($quantity >= 1 && $quantity <= 9) {  // ← Change this range
    // Tier B logic
}
```

### To Change Reward Products
Update the product IDs in the same file:

```php
'product_id' => 6,  // Free LPG Tank - change to different product ID
'product_id' => 7,  // Dish Washer Paste - change to different product ID
```

---

## Next Steps

✅ Run migration  
✅ Seed products  
✅ Test with bulk order (10+ items)  
✅ Test with small order (1-9 items)  
✅ Verify admin notification  
✅ Verify customer sees FREE badge  
✅ Verify admin sees REWARD INCLUDED flag  

---

## Questions?

Refer to `TIERED_LOYALTY_SYSTEM_GUIDE.md` for detailed documentation and troubleshooting.
