// assets/js/cart.js

document.addEventListener('DOMContentLoaded', () => {
    // Initialize cart badge count on load
    updateCartBadge();
});

// Utility to show temporary toast/alert (Optionally use Bootstrap Toast)
function showNotification(message, isError = false) {
    // For simplicity, using alert. Can be upgraded to Bootstrap toast
    alert(message);
}

// Update the badge in the Navbar
async function updateCartBadge() {
    try {
        const response = await fetch('controllers/cartController.php?action=count', {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        });
        
        const data = await response.json();
        
        if (data.success) {
            const badges = document.querySelectorAll('.cart-badge');
            badges.forEach(badge => {
                badge.textContent = data.cart_count || 0;
            });
        }
    } catch (e) {
        console.error("Error fetching cart count:", e);
    }
}

// Add item to cart
async function addToCart(productId, quantity = 1) {
    try {
        const response = await fetch('controllers/cartController.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                action: 'add',
                product_id: productId,
                quantity: quantity
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            updateCartBadge();
            // Optional: nice toast notification instead of alert
            // showNotification("Item added to cart successfully!");
        } else {
            showNotification(data.error || "Failed to add item.", true);
            if (data.error === "Please login to use the cart") {
                window.location.href = "login.php";
            }
        }
    } catch (e) {
        console.error("AJAX Error:", e);
        showNotification("Server error while adding to cart.", true);
    }
}

// Update quantity from cart page
async function updateCartQuantity(cartId, inputElem, change) {
    let currentQty = parseInt(inputElem.value);
    let newQty = currentQty + change;
    
    if (newQty < 1) newQty = 1; // Prevent going below 1 via +/- buttons. Use remove for 0.

    try {
        const response = await fetch('controllers/cartController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'update',
                cart_id: cartId,
                quantity: newQty
            })
        });

        const data = await response.json();
        
        if (data.success) {
            inputElem.value = newQty;
            updateCartBadge();
            // Ideally trigger a DOM calculation reload for subtotal here
            calculateTotals();
        } else {
            showNotification(data.error || "Failed to update quantity.", true);
        }
    } catch (e) {
        console.error("AJAX Error:", e);
    }
}

// Remove item from cart
async function removeCartItem(cartId, btnElem) {
    if (!confirm("Are you sure you want to remove this item?")) return;

    try {
        const response = await fetch('controllers/cartController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'remove',
                cart_id: cartId
            })
        });

        const data = await response.json();
        
        if (data.success) {
            // Remove row from DOM
            const row = btnElem.closest('tr');
            if (row) row.remove();
            
            updateCartBadge();
            calculateTotals();
        } else {
            showNotification(data.error || "Failed to remove item.", true);
        }
    } catch (e) {
        console.error("AJAX Error:", e);
    }
}

// Recalculates visual totals on Cart page without reload
function calculateTotals() {
    let subtotal = 0;
    const rows = document.querySelectorAll('.cart-table tbody tr');
    
    rows.forEach(row => {
        const priceCell = row.querySelector('.item-price');
        const priceStr = priceCell ? priceCell.dataset.effectivePrice : '0';
        const qty = row.querySelector('.qty-input').value;
        const totalElem = row.querySelector('.item-total');
        
        const price = parseFloat(priceStr);
        const rowTotal = price * parseInt(qty);
        
        if(totalElem) {
            totalElem.textContent = '$' + rowTotal.toFixed(2);
        }
        subtotal += rowTotal;
    });

    const subtotalElem = document.getElementById('cart-subtotal');
    const finalTotalElem = document.getElementById('cart-final-total');
    
    if (subtotalElem) {
        subtotalElem.textContent = '$' + subtotal.toFixed(2);
        // Add static shipping/tax for demo purposes
        const shipping = subtotal > 0 ? 5.00 : 0;
        const tax = subtotal * 0.10;
        const finalTotal = subtotal + shipping + tax;
        
        if(finalTotalElem) {
            finalTotalElem.textContent = '$' + finalTotal.toFixed(2);
        }
    }
}
