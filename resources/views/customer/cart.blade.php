@extends('layouts.customer')

@section('title', 'GasGo - Cart')
@section('nav-products', 'active')

@section('styles')
<style>
    .page-header {
        background: linear-gradient(135deg, var(--gasgo-blue) 0%, #2196f3 100%);
        color: white; padding: 50px 0 60px; margin-bottom: -30px; position: relative;
    }
    .page-header::after {
        content: ''; position: absolute; bottom: -2px; left: 0; right: 0; height: 60px;
        background: #f8f9fa; clip-path: ellipse(55% 100% at 50% 100%);
    }

    .cart-container { position: relative; z-index: 2; }
    .cart-card {
        background: white; border-radius: 20px; box-shadow: 0 8px 30px rgba(0,0,0,.08); overflow: hidden;
    }
    .cart-item {
        display: flex; align-items: center; gap: 16px; padding: 18px 24px;
        border-bottom: 1px solid #f0f0f0; transition: background .2s;
    }
    .cart-item input[type="checkbox"] {
        width: 20px; height: 20px; cursor: pointer; flex-shrink: 0;
    }
    .cart-item input[type="checkbox"]:checked + img {
        border: 2px solid var(--gasgo-orange);
    }
    .cart-item:hover { background: #fafafa; }
    .cart-item:last-child { border-bottom: none; }
    .cart-item img { width: 70px; height: 70px; border-radius: 12px; object-fit: cover; background: var(--gasgo-blue-light); }
    .cart-item .item-details { flex: 1; }
    .cart-item .item-name { font-weight: 600; color: var(--gasgo-blue); }
    .cart-item .item-price { color: var(--gasgo-orange); font-weight: 700; }
    .cart-item .item-subtotal { font-weight: 700; font-size: 1.05rem; color: #333; min-width: 100px; text-align: right; }
    .qty-control {
        display: flex; align-items: center; gap: 0; border: 2px solid #eee; border-radius: 10px; overflow: hidden;
    }
    .qty-control button {
        width: 34px; height: 34px; border: none; background: #f8f8f8; font-size: 1rem; cursor: pointer;
        display: flex; align-items: center; justify-content: center; transition: background .2s;
    }
    .qty-control button:hover { background: var(--gasgo-blue-light); }
    .qty-control span { width: 36px; text-align: center; font-weight: 600; font-size: .95rem; }
    .remove-btn {
        background: none; border: none; color: #ccc; font-size: 1.1rem; cursor: pointer; transition: color .2s;
    }
    .remove-btn:hover { color: #e74c3c; }

    /* Summary */
    .cart-summary {
        background: white; border-radius: 20px; padding: 28px;
        box-shadow: 0 8px 30px rgba(0,0,0,.08); position: sticky; top: 100px;
    }
    .cart-summary h5 { font-weight: 700; color: var(--gasgo-blue); border-bottom: 2px solid #f0f0f0; padding-bottom: 14px; }
    .summary-row { display: flex; justify-content: space-between; padding: 10px 0; font-size: .95rem; }
    .summary-row.total { font-size: 1.2rem; font-weight: 700; border-top: 2px solid var(--gasgo-orange); margin-top: 8px; padding-top: 14px; }
    .summary-row.total .value { color: var(--gasgo-orange); }

    /* Empty */
    .empty-cart { text-align: center; padding: 60px 20px; }
    .empty-cart i { font-size: 4rem; color: #ddd; margin-bottom: 16px; }

    /* Notification Toast */
    .notification-toast {
        position: fixed;
        bottom: 30px;
        right: 30px;
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        color: white;
        padding: 18px 24px;
        border-radius: 12px;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        min-width: 300px;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: slideInUp 0.4s ease-out;
    }

    .notification-toast.success {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    }

    .notification-toast.warning {
        background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
    }

    .notification-toast.info {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    }

    .notification-toast i {
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    .notification-toast-content {
        flex: 1;
    }

    .notification-toast-title {
        font-weight: 700;
        margin-bottom: 4px;
        font-size: 0.95rem;
    }

    .notification-toast-message {
        font-size: 0.85rem;
        opacity: 0.95;
    }

    .notification-toast-close {
        background: none;
        border: none;
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        padding: 0;
        margin-left: 12px;
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .notification-toast-close:hover {
        opacity: 1;
    }

    @keyframes slideInUp {
        from {
            transform: translateY(100px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    @keyframes slideOutDown {
        from {
            transform: translateY(0);
            opacity: 1;
        }
        to {
            transform: translateY(100px);
            opacity: 0;
        }
    }
</style>
@endsection

@section('content')
<section class="page-header">
    <div class="container text-center">
        <h1 class="fw-bold"><i class="fas fa-shopping-cart me-2"></i>Shopping Cart</h1>
        <p class="mb-0" style="opacity:.9;">Review your items before checkout</p>
    </div>
</section>

<section class="container section-padding cart-container">
    <div class="row g-4">
        @if($cartItems->isEmpty())
            <div class="col-12">
                <div class="cart-card">
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <h4 class="fw-bold" style="color:var(--gasgo-blue);">Your Cart is Empty</h4>
                        <p class="text-muted">Browse our products and add items to your cart</p>
                        <a href="{{ route('customer.products') }}" class="btn btn-gasgo mt-2">
                            <i class="fas fa-fire me-2"></i>Browse Products
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="col-lg-8">
                <div class="cart-card">
                    <div style="padding:20px 24px 10px;border-bottom:1px solid #f0f0f0;display:flex;justify-content:space-between;align-items:center;">
                        <h5 class="fw-bold mb-0" style="color:var(--gasgo-blue);">
                            <i class="fas fa-box me-2"></i><span id="cart-items-count">Cart Items ({{ $cartItems->sum('quantity') }})</span>
                        </h5>
                        <button id="select-all-btn" class="btn btn-sm" style="background:linear-gradient(135deg,var(--gasgo-orange),#ff6b35);color:white;border:none;border-radius:8px;padding:8px 16px;font-size:0.85rem;font-weight:600;cursor:pointer;transition:all 0.3s ease;">
                            <i class="fas fa-check-square me-1"></i>Select All
                        </button>
                    </div>

                    @foreach($cartItems as $item)
                        <div class="cart-item" data-product-id="{{ $item->product_id ?? $item->product->id }}" data-quantity="{{ $item->quantity }}" data-unit-price="{{ $item->product->price }}" data-max-stock="{{ $item->product->quantity_on_hand }}" data-product-name="{{ $item->product->name }}">
                            <input type="checkbox" class="item-checkbox" value="{{ $item->product_id ?? $item->product->id }}">
                            @if($item->product->resolved_image)
                                <img src="{{ $item->product->resolved_image }}" alt="{{ $item->product->name }}">
                            @else
                                <span class="text-muted small">No image available</span>
                            @endif
                            <div class="item-details">
                                <div class="item-name">{{ $item->product->name }}</div>
                                <div class="item-price" data-unit-price="{{ $item->product->price }}">₱{{ number_format($item->product->price, 2) }}</div>
                                <small style="color: #888; font-size: 0.8rem;">
                                    <i class="fas fa-box me-1"></i>
                                    Stock: <strong>{{ $item->product->quantity_on_hand }}</strong> available
                                </small>
                            </div>

                            <div class="qty-control">
                                <button class="qty-btn-minus" data-product-id="{{ $item->product_id ?? $item->product->id }}" data-quantity="{{ max(1, $item->quantity - 1) }}" {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                    <i class="fas fa-minus" style="font-size:.7rem;"></i>
                                </button>
                                <span class="item-quantity">{{ $item->quantity }}</span>
                                <button class="qty-btn-plus" data-product-id="{{ $item->product_id ?? $item->product->id }}" data-quantity="{{ $item->quantity + 1 }}" {{ $item->quantity >= $item->product->quantity_on_hand ? 'disabled' : '' }} title="{{ $item->quantity >= $item->product->quantity_on_hand ? 'Maximum stock exceeded' : '' }}">
                                    <i class="fas fa-plus" style="font-size:.7rem;"></i>
                                </button>
                            </div>

                            <div class="item-subtotal">₱{{ number_format($item->product->price * $item->quantity, 2) }}</div>

                            <button class="remove-btn" data-product-id="{{ $item->product_id ?? $item->product->id }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="col-lg-4">
                <div class="cart-summary">
                    <h5><i class="fas fa-receipt me-2"></i>Order Summary</h5>
                    
                    <div id="selected-items-list" style="max-height: 300px; overflow-y: auto; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                        <!-- Selected items will be inserted here by JavaScript -->
                    </div>
                    
                    <div class="summary-row total" style="border-top: none; margin-top: 0; padding-top: 0;">
                        <span>Total</span>
                        <span class="value" id="summary-total">₱0.00</span>
                    </div>

                    <a href="{{ route('customer.checkout') }}" class="btn btn-gasgo w-100 mt-3" onclick="proceedCheckout(event)">
                        <i class="fas fa-lock me-2"></i>Proceed to Checkout
                    </a>
                    <a href="{{ route('customer.products') }}" class="btn btn-gasgo-outline w-100 mt-2" style="padding:12px;">
                        <i class="fas fa-arrow-left me-2"></i>Continue Shopping
                    </a>
                </div>
            </div>
        @endif
    </div>
</section>
@endsection

@section('scripts')
<script>
const quantityState = new Map();

// Show notification toast at bottom right
function showNotification(title, message, type = 'error') {
    const toast = document.createElement('div');
    toast.className = `notification-toast ${type}`;
    
    let icon = 'fas fa-exclamation-circle';
    if (type === 'success') icon = 'fas fa-check-circle';
    if (type === 'warning') icon = 'fas fa-exclamation-triangle';
    if (type === 'info') icon = 'fas fa-info-circle';
    
    toast.innerHTML = `
        <i class="${icon}"></i>
        <div class="notification-toast-content">
            <div class="notification-toast-title">${title}</div>
            <div class="notification-toast-message">${message}</div>
        </div>
        <button class="notification-toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOutDown 0.4s ease-out';
        setTimeout(() => toast.remove(), 400);
    }, 5000);
}

function initQuantityState() {
    document.querySelectorAll('.cart-item').forEach(item => {
        const productId = item.dataset.productId;
        const quantity = parseInt(item.dataset.quantity || item.querySelector('.item-quantity')?.textContent || '1', 10);
        const safeQty = Number.isNaN(quantity) ? 1 : Math.max(1, quantity);

        quantityState.set(String(productId), {
            desired: safeQty,
            confirmed: safeQty,
            inFlight: false,
        });

        syncQuantityUi(productId, safeQty);
    });
}

function validateCartStock() {
    let hasOverstocked = false;
    const overstockedItems = [];
    
    document.querySelectorAll('.cart-item').forEach(item => {
        const productId = item.dataset.productId;
        const quantitySpan = item.querySelector('.item-quantity');
        const quantity = parseInt(item.dataset.quantity || '1', 10);
        const maxStock = parseInt(item.dataset.maxStock || '0', 10);
        const productName = item.dataset.productName || 'Product';
        
        if (quantity > maxStock) {
            hasOverstocked = true;
            overstockedItems.push(`${productName}: Quantity reduced from ${quantity} to ${maxStock}`);
            
            // Auto-reduce to max stock
            item.dataset.quantity = maxStock;
            if (quantitySpan) {
                quantitySpan.textContent = maxStock;
            }
            
            // Update quantity state
            const key = String(productId);
            if (quantityState.has(key)) {
                quantityState.get(key).desired = maxStock;
                quantityState.get(key).confirmed = maxStock;
            }
        }
    });
    
    if (hasOverstocked) {
        const message = overstockedItems.join('\n');
        showNotification('Stock Alert!', `Your quantities exceeded available stock:\n${message}`, 'warning');
        updateCartTotal(); // Recalculate total
    }
    
    return !hasOverstocked;
}

function syncQuantityUi(productId, quantity) {
    const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
    if (!cartItem) {
        return;
    }

    const safeQty = Math.max(1, quantity);
    cartItem.dataset.quantity = String(safeQty);

    const quantityElement = cartItem.querySelector('.item-quantity');
    if (quantityElement) {
        quantityElement.textContent = String(safeQty);
    }

    const minusBtn = cartItem.querySelector('.qty-btn-minus');
    if (minusBtn) {
        minusBtn.disabled = safeQty <= 1;
    }

    const priceElement = cartItem.querySelector('.item-price');
    const subtotalElement = cartItem.querySelector('.item-subtotal');
    if (priceElement && subtotalElement) {
        const unitPrice = parseFloat(priceElement.dataset.unitPrice || '0');
        const itemTotal = unitPrice * safeQty;
        subtotalElement.textContent = '₱' + new Intl.NumberFormat('en-PH', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(itemTotal);
    }

    updateCartTotal();
}

function queueQuantityDelta(productId, delta) {
    const key = String(productId);
    const current = quantityState.get(key);
    if (!current) {
        return;
    }

    // Get max stock for this product from the cart item data
    const cartItem = document.querySelector(`[data-product-id="${productId}"]`);
    const maxStock = cartItem ? parseInt(cartItem.dataset.maxStock) || 999 : 999;
    const productName = cartItem ? cartItem.dataset.productName : 'Product';

    // Check if trying to exceed stock
    const newDesired = Math.max(1, current.desired + delta);
    if (newDesired > maxStock) {
        showNotification('Stock Limit Reached', `${productName} has only ${maxStock} available in stock.`, 'warning');
        return;
    }

    current.desired = newDesired;
    syncQuantityUi(productId, current.desired);

    processQuantityQueue(productId);
}

async function processQuantityQueue(productId) {
    const key = String(productId);
    const state = quantityState.get(key);
    if (!state || state.inFlight || state.desired === state.confirmed) {
        return;
    }

    state.inFlight = true;
    const targetQuantity = state.desired;

    try {
        await updateCartItemAjax(productId, targetQuantity, { suppressToast: true, skipDomUpdate: true });
        state.confirmed = targetQuantity;
    } catch (error) {
        state.desired = state.confirmed;
        syncQuantityUi(productId, state.confirmed);
        showNotification('Update Failed', 'Could not update item quantity. Please try again.', 'error');
    } finally {
        state.inFlight = false;
        if (state.desired !== state.confirmed) {
            processQuantityQueue(productId);
        }
    }
}

// Handle quantity button clicks
document.addEventListener('click', function(e) {
    if (e.target.closest('.qty-btn-minus')) {
        const btn = e.target.closest('.qty-btn-minus');
        const productId = btn.dataset.productId;
        queueQuantityDelta(productId, -1);
    }
    
    if (e.target.closest('.qty-btn-plus')) {
        const btn = e.target.closest('.qty-btn-plus');
        const productId = btn.dataset.productId;
        queueQuantityDelta(productId, 1);
    }
    
    if (e.target.closest('.remove-btn')) {
        const btn = e.target.closest('.remove-btn');
        const productId = btn.dataset.productId;
        removeItem(productId);
    }
});

function updateQuantity(productId, quantity) {
    updateCartItemAjax(productId, quantity).catch(error => {
        // Error already shown in toast
    });
}

function removeItem(productId) {
    if (confirm('Are you sure you want to remove this item?')) {
        removeCartItemAjax(productId)
            .then(() => {
                quantityState.delete(String(productId));
                updateCartTotal();
            })
            .catch(error => {
                // Error already shown in toast
            });
    }
}

function getSelectedItems() {
    const checkboxes = document.querySelectorAll('.item-checkbox:checked');
    return Array.from(checkboxes).map(cb => parseInt(cb.value));
}

function clearSelectedAjax() {
    const selectedIds = getSelectedItems();
    if (selectedIds.length === 0) {
        showNotification('No Items Selected', 'Please select at least one item to remove', 'warning');
        return;
    }
    
    if (!confirm(`Remove ${selectedIds.length} selected item(s)?`)) {
        return;
    }
    
    // Remove each selected item
    selectedIds.forEach(productId => {
        removeCartItemAjax(productId).catch(error => {
            console.error('Error removing item:', error);
        });
    });
}

function proceedCheckout(event) {
    event.preventDefault();
    const selectedIds = getSelectedItems();
    if (selectedIds.length === 0) {
        showNotification('No Items Selected', 'Please select a product to checkout', 'error');
        return;
    }
    
    // Validate that selected items don't exceed stock
    let hasOverstocked = false;
    document.querySelectorAll('.cart-item').forEach(item => {
        const productId = parseInt(item.dataset.productId);
        // Only check selected items
        if (selectedIds.includes(productId)) {
            const quantity = parseInt(item.dataset.quantity || '1', 10);
            const maxStock = parseInt(item.dataset.maxStock || '0', 10);
            const productName = item.dataset.productName || 'Product';
            
            if (quantity > maxStock) {
                hasOverstocked = true;
                showNotification('Stock Exceeded!', `${productName}: You have ${quantity} but only ${maxStock} available. Please reduce quantity.`, 'error');
            }
        }
    });
    
    if (hasOverstocked) {
        return; // Prevent checkout
    }
    
    // Create a hidden form to POST selected items
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("customer.checkout") }}';
    
    // Add CSRF token
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').content;
    form.appendChild(csrfInput);
    
    // Add selected product IDs
    selectedIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_items[]';
        input.value = id;
        form.appendChild(input);
    });
    
    document.body.appendChild(form);
    form.submit();
}

// Toggle all checkboxes
function toggleAllCheckboxes(checked) {
    document.querySelectorAll('.item-checkbox').forEach(cb => {
        cb.checked = checked;
    });
}

// Update selected count and total in real-time
function updateCartTotal() {
    let subtotal = 0;
    const selectedItemsList = document.getElementById('selected-items-list');
    let selectedItemsHTML = '';

    document.querySelectorAll('.cart-item').forEach(item => {
        const checkbox = item.querySelector('.item-checkbox');
        const quantity = parseInt(item.dataset.quantity);
        const unitPrice = parseFloat(item.dataset.unitPrice);
        const productName = item.querySelector('.item-name').textContent;
        const itemSubtotal = quantity * unitPrice;
        
        if (checkbox.checked) {
            subtotal += itemSubtotal;
            
            // Build selected items list with product details
            selectedItemsHTML += `
                <div style="padding: 10px 0; border-bottom: 1px solid #f0f0f0;">
                    <div style="font-weight: 600; color: var(--gasgo-blue); font-size: 0.95rem;">
                        ${productName}
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: #666; margin-top: 4px;">
                        <span>₱${unitPrice.toFixed(2)} × ${quantity}</span>
                        <span style="font-weight: 600; color: var(--gasgo-orange);">₱${itemSubtotal.toFixed(2)}</span>
                    </div>
                </div>
            `;
        }
    });

    // If no items selected, show message
    if (selectedItemsHTML === '') {
        selectedItemsHTML = '<div style="text-align: center; color: #999; padding: 20px 0;">No items selected</div>';
    }

    // Update selected items list
    selectedItemsList.innerHTML = selectedItemsHTML;
    
    // Update total (no delivery fee)
    document.getElementById('summary-total').textContent = '₱' + subtotal.toFixed(2);
}

// Add change listeners to all checkboxes
document.querySelectorAll('.item-checkbox').forEach(cb => {
    cb.addEventListener('change', function() {
        updateCartTotal();
        const selectedCount = document.querySelectorAll('.item-checkbox:checked').length;
        const totalCount = document.querySelectorAll('.item-checkbox').length;
        console.log(`Selected: ${selectedCount}/${totalCount}`);
    });
});

// Initialize total to 0 on page load (since no items are checked by default)
window.addEventListener('load', function() {
    initQuantityState();
    validateCartStock(); // Check for overstocked items on load
    updateCartTotal();
    
    // Auto-select reordered items if they exist in sessionStorage
    const reorderedItems = sessionStorage.getItem('reorderedItems');
    if (reorderedItems) {
        try {
            const itemIds = JSON.parse(reorderedItems);
            
            // Auto-select checkboxes for reordered items
            document.querySelectorAll('.item-checkbox').forEach(cb => {
                if (itemIds.includes(String(cb.value))) {
                    cb.checked = true;
                }
            });
            
            // Update cart total and button state
            updateCartTotal();
            updateSelectAllBtn();
            
            // Show success notification
            showNotification('Reorder Successful!', `${itemIds.length} item(s) added to your cart and selected for checkout.`, 'success');
            
            // Clear sessionStorage
            sessionStorage.removeItem('reorderedItems');
        } catch (error) {
            console.error('Error processing reordered items:', error);
        }
    }
    
    // Setup Select All button
    const selectAllBtn = document.getElementById('select-all-btn');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            const allCheckboxes = document.querySelectorAll('.item-checkbox');
            const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
            
            // Toggle all checkboxes
            allCheckboxes.forEach(cb => {
                cb.checked = !allChecked;
            });
            
            // Update button text and appearance
            updateSelectAllBtn();
            
            // Update cart total
            updateCartTotal();
        });
        
        // Update button state on checkbox changes
        document.querySelectorAll('.item-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectAllBtn);
        });
    }
});

function updateSelectAllBtn() {
    const selectAllBtn = document.getElementById('select-all-btn');
    if (!selectAllBtn) return;
    
    const allCheckboxes = document.querySelectorAll('.item-checkbox');
    const checkedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
    const allChecked = allCheckboxes.length > 0 && checkedCheckboxes.length === allCheckboxes.length;
    const someChecked = checkedCheckboxes.length > 0 && checkedCheckboxes.length < allCheckboxes.length;
    
    // Update button text and icon
    if (allChecked) {
        selectAllBtn.innerHTML = '<i class="fas fa-times me-1"></i>Deselect All';
    } else if (someChecked) {
        selectAllBtn.innerHTML = '<i class="fas fa-square me-1"></i>Select All';
    } else {
        selectAllBtn.innerHTML = '<i class="fas fa-check-square me-1"></i>Select All';
    }
}
</script>
@endsection
