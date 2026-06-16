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
                        <span class="text-lg font-bold" style="color: #6a8a63;">€<?= number_format($item['total_price'], 2) ?></span>
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
                
                <form id="checkoutForm">
                    
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
                
                <!-- Info Message -->
                <div id="messageContainer" class="mt-4 p-3 rounded hidden"></div>
                
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
    e.preventDefault();
    
    const loadingSpinner = document.getElementById('loadingSpinner');
    const messageContainer = document.getElementById('messageContainer');
    const submitButton = this.querySelector('button[type="submit"]');
    
    loadingSpinner.classList.remove('hidden');
    submitButton.disabled = true;
    messageContainer.classList.add('hidden');
    
    try {
        const formData = new FormData(this);
        
        const response = await fetch('<?= BASE_URL ?>/api/orders/create.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        loadingSpinner.classList.add('hidden');
        
        if (data.success) {
            messageContainer.classList.remove('hidden');
            messageContainer.classList.add('bg-green-100', 'border', 'border-green-400', 'text-green-700');
            messageContainer.textContent = '✓ ' + data.message + ' Redirecting...';
            
            setTimeout(() => {
                window.location.href = '<?= BASE_URL ?>/order.php?id=' + data.orderId;
            }, 2000);
        } else {
            messageContainer.classList.remove('hidden');
            messageContainer.classList.add('bg-red-100', 'border', 'border-red-400', 'text-red-700');
            messageContainer.textContent = '✗ Error: ' + data.message;
            submitButton.disabled = false;
        }
    } catch (error) {
        loadingSpinner.classList.add('hidden');
        messageContainer.classList.remove('hidden');
        messageContainer.classList.add('bg-red-100', 'border', 'border-red-400', 'text-red-700');
        messageContainer.textContent = '✗ Error: ' + error.message;
        submitButton.disabled = false;
    }
});
</script>

