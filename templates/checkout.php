<?php

require_once base_path('templates/header.php');
?>

<main class="max-w-6xl mx-auto px-8 py-8">
    <div class="flex gap-8 items-start">
        
        <!-- LEFT: CART ITEMS -->
        <div class="flex-1 bg-white border rounded-lg p-6">
            <h1 class="text-3xl font-bold mb-6">Checkout</h1>
            <h2 class="text-xl font-bold mb-4">Order Summary</h2>
            
            <?php foreach ($cartItems as $item): ?>
                <div class="border-b pb-4 mb-4 last:border-b-0">
                    <div class="flex gap-4 mb-3">
                        <?php if ($item['image']): ?>
                            <img src="<?= BASE_URL ?>/assets/images/<?= htmlspecialchars($item['image']) ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                 class="w-20 h-20 object-cover rounded">
                        <?php else: ?>
                            <div class="w-20 h-20 bg-gray-200 rounded flex items-center justify-center text-sm">No image</div>
                        <?php endif; ?>
                        
                        <div class="flex-1">
                            <h3 class="font-bold text-lg"><?= htmlspecialchars($item['name']) ?></h3>
                            <?php if (!empty($item['components'])): ?>
                                <div class="text-sm text-gray-600 mt-2">
                                    <p class="font-semibold">Components:</p>
                                    <ul class="space-y-1">
                                        <?php foreach ($item['components'] as $component): ?>
                                            <li class="flex justify-between text-sm">
                                                <span>
                                                    <?= htmlspecialchars($component['name']) ?>
                                                    <span class="font-semibold">
                                                        (<?= htmlspecialchars($component['value']) ?>)
                                                    </span>
                                                </span>

                                                <span class="text-gray-500">
                                                    +€<?= number_format($component['price'], 2) ?>
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <span class="font-semibold">Price:</span>
                        <div class="flex items-center gap-4">
                            <span class="text-lg font-bold" style="color: #6a8a63;">
                                €<?= number_format($item['total_price'], 2) ?>
                            </span>
                            <button
                                type="button"
                                class="remove-checkout-item bg-red-500 text-white px-3 py-1 rounded text-sm hover:bg-red-600"
                                data-item-id="<?= $item['id'] ?>"
                                >Remove</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <div class="border-t pt-4 flex justify-between items-center text-xl font-bold">
                <span>Total:</span>
                <span style="color: #6a8a63;">€<?= number_format($totalPrice, 2) ?></span>
            </div>
        </div>
        
        
        <!-- RIGHT: Checkout Form (1 column) -->
        <div class="w-96 flex-shrink-0 sticky top-20">
            <div class="bg-white border rounded-lg p-6">
                <h2 class="text-xl font-bold mb-6">Buyer Information</h2>
                
                <form id="checkoutForm" class="notification-validation-form no-inline-validation-errors" novalidate>
                    
                    <!-- Full Name -->
                    <div class="mb-4">
                        <label for="name" class="block font-semibold mb-2">Full Name *</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            required
                            placeholder="Your Name"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2"
                        >
                        <span class="error-message text-red-500 text-sm hidden"></span>
                    </div>
                    
                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block font-semibold mb-2">Email Address *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required
                            placeholder="example@example.com"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2"
                        >
                        <span class="error-message text-red-500 text-sm hidden"></span>
                    </div>
                    
                    <!-- Phone (optional) -->
                    <div class="mb-4">
                        <label for="phone" class="block font-semibold mb-2">Phone Number</label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            placeholder="+371-2222-3333"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2"
                        >
                        <span class="error-message text-red-500 text-sm hidden"></span>
                    </div>
                    
                    <!-- Comments (optional) -->
                    <div class="mb-6">
                        <label for="comment" class="block font-semibold mb-2">Additional Comments</label>
                        <textarea 
                            id="comment" 
                            name="comment" 
                            placeholder="Any special requests..."
                            rows="3"
                            class="w-full border rounded px-3 py-2 focus:outline-none focus:ring-2"
                        ></textarea>
                        <span class="error-message text-red-500 text-sm hidden"></span>
                    </div>
                    
                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full py-3 font-bold rounded text-white transition"
                        style="background-color: #6a8a63; hover:background-color: #5a7a53;"
                    >
                        Create Order
                    </button>
                </form>
                
                <!-- Loading Indicator -->
                <div id="loadingSpinner" class="mt-4 text-center hidden">
                    <p class="text-gray-600">Processing order...</p>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Form submission handler -->
<script>
document.getElementById('checkoutForm').addEventListener('submit', async function(e) {
    if (!this.checkValidity()) {
        return;
    }

    e.preventDefault();
    
    const loadingSpinner = document.getElementById('loadingSpinner');
    const submitButton = this.querySelector('button[type="submit"]');
    
    loadingSpinner.classList.remove('hidden');
    submitButton.disabled = true;
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch('<?= BASE_URL ?>/api/orders/create.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        loadingSpinner.classList.add('hidden');
        
        if (data.success) {
            showNotification(data.message + ' Redirecting...', 'success');
            
            setTimeout(() => {
                window.location.href = '<?= BASE_URL ?>/order.php?id=' + data.orderId;
            }, 2000);
        } else {
            showNotification(data.message, 'error');
            submitButton.disabled = false;
        }
    } catch (error) {
        loadingSpinner.classList.add('hidden');
        showNotification(error.message, 'error');
        submitButton.disabled = false;
    }
});

document.querySelectorAll('.remove-checkout-item').forEach(button => {
    button.addEventListener('click', async function() {
        this.disabled = true;

        try {
            const response = await fetch('<?= BASE_URL ?>/api/cart/remove.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({item_id: this.dataset.itemId})
            });

            const data = await response.json();

            if (data.success) {
                window.location.reload();
            } else {
                this.disabled = false;
                showNotification(data.message || 'Unable to remove item', 'error');
            }
        } catch (error) {
            this.disabled = false;
            showNotification('Unable to remove item: ' + error.message, 'error');
        }
    });
});
</script>

