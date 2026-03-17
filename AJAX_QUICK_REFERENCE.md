# AJAX Quick Reference

## What Changed?

Instead of form submissions causing page refreshes, all user interactions now use AJAX with instant feedback via toast notifications.

## User Experience

### Before
```
1. User clicks "Add to Cart"
   ↓
2. Form submits
   ↓
3. Page reloads (2-3 seconds)
   ↓
4. User sees success message
   ↓ 
5. Page ready for next action
```

### After
```
1. User clicks "Add to Cart"
   ↓
2. AJAX request sent
   ↓
3. Toast appears immediately (< 200ms)
   ↓
4. User can continue shopping
   ↓
5. Page never refreshes
```

## Quick Start for Developers

### 1. Using Existing AJAX Functions

```javascript
// Add to cart
addToCartAjax(productId, 1)
    .then(response => {
        // Success - toast already shown
    })
    .catch(error => {
        // Error - toast already shown
       console.error(error);
    });
```

### 2. Adding AJAX to New Forms

**Step 1: Update Controller**
```php
public function myAction(Request $request)
{
    // Your logic here
    
    // Check if it's AJAX request
    if ($request->expectsJson()) {
        return response()->json([
            'success' => true,
            'message' => 'Success!',
            'data' => $someData
        ]);
    }
    
    // Fallback for non-AJAX
    return redirect()->back()->with('success', 'Success!');
}
```

**Step 2: Create AJAX Function**
```javascript
async function myActionAjax(data) {
    try {
        const response = await ajaxRequest(
            '/my/api/endpoint',  // URL
            'POST',              // Method
            data                 // Request body
        );
        
        showToast(response.message, 'success');
        return response;
    } catch (error) {
        showToast(error.message, 'error');
        throw error;
    }
}
```

**Step 3: Update HTML**
```html
<!-- Replace form with button -->
<form onsubmit="handleSubmit(event)">
    <input name="field1" />
    <button type="button" onclick="handleSubmit()">
        Save
    </button>
</form>

<script>
function handleSubmit(event) {
    if (event) event.preventDefault();
    
    const data = {
        field1: document.querySelector('input[name="field1"]').value
    };
    
    myActionAjax(data)
        .catch(error => console.error(error));
}
</script>
```

### 3. Toast Notifications

```javascript
// Success (auto-closes in 3 seconds)
showToast('Product added!', 'success');

// Error (auto-closes in 3 seconds)
showToast('Please try again', 'error');

// Warning (auto-closes in 3 seconds)
showToast('Warning message', 'warning');

// Info (stay visible until user closes)
showToast('Important information', 'info', 0);

// Custom timeout (5 seconds)
showToast('Custom message', 'success', 5000);
```

## Available Routes

All routes automatically available in `window.gasgoRoutes`:

```javascript
window.gasgoRoutes = {
    cartStore,              // POST /customer/cart
    cartItemUpdate,         // POST /customer/cart/item/update
    cartItemDestroy,        // POST /customer/cart/item/remove
    cartClear,              // DELETE /customer/cart
    cartSync,               // POST /customer/cart/sync
    authenticate,           // POST /customer/authenticate
    register,               // POST /customer/register
    logout,                 // POST /customer/logout
    login,                  // GET /customer/login
    dashboard,              // GET /customer/dashboard
    profileUpdate,          // POST /customer/profile
    orderStore,             // POST /customer/order
    orders,                 // GET /customer/orders
    checkout                // GET /customer/checkout
};
```

## HTTP Methods

```javascript
// GET request
const data = await ajaxRequest(url, 'GET');

// POST request
const data = await ajaxRequest(url, 'POST', { key: 'value' });

// DELETE request
const data = await ajaxRequest(url, 'DELETE');

// PUT request
const data = await ajaxRequest(url, 'PUT', { key: 'value' });
```

## Response Format

### Success
```json
{
    "success": true,
    "message": "Operation successful",
    "cartCount": 5,
    "redirect": "https://..."
}
```

### Error
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["Email already exists"],
        "password": ["Password too short"]
    }
}
```

## Common Errors & Solutions

### Toast not showing?
```javascript
// Make sure ajax-utils.js is loaded
console.log(window.showToast); // Should be function

// Initialize toast styles if needed
injectToastStyles();
```

### AJAX request failing?
```javascript
// Check CSRF token exists
const token = document.querySelector('meta[name="csrf-token"]');
console.log(token.content); // Should have a value

// Check request in browser DevTools
// F12 → Network → click request → Response tab
```

### Page still refreshing?
```html
<!-- WRONG: Form will still submit -->
<form action="/path" method="POST">
    <button type="submit">Click</button>
</form>

<!-- RIGHT: Button calls AJAX function -->
<button type="button" onclick="myAjaxFunc()">Click</button>
```

## Debug Mode

To debug AJAX requests:

```javascript
// Enable logging
const originalAjax = ajaxRequest;
window.ajaxRequest = async function(url, method, data, options) {
    console.log(`AJAX ${method} ${url}`, data);
    const response = await originalAjax(...arguments);
    console.log(`Response:`, response);
    return response;
};
```

## Performance Tips

✅ **Use AJAX for:**
- Cart operations
- Form submissions
- Quick updates
- User interactions

❌ **Don't use AJAX for:**
- Large data downloads
- File uploads (use multipart/form-data)
- Page navigation (use links)
- Authentication redirects

## Security Checklist

- ✅ CSRF token included automatically
- ✅ Server-side validation required
- ✅ Input sanitization needed
- ✅ Authentication checks needed
- ✅ Authorization checks needed
- ✅ XSS protection via content encoding

## Browser DevTools Tips

### View AJAX requests
1. Open DevTools (F12)
2. Go to Network tab
3. Filter for XHR requests
4. Click request to see details
5. Response tab shows JSON

### Check console errors
1. Open DevTools (F12)
2. Go to Console tab
3. Look for red error messages
4. Check Network requests for failed calls

### Set breakpoints
```javascript
// Add breakpoint in code
debugger; // Execution pauses here when DevTools open

// Or use conditional breakpoint
if (someCondition) debugger;
```

## Real-World Examples

### Add to Cart with Validation
```javascript
function addToCartWithValidation(productId, quantity) {
    if (!productId) {
        showToast('Invalid product', 'error');
        return;
    }
    
    if (quantity < 1) {
        showToast('Quantity must be at least 1', 'warning');
        return;
    }
    
    addToCartAjax(productId, quantity)
        .catch(error => {
            if (error.status === 401) {
                window.location.href = window.gasgoRoutes.login;
            }
        });
}
```

### Update Profile with Show/Hide Loading
```javascript
async function updateProfileWithLoading(formData) {
    const btn = document.querySelector('.save-btn');
    btn.disabled = true;
    btn.textContent = 'Saving...';
    
    try {
        await updateProfileAjax(formData);
        btn.textContent = 'Saved!';
        setTimeout(() => {
            btn.disabled = false;
            btn.textContent = 'Save';
        }, 1000);
    } catch (error) {
        btn.disabled = false;
        btn.textContent = 'Save';
    }
}
```

### Optimistic UI Update
```javascript
async function addToCartOptimistic(productId) {
    // Update UI immediately
    updateCartCountDisplay(parseInt(document.querySelector('.cart-count').textContent) + 1);
    
    try {
        // Then sync with server
        await addToCartAjax(productId, 1);
    } catch (error) {
        // Revert on error
        location.reload();
    }
}
```

## Monitoring/Analytics

You can track AJAX events for analytics:

```javascript
// Track AJAX events
document.addEventListener('ajaxSuccess', function(e) {
    // Send to analytics
    console.log('AJAX success:', e.detail);
});

document.addEventListener('ajaxError', function(e) {
    // Send to error tracking
    console.log('AJAX error:', e.detail);
});
```

## Migration Checklist

When converting a form to AJAX:

- [ ] Add `expectsJson()` check to controller
- [ ] Return JSON response
- [ ] Create AJAX function in JavaScript
- [ ] Replace form/button with AJAX call
- [ ] Add error handling
- [ ] Add success feedback (toast)
- [ ] Add loading state (optional)
- [ ] Test with browser DevTools
- [ ] Test on mobile
- [ ] Test without JavaScript (graceful degradation)

## Summary

- 🎯 **All major interactions are now AJAX**
- 📈 **Better performance and UX**
- 🔒 **Secure with CSRF protection**
- 📱 **Works on mobile**
- 🐛 **Easy to debug and extend**

Happy AJAXing! 🚀
