<div id="cart-sidebar" class="fixed top-0 right-0 h-full w-80 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 z-50 flex flex-col">
    
    <div class="flex items-center justify-between p-5 border-b border-gray-200">
        <h2 class="text-lg font-bold">Cart</h2>
        <button id="cart-close" class="text-gray-400 hover:text-gray-600 text-2xl">&times;</button>
    </div>

    <div id="cart-items" class="flex-1 overflow-y-auto p-5">
        <p id="cart-empty" class="text-gray-400 text-center mt-10">Cart is empty</p>
    </div>

    <div class="p-5 border-t border-gray-200">
        <div class="flex justify-between mb-4">
            <span class="font-semibold">Total:</span>
            <span id="cart-total" class="font-bold text-green-700">0 €</span>
        </div>
        <a href="<?= APP_URL ?>public/checkout.php" class="block text-center text-white py-2 rounded-lg font-semibold" style="background-color: #308020;">Checkout</a>
    </div>

</div>

<div id="cart-overlay" class="fixed inset-0 bg-black opacity-0 pointer-events-none transition-opacity duration-300 z-40"></div>