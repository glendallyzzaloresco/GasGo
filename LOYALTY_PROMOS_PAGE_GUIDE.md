# GasGo Loyalty & Promos Page - Design & Implementation Guide

## 📋 Overview

A comprehensive, responsive "Loyalty & Promos" page that provides different experiences for **guest** and **logged-in** users, following the GasGo design theme with orange gradients, rounded cards, and professional styling.

---

## 🎨 Design System

### Colors
- **Primary Orange**: `#f79421` (var(--gasgo-orange))
- **Accent Orange**: `#ff6b35`
- **Primary Blue**: `#0f3460` (var(--gasgo-blue))
- **Neutral**: `#f8f9fa` (backgrounds), `#e0e0e0` (borders)
- **Text**: `#555` (body), `#888` (secondary)

### Typography
- **Headers**: Ubuntu Bold, 700 weight
- **Body**: System font stack, 400-600 weight
- **Sizes**: 
  - H1: 2.5rem
  - H2 (Section): 2rem, 1.5rem mobile
  - H5-H6: 1rem-1.2rem

### Spacing
- Section padding: 60px top/bottom (40px mobile)
- Card padding: 24-40px
- Gap between items: 12px-30px
- Margins: Scale from 12px to 50px

### Radius
- Large cards: 20px
- Medium cards: 16px
- Pill buttons: 25px
- Small elements: 12px, 8px

### Shadows
- Subtle: `0 4px 15px rgba(0,0,0,0.06)`
- Medium: `0 8px 30px rgba(0,0,0,0.08)`
- Hover: `0 12px 40px rgba(0,0,0,0.12)`
- Orange accent: `0 10px 30px rgba(247,148,29,0.2)`

---

## 📱 Page Sections

### 1. Page Header
**Purpose**: Introduce the page with visual impact

```
┌─────────────────────────────────────────┐
│  🎁 Loyalty & Promos                    │
│  Earn loyalty points with every order   │
│  and unlock exclusive rewards          │
└─────────────────────────────────────────┘
```

- **Background**: Linear gradient (Orange → accent orange)
- **Text**: White, centered
- **Curve**: Wave clip-path at bottom
- **Height**: 50px padding top/bottom
- **Responsive**: Title scales down on mobile

---

### 2. Guest CTA Banner (Guests Only)
**Purpose**: Drive registration/login for non-authenticated users

**When Visible**: Only for guests (automatically hidden for logged-in users)

**Layout**:
```
┌──────────────────────────────────────────────────────┐
│ Unlock Rewards &          [Register] [Login]        │
│ Exclusive Promos          ← Continue browsing        │
│ Login or register to track loyalty...               │
└──────────────────────────────────────────────────────┘
```

**Features**:
- Orange gradient background (matches header)
- Left: Headline, subtitle, small link
- Right: Two buttons (Primary white, Secondary outline)
- Mobile: Full width, stacked buttons, centered text
- Shadow: Orange-tinted (0 10px 30px rgba(247,148,29,0.2))
- Border radius: 20px

**Button Styles**:
- **[Register]** (Primary): White bg, orange text, no border
- **[Login]** (Secondary): Transparent bg, white border, white text

---

### 3. Promos Today Section
**Purpose**: Showcase available promotions for all customers

**Layout**:
```
PROMOS TODAY
Special offers available for all customers

┌─────────────────────┐  ┌─────────────────────┐
│ 🎁 FREE Freebie... │  │ 🔥 Buy 10+ LPG...   │
│                     │  │                     │
│ AUTO-INCLUDED      │  │ BULK BONUS          │
│                     │  │                     │
│ ✓ Buy at least 1   │  │ ✓ Total LPG qty >= 10
│ ✓ 1 freebie/order  │  │ ✓ Auto-added in cart
│ ✓ Automatically... │  │ ✓ Free tank deliv...│
└─────────────────────┘  └─────────────────────┘
```

**Promo Card Details**:
- **Icon**: Large (2.5rem), orange color
- **Badge**: Inline, uppercase, small font
  - Success (green tint): AUTO-INCLUDED
  - Info (blue tint): BULK BONUS
- **Title**: Bold, 1.1rem, blue color
- **Rules**: List with check icons, 0.9rem
- **Hover**: Lift transform (-5px), enhanced shadow, orange border
- **Spacing**: 28px padding, gap 20px between cards

**Mobile**: Full width, stacked vertically

---

### 4. How Loyalty Works (Steps)
**Purpose**: Educate users on the loyalty process

**Layout**:
```
HOW LOYALTY WORKS
Follow these simple steps...

┌──────────┐    ┌──────────┐    ┌──────────┐    ┌──────────┐
│    1     │──→ │    2     │──→ │    3     │──→ │    4     │
│Register. │    │  Place   │    │  Order   │    │ Earn &   │
│  Login   │    │ Orders   │    │ Delivered│    │ Redeem   │
└──────────┘    └──────────┘    └──────────┘    └──────────┘
```

**Step Item Details**:
- **Numeric Badge**: Circular, orange gradient, centered (50x50px)
- **Icon**: 2rem, orange (shopping-cart, box, coins, etc.)
- **Title**: 0.95rem, bold, blue
- **Card**: White background, rounded, subtle shadow
- **Connector**: Horizontal line (desktop only), gradient transparent
- **Hover**: Slight lift, enhanced shadow

**Grid**:
- Desktop: 4 columns
- Tablet: 2 columns
- Mobile: 1 column (connectors hidden)

---

### 5. Your Loyalty Progress (Dual Content)

#### A. For Guests: "Example Loyalty Progress"
```
EXAMPLE LOYALTY PROGRESS
Here's how your progress will look once you start ordering:

┌────────────────────────────────────────────┐
│ Stats:                                     │
│  0 Orders  |  0 Points  |  0 Vouchers    │
├────────────────────────────────────────────┤
│ Progress to ₱50 OFF Voucher              │
│ ▏░░░░░░░░░░░░░░░░░░░░░░░░ 0%            │
│ Complete 10 delivered orders to unlock    │
│ ₱50 OFF voucher                          │
├────────────────────────────────────────────┤
│ 💡 Login to track your real progress.   │
└────────────────────────────────────────────┘
```

#### B. For Logged-In: "Your Loyalty Progress"
```
YOUR LOYALTY PROGRESS

┌────────────────────────────────────────────┐
│ Stats:                                     │
│  6 Orders  |  12 Points  |  0 Vouchers   │
├────────────────────────────────────────────┤
│ Progress to Free LPG Tank                 │
│ ▓▓▓▓▓▓░░░░░░░░░░░░░░░░░░ 60%           │
│ 4 more orders to unlock Free LPG Tank    │
├────────────────────────────────────────────┤
│ Order Stamps:                             │
│ 🔥 🔥 🔥 🔥 🔥 🔥 ⓻ ⓼ ⓽ ⓾              │
│                                           │
│ Complete 10 orders to get a FREE LPG Tank!
└────────────────────────────────────────────┘
```

**Card Details**:
- **Container**: White, 40px padding, rounded 20px, subtle shadow
- **Stats Box**: 3-column grid
  - Large number (1.5rem), orange color
  - Small label (0.8rem), grey
  - Background: #f8f9fa
  - Border radius: 12px
- **Progress Bar**: 14px height, rounded, grey background with orange gradient fill
- **Stamps**: 10 circular stamps (56x56px)
  - Unfilled: Dashed border, grey color, showing number
  - Filled: Orange gradient, fire icon
  - Gap: 12px

**Mobile**: Single column layout, centered stats

---

### 6. Available Vouchers (Logged-In Only)
**Purpose**: Show redeemable vouchers

**Visibility**: Only if `$availableVouchers->count() > 0`

```
YOUR AVAILABLE VOUCHERS (2)

┌─────────────────────────────────────────┐
│ ₱50 OFF Voucher              [Apply]   │
│ Get ₱50 discount on orders              │
│ Requires 500 points                     │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│ ₱200 OFF Promo              [Apply]    │
│ Get ₱200 discount on large orders       │
│ Requires 1000 points                    │
└─────────────────────────────────────────┘
```

**Card Details**:
- **Border**: 2px solid orange
- **Shadow**: Orange-tinted (0 4px 15px rgba(247,148,29,0.1))
- **Layout**: Flex (space-between on desktop, column on mobile)
- **Left Section**:
  - Title: Bold blue, 0.95rem
  - Description: 0.85rem, grey
  - Points: Small, orange text
- **Right Button**: "Apply at Checkout", orange bg, white text, 8px padding

---

### 7. Rewards You Can Earn
**Purpose**: Show aspirational rewards

```
REWARDS YOU CAN EARN
See what's waiting for you

┌────────────────┐  ┌────────────────┐  ┌────────────────┐
│      💰        │  │      🔥        │  │      ⭐        │
│ ₱50 OFF        │  │ Free LPG       │  │ ₱200 OFF       │
│ Voucher        │  │ Tank           │  │ Promo          │
│ 500 points     │  │ 10 orders      │  │ 1000 points    │
└────────────────┘  └────────────────┘  └────────────────┘
```

**Card Details**:
- **Layout**: 3-column grid (auto-fit minmax 200px)
- **Icon**: 2.5rem, orange, centered
- **Title**: Bold blue
- **Requirement**: 0.85rem, grey
- **Border**: 2px grey
- **Hover**: Orange border, enhanced shadow (orange-tinted)
- **Background**: Linear gradient (light grey → white)

**Mobile**: Single column

---

### 8. FAQ Accordion
**Purpose**: Answer common questions

```
FREQUENTLY ASKED QUESTIONS
Get answers to common questions

┌────────────────────────────────────────┐
│ ▼ Do I need to log in to earn points? │
│   Yes, loyalty points are stored in    │
│   your account. You must be logged in  │
│   to track and redeem them.            │
└────────────────────────────────────────┘

┌────────────────────────────────────────┐
│ ▶ When are loyalty points added?      │
│   [collapsed - expand on click]        │
└────────────────────────────────────────┘

┌────────────────────────────────────────┐
│ ▶ Do promos apply to guests?           │
│   [collapsed - expand on click]        │
└────────────────────────────────────────┘

┌────────────────────────────────────────┐
│ ▶ How do I redeem vouchers?            │
│   [collapsed - expand on click]        │
└────────────────────────────────────────┘
```

**Accordion Details**:
- **Button** (collapsed): 2px grey border, white bg, blue text, 16px padding, rounded 12px
- **Button** (expanded): Orange gradient bg, white text, enhanced shadow
- **Body**: #f8f9fa background, 20px padding, 0.85rem text, line-height 1.6
- **Max-width**: 800px, centered
- **First Item**: Open by default

---

### 9. Bottom CTA (Call To Action)

#### For Guests:
```
READY TO START EARNING?
Join thousands of happy GasGo customers today.

[Register or Login]
```

#### For Logged-In Users:
```
KEEP EARNING!
Place more orders and unlock more rewards.

[Browse Products]
```

**Details**:
- **Layout**: Centered, text-align center
- **Headline**: 1.2rem, bold, blue
- **Subtitle**: 0.95rem, grey
- **Button**: 12px vertical padding, 40px horizontal, rounded 25px, orange bg, white text
- **Icons**: FontAwesome (user-plus or shopping-cart)

---

## 🔧 Backend Implementation

### LoyaltyController::index()

**Data Structure Passed to View**:

```php
[
    // Authentication
    'isGuest' => bool,
    
    // User Loyalty (if logged in)
    'points' => Collection,
    'totalEarned' => int,
    'totalRedeemed' => int,
    'balance' => int,
    'completedOrders' => int,
    'availableVouchers' => Collection,
    'pointsToNextReward' => int,
    
    // Marketing (all users)
    'promos' => [
        [
            'id' => 'freebie_auto',
            'title' => 'FREE Freebie with Any LPG Order',
            'badge' => 'AUTO-INCLUDED',
            'badgeColor' => 'success', // or 'info'
            'icon' => 'fas fa-gift',
            'rules' => [
                'Buy at least 1 LPG item',
                '1 freebie per order',
                'Automatically added at checkout when qualified',
            ],
        ],
        // ... more promos
    ],
    
    'loyaltySteps' => [
        ['number' => 1, 'title' => 'Register / Login', 'icon' => 'fas fa-user-plus'],
        // ... more steps
    ],
    
    'rewards' => [
        ['title' => '₱50 OFF Voucher', 'requirement' => '500 points', 'icon' => 'fas fa-tag'],
        // ... more rewards
    ],
    
    'faqs' => [
        [
            'question' => 'Do I need to log in to earn loyalty points?',
            'answer' => 'Yes, loyalty points are logged in your account. You must be logged in to track and redeem them.',
        ],
        // ... more FAQs
    ],
]
```

---

## 📐 Responsive Breakpoints

### Desktop (≥1024px)
- Full featured layout
- Multi-column grids (3-4 columns)
- Side-by-side banners
- Step connectors visible

### Tablet (768px-1023px)
- 2-column grids
- Flexible layouts
- Touch-friendly buttons (48px min height)

### Mobile (375px-767px)
- Single column, full-width
- Stacked buttons
- Centered text
- Step connectors hidden
- Reduced padding/margins
- Larger touch targets
- Simplified banner layout

---

## 🎯 Key Features

✅ **Guest/Auth-Aware**: Different content for different user states
✅ **Performance**: Optimized CSS, minimal animations
✅ **Accessibility**: Semantic HTML, ARIA labels, keyboard navigation
✅ **SEO**: Proper heading hierarchy, meta tags
✅ **Mobile-First**: Responsive cascade design
✅ **Animations**: AOS library (fade-up), staggered delays
✅ **Icons**: FontAwesome 6 Pro icons throughout
✅ **Consistency**: GasGo brand colors, typography, spacing

---

## 🚀 Implementation Files

- **Controller**: `app/Http/Controllers/LoyaltyController.php` → Updated `index()` method
- **View**: `resources/views/customer/loyalty.blade.php` → Complete redesign
- **Route**: `routes/web.php` → `/customer/loyaltyRewards` (existing)
- **Layout**: Uses `layouts.customer` (existing)

---

## 📝 Future Enhancements

1. **Admin Dashboard**: Manage promos, vouchers, rewards
2. **Email Notifications**: Loyalty milestone alerts
3. **Referral Program**: Earn points for referrals
4. **Tier System**: Bronze/Silver/Gold tiers with escalating rewards
5. **Analytics**: Track promo effectiveness, user engagement
6. **Integration**: Connect to checkout for automatic promo application
7. **Push Notifications**: Real-time loyalty updates
8. **Gamification**: Badges, achievements, leaderboards

---

## ✨ Color Reference

```html
<!-- Orange Gradient (headers, highlights) -->
background: linear-gradient(135deg, var(--gasgo-orange) 0%, #ff6b35 100%);

<!-- Blue Accents (text, badges) -->
color: var(--gasgo-blue); /* #0f3460 */

<!-- Neutral Backgrounds -->
background: #f8f9fa;

<!-- Borders -->
border: 2px solid #e0e0e0;
```

---

**Last Updated**: March 28, 2026
**Status**: ✅ Implementation Complete
**Version**: 1.0
