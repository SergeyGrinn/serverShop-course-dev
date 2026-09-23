<?php 

if (!isset($_COOKIE['cookie_consent'])): ?>
    <div id="cookie-banner" class="fixed bottom-0 left-0 right-0 bg-white p-4 shadow-lg">
        <p class="mb-3">
            We use cookies to keep you logged in and remember your cart.
        </p>

        <div class="flex gap-3">
            <button
                type="button"
                class="cookie-consent-button bg-green-600 text-white px-4 py-2 rounded"
                data-consent="accepted"
            >
                Accept
            </button>
            <button
                type="button"
                class="cookie-consent-button bg-gray-500 text-white px-4 py-2 rounded"
                data-consent="declined"
            >
                Decline
            </button>
        </div>
    </div>

    <script>
        document.querySelectorAll('.cookie-consent-button').forEach(button => {
            button.addEventListener('click', async () => {
                document.querySelectorAll('.cookie-consent-button').forEach(item => {
                    item.disabled = true;
                });

                await fetch('<?= BASE_URL ?>/api/cookies/consent.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({consent: button.dataset.consent})
                });

                document.getElementById('cookie-banner').remove();
            });
        });
    </script>
<?php endif; ?>