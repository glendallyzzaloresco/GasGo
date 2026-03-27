/**
 * Global AJAX Utilities for GasGo Application
 * Handles all AJAX operations with toast notifications
 * 
 * Usage: Include in layout and call AJAX functions directly
 * Example: addToCartAjax(productId, quantity)
 */

// Global route store (to be set by views)
window.gasgoRoutes = window.gasgoRoutes || {};

// Initialize toast notification
function showToast(message, type = 'info', duration = 3000) {
    console.log(`[Toast] ${type.toUpperCase()}: ${message}`);
    
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-icon">
                ${type === 'success' ? '<i class="fas fa-check-circle"></i>' : 
                  type === 'error' ? '<i class="fas fa-exclamation-circle"></i>' :
                  type === 'warning' ? '<i class="fas fa-warning"></i>' :
                  '<i class="fas fa-info-circle"></i>'}
            </span>
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="this.parentElement.parentElement.remove()">×</button>
        </div>
    `;
    
    console.log('[Toast] Container:', toastContainer);
    console.log('[Toast] Toast element:', toast);
    
    toastContainer.appendChild(toast);
    
    if (duration) {
        setTimeout(() => toast.remove(), duration);
    }
    
    return toast;
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toast-container';
    container.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
    `;
    document.body.appendChild(container);
    return container;
}

// Add toast styles to page
function injectToastStyles() {
    if (document.getElementById('toast-styles')) return;
    
    const style = document.createElement('style');
    style.id = 'toast-styles';
    style.innerHTML = `
        #toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            min-width: 300px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }
        
        .toast {
            margin-bottom: 0;
            animation: slideIn 0.3s ease-out;
            pointer-events: auto;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .toast-content {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 14px;
            gap: 12px;
            width: 100%;
            box-sizing: border-box;
        }
        
        .toast-success .toast-content {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .toast-error .toast-content {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .toast-warning .toast-content {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .toast-info .toast-content {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        .toast-icon {
            font-size: 18px;
            flex-shrink: 0;
            min-width: 20px;
        }
        
        .toast-message {
            flex: 1;
            font-weight: 500;
            line-height: 1.4;
        }
        
        .toast-close {
            background: none;
            border: none;
            font-size: 20px;
            cursor: pointer;
            opacity: 0.7;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: opacity 0.2s;
            flex-shrink: 0;
            color: inherit;
        }
        
        .toast-close:hover {
            opacity: 1;
        }
    `;
    document.head.appendChild(style);
}

// Generic AJAX request handler
async function ajaxRequest(url, method = 'GET', data = null, options = {}) {
    const defaultOptions = {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    };
    
    if (method !== 'GET') {
        defaultOptions.headers['Content-Type'] = 'application/json';
        defaultOptions.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content || '';
    }
    
    const config = {
        method,
        ...defaultOptions,
        ...options
    };
    
    if (data && method !== 'GET') {
        config.body = JSON.stringify(data);
    }
    
    try {
        const response = await fetch(url, config);
        const json = await response.json();
        
        if (!response.ok) {
            throw {
                status: response.status,
                message: json.message || 'An error occurred',
                errors: json.errors || {}
            };
        }
        
        return json;
    } catch (error) {
        if (error.message) {
            throw error;
        }
        throw {
            status: error.status || 500,
            message: error.message || 'Network error',
            errors: error.errors || {}
        };
    }
}

// Add to Cart AJAX
async function addToCartAjax(productId, quantity = 1) {
    try {
        const response = await ajaxRequest(window.gasgoRoutes.cartStore || '/customer/cart', 'POST', {
            product_id: productId,
            quantity: quantity
        });
        
        showToast(response.message || 'Product added to cart', 'success');
        updateCartCountDisplay(response.cartCount);
        // Update cart items header if on cart page
        const cartItemsHeader = document.getElementById('cart-items-count');
        if (cartItemsHeader) {
            cartItemsHeader.textContent = `Cart Items (${response.cartCount})`;
        }
        return response;
    } catch (error) {
        if (error.status === 401) {
            showToast('Please login to add items to cart', 'warning');
            window.location.href = window.gasgoRoutes.login || '/customer/loginRegistration';
        } else {
            showToast(error.message || 'Failed to add product', 'error');
        }
        throw error;
    }
}

// Update Cart Item AJAX
async function updateCartItemAjax(productId, quantity, options = {}) {
    const { suppressToast = false, skipDomUpdate = false } = options;

    try {
        const response = await ajaxRequest(window.gasgoRoutes.cartItemUpdate || '/customer/cart/item/update', 'POST', {
            product_id: productId,
            quantity: quantity
        });
        
        if (!suppressToast) {
            showToast(response.message || 'Cart updated', 'success');
        }

        if (!skipDomUpdate) {
            updateCartItemDisplay(productId, quantity);
        }

        if (response.cartCount !== undefined) {
            updateCartCountDisplay(response.cartCount);
            // Update cart items header
            const cartItemsHeader = document.getElementById('cart-items-count');
            if (cartItemsHeader) {
                cartItemsHeader.textContent = `Cart Items (${response.cartCount})`;
            }
        }
        // Update totals
        updateCartTotals();
        return response;
    } catch (error) {
        if (!suppressToast) {
            showToast(error.message || 'Failed to update cart', 'error');
        }
        throw error;
    }
}

// Remove Cart Item AJAX
async function removeCartItemAjax(productId) {
    try {
        const response = await ajaxRequest(window.gasgoRoutes.cartItemDestroy || '/customer/cart/item/remove', 'POST', {
            product_id: productId
        });
        
        showToast(response.message || 'Item removed from cart', 'success');
        removeCartItemDisplay(productId);
        if (response.cartCount !== undefined) {
            updateCartCountDisplay(response.cartCount);
            // Update cart items header
            const cartItemsHeader = document.getElementById('cart-items-count');
            if (cartItemsHeader) {
                cartItemsHeader.textContent = `Cart Items (${response.cartCount})`;
            }
            // If cart is now empty, refresh the page to show empty cart message
            if (response.cartCount === 0) {
                setTimeout(() => location.reload(), 500);
            }
        }
        // Update totals
        updateCartTotals();
        return response;
    } catch (error) {
        showToast(error.message || 'Failed to remove item', 'error');
        throw error;
    }
}

// Clear Cart AJAX
async function clearCartAjax() {
    if (!confirm('Are you sure you want to clear your entire cart?')) {
        return;
    }
    
    try {
        const response = await ajaxRequest(window.gasgoRoutes.cartClear || '/customer/cart', 'DELETE');
        
        showToast(response.message || 'Cart cleared', 'success');
        // Refresh page to show empty cart message
        setTimeout(() => location.reload(), 500);
        return response;
    } catch (error) {
        showToast(error.message || 'Failed to clear cart', 'error');
        throw error;
    }
}

// Sync Cart AJAX (for reorder)
async function syncCartAjax(items) {
    try {
        const response = await ajaxRequest(window.gasgoRoutes.cartSync || '/customer/cart/sync', 'POST', { items });
        
        showToast(response.message || 'Items added to cart', 'success');
        return response;
    } catch (error) {
        showToast(error.message || 'Failed to sync cart', 'error');
        throw error;
    }
}

// Login AJAX
async function loginAjax(email, password, remember = false) {
    try {
        const response = await ajaxRequest(window.gasgoRoutes.authenticate || '/customer/authenticate', 'POST', {
            email,
            password,
            remember: remember ? 1 : 0
        });
        
        showToast(response.message || 'Login successful', 'success');
        setTimeout(() => {
            window.location.href = response.redirect || (window.gasgoRoutes.dashboard || '/customer/dashboard');
        }, 1000);
        return response;
    } catch (error) {
        showToast(error.message || 'Login failed', 'error');
        throw error;
    }
}

// Register AJAX
async function registerAjax(formData) {
    try {
        const response = await ajaxRequest(window.gasgoRoutes.register || '/customer/register', 'POST', formData);
        
        showToast(response.message || 'Registration successful', 'success');
        setTimeout(() => {
            window.location.href = response.redirect || (window.gasgoRoutes.dashboard || '/customer/dashboard');
        }, 1000);
        return response;
    } catch (error) {
        showToast(error.message || 'Registration failed', 'error');
        throw error;
    }
}

// Logout AJAX
async function logoutAjax() {
    try {
        const response = await ajaxRequest(window.gasgoRoutes.logout || '/customer/logout', 'POST');
        
        showToast(response.message || 'Logged out successfully', 'success');
        setTimeout(() => {
            window.location.href = response.redirect || (window.gasgoRoutes.dashboard || '/customer/dashboard');
        }, 1000);
        return response;
    } catch (error) {
        showToast(error.message || 'Logout failed', 'error');
        throw error;
    }
}

// Update Cart Count Display
function updateCartCountDisplay(count) {
    if (!count && count !== 0) return;
    
    const countElements = document.querySelectorAll('.cart-count');
    countElements.forEach(el => {
        el.textContent = count;
    });
}

// Update Cart Item Display (in cart page)
function updateCartItemDisplay(productId, quantity) {
    const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
    if (cartItem) {
        cartItem.dataset.quantity = String(quantity);

        const quantityElement = cartItem.querySelector('.item-quantity');
        if (quantityElement) {
            quantityElement.textContent = quantity;
        }

        const minusButton = cartItem.querySelector('.qty-btn-minus');
        if (minusButton) {
            minusButton.disabled = quantity <= 1;
        }
        
        const priceElement = cartItem.querySelector('.item-price');
        const subtotalElement = cartItem.querySelector('.item-subtotal');
        if (priceElement && subtotalElement) {
            const unitPrice = parseFloat(priceElement.dataset.unitPrice);
            const itemTotal = (unitPrice * quantity).toFixed(2);
            subtotalElement.textContent = '₱' + new Intl.NumberFormat('en-PH').format(parseFloat(itemTotal));
        }
    }
}

// Remove Cart Item Display
function removeCartItemDisplay(productId) {
    const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
    if (cartItem) {
        cartItem.style.animation = 'fadeOut 0.3s ease-out forwards';
        setTimeout(() => cartItem.remove(), 300);
    }
}

// Update cart totals based on remaining items
function updateCartTotals() {
    const cartItems = document.querySelectorAll('.cart-item');
    let subtotal = 0;
    
    cartItems.forEach(item => {
        const subtotalEl = item.querySelector('.item-subtotal');
        if (subtotalEl) {
            const subtotalText = subtotalEl.textContent.replace('₱', '').replace(/,/g, '');
            subtotal += parseFloat(subtotalText) || 0;
        }
    });
    
    const deliveryFee = 50;
    const total = subtotal + deliveryFee;
    
    // Update all summary rows
    const summaryRows = document.querySelectorAll('.summary-row');
    summaryRows.forEach(row => {
        const firstSpan = row.querySelector('span:first-child');
        if (firstSpan && firstSpan.textContent.includes('Subtotal')) {
            const valueSpan = row.querySelector('span:last-child');
            if (valueSpan) {
                valueSpan.textContent = '₱' + subtotal.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            }
        }
    });
    
    // Update total
    const totalElements = document.querySelectorAll('.summary-row.total .value');
    if (totalElements.length > 0) {
        totalElements[0].textContent = '₱' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }
}

// Profile Update AJAX
async function updateProfileAjax(formData) {
    try {
        const response = await ajaxRequest(window.gasgoRoutes.profileUpdate || '/customer/profile', 'POST', formData);
        
        showToast(response.message || 'Profile updated successfully', 'success');
        return response;
    } catch (error) {
        showToast(error.message || 'Failed to update profile', 'error');
        throw error;
    }
}

// Order Placement AJAX
async function placeOrderAjax(formData) {
    try {
        const response = await ajaxRequest(window.gasgoRoutes.orderStore || '/customer/order', 'POST', formData);
        
        showToast(response.message || 'Order placed successfully', 'success');
        setTimeout(() => {
            window.location.href = response.redirect || (window.gasgoRoutes.orders || '/customer/orders');
        }, 1500);
        return response;
    } catch (error) {
        showToast(error.message || 'Failed to place order', 'error');
        throw error;
    }
}

// Initialize AJAX on page load
document.addEventListener('DOMContentLoaded', function() {
    injectToastStyles();
    
    // Add fade-out animation
    const style = document.createElement('style');
    style.innerHTML = `
        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(-20px);
            }
        }
    `;
    document.head.appendChild(style);
});
