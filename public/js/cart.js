// Cart open/close logic

const sidebar = document.getElementById('cart-sidebar');
const overlay = document.getElementById('cart-overlay');
const cartBtn = document.getElementById('cart-btn');
const closeBtn = document.getElementById('cart-close');

function openCart() {
    sidebar.classList.remove('translate-x-full');
    sidebar.classList.add('translate-x-0');
    overlay.classList.remove('opacity-0', 'pointer-events-none');
    overlay.classList.add('opacity-50', 'pointer-events-auto');
    loadCart();
}

function closeCart() {
    sidebar.classList.remove('translate-x-0');
    sidebar.classList.add('translate-x-full');
    overlay.classList.remove('opacity-50', 'pointer-events-auto');
    overlay.classList.add('opacity-0', 'pointer-events-none');
}

cartBtn.addEventListener('click', openCart);
closeBtn.addEventListener('click', closeCart);
overlay.addEventListener('click', closeCart);

// Cart item loading

function loadCart() {
    fetch(BASE_URL + '/api/cart/get.php')
        .then(res => res.json())
        .then(data => {
            const cartItemsContainer = document.getElementById('cart-items');
            const empty = document.getElementById('cart-empty');
            const total = document.getElementById('cart-total');

            if (data.items.length === 0) {
                if (empty) empty.style.display = 'block';
                cartItemsContainer.innerHTML = '<p class="text-gray-500">Your cart is empty.</p>';
                total.textContent = '€0.00';
                document.querySelectorAll('.clear-cart-button').forEach(button => button.classList.add('hidden'));
                return;
            }

            if (empty) empty.style.display = 'none'; // Hide empty message
            document.querySelectorAll('.clear-cart-button').forEach(button => button.classList.remove('hidden'));
            let totalPrice = 0; // Initialize total price

            cartItemsContainer.innerHTML = data.items.map(item => {
                totalPrice += parseFloat(item.total_price); // Add item total price to overall total
                return `
                    <div class="flex items-center gap-3 py-3 border-b border-gray-100">
                        <img src="${BASE_URL}/assets/images/${item.image}" class="w-12 h-12 object-cover rounded">
                        <div class="flex-1">
                            <p class="font-semibold text-sm">${item.name}</p>
                            ${item.components.length > 0 ? item.components.map(c => `
                            <p class="text-xs text-gray-400">${c.type}: ${c.name} (${c.value})</p>`).join('') : ''}
                            <p class="text-green-700 font-bold">€${item.total_price}</p>
                        </div>
                        <button onclick="removeItem(${item.id})" class="text-gray-400 hover:text-red-500">&times;</button>
                    </div>
                `;
            }).join('');

            total.textContent = totalPrice.toFixed(2) + ' €'; // Update total price display
        });
}

// Remove item from cart

function removeItem(item_id) {
    fetch(BASE_URL + '/api/cart/remove.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': CSRF_TOKEN
        },
        body: JSON.stringify({item_id: item_id})
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) loadCart(); // Reload cart after removal
    });
}

document.querySelectorAll('.clear-cart-button').forEach(button => {
    button.addEventListener('click', async function() {
        const confirmed = await showConfirmationDialog({
            title: 'Clear the cart?',
            message: 'This will remove every item from your cart. You can add them again later.',
            confirmLabel: 'Clear Cart',
            variant: 'user-danger'
        });
        if (!confirmed) return;

        document.querySelectorAll('.clear-cart-button').forEach(item => {
            item.disabled = true;
        });

        try {
            const response = await fetch(BASE_URL + '/api/cart/clear.php', {
                method: 'POST',
                headers: {'X-CSRF-Token': CSRF_TOKEN}
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Unable to clear cart');
            }

            if (document.getElementById('checkout-page')) {
                window.location.href = BASE_URL + '/index.php';
                return;
            }

            showNotification(data.message, 'success');
            loadCart();
        } catch (error) {
            showNotification(error.message, 'error');
            document.querySelectorAll('.clear-cart-button').forEach(item => {
                item.disabled = false;
            });
        }
    });
});