<?php $supportEmail = 'servershopcatalog@gmail.com'; ?>

<section class="contact-page">
    <div class="contact-layout">
        <section class="contact-overview">
            <div class="contact-hero">
                <p class="contact-eyebrow">Server Catalogue / Support</p>
                <h1>How can we help you?</h1>
                <p>Have a question about a server, your configuration, or an order? Get in touch with our team.</p>
            </div>

            <div class="contact-shortcuts">
                <section class="contact-shortcut">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="m4 7 8 6 8-6" />
                    </svg>
                    <h2>Email us</h2>
                    <p>Questions about products and orders.</p>
                    <a href="mailto:<?= htmlspecialchars($supportEmail, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($supportEmail) ?></a>
                </section>

                <section class="contact-shortcut">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z" />
                        <path d="m4.5 7.5 7.5 4.3 7.5-4.3M12 12v9" />
                    </svg>
                    <h2>Order support</h2>
                    <p>Check your orders and access invoices.</p>
                    <a href="<?= BASE_URL ?>/my-orders.php">View my orders <span aria-hidden="true">&rarr;</span></a>
                </section>
            </div>
        </section>

        <section class="contact-message">
            <h2>Send us a message</h2>
            <div class="contact-form">
                <div class="contact-field">
                    <label for="contact-name">Name</label>
                    <input id="contact-name" type="text" placeholder="Your name" autocomplete="name">
                </div>
                <div class="contact-field">
                    <label for="contact-email">Email</label>
                    <input id="contact-email" type="email" placeholder="you@example.com" autocomplete="email">
                </div>
                <div class="contact-field">
                    <label for="contact-subject">Subject</label>
                    <select id="contact-subject">
                        <option value="" selected disabled>Select a topic</option>
                        <option value="product">Product question</option>
                        <option value="order">Order support</option>
                        <option value="billing">Billing</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="contact-field">
                    <label for="contact-message">Message</label>
                    <textarea id="contact-message" rows="4" placeholder="How can we help?"></textarea>
                </div>
                <button class="contact-submit" type="button" disabled>Send Message</button>
                <p class="contact-form-note">Message sending is not connected yet. Please email us directly.</p>
            </div>
        </section>

        <section class="contact-faq">
            <h2>Frequently asked questions</h2>
            <details>
                <summary>How do I configure a server?</summary>
                <p>Browse the available servers, choose Configure, select the components you need, and add your configuration to the cart.</p>
            </details>
            <details>
                <summary>Where can I find my orders?</summary>
                <p>Sign in and open your profile to view your order history and current order statuses.</p>
            </details>
            <details>
                <summary>How can I download an invoice?</summary>
                <p>Open the order details and choose Download Invoice. After payment, the invoice is also sent to the email on the order.</p>
            </details>
        </section>
    </div>
</section>