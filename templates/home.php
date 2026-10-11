<?php $heroThemeClass = $isAdmin ? 'home-hero--admin' : 'home-hero--user'; ?>

<section class="home-page mx-auto w-full px-4 py-8 md:px-8">
    <div class="home-hero <?= htmlspecialchars($heroThemeClass, ENT_QUOTES, 'UTF-8') ?>">
        <div class="home-hero__media" aria-hidden="true">
            <img src="<?= BASE_URL ?>/assets/images/homebanner.png" alt="" fetchpriority="high">
        </div>

        <div class="home-hero__content">
            <p class="home-hero__eyebrow"><span aria-hidden="true"></span> Reliable compute, tailored to you</p>
            <h1>Power your next big idea.</h1>
            <p class="home-hero__description">Choose a server built around the performance, capacity, and storage your work demands.</p>

            <a class="home-hero__button" href="<?= BASE_URL ?>/index.php">
                Explore servers
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
            </a>

            <ul class="home-hero__features" aria-label="Server features">
                <li>
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="5" y="5" width="14" height="14" rx="2" />
                        <path d="M9 9h6v6H9zM9 2v3m6-3v3M9 19v3m6-3v3M2 9h3m-3 6h3m14-6h3m-3 6h3" />
                    </svg>
                    <span><strong>CPU</strong><small>Compute power</small></span>
                </li>
                <li>
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M7 3h10v18H7zM10 7h4m-4 4h4m-4 4h4m-4 4h4" />
                    </svg>
                    <span><strong>Memory</strong><small>Room to scale</small></span>
                </li>
                <li>
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="2" />
                        <path d="M7 15h.01M11 15h.01M15 15h2M7 9h10" />
                    </svg>
                    <span><strong>Storage</strong><small>Fast and flexible</small></span>
                </li>
            </ul>
        </div>
    </div>
</section>