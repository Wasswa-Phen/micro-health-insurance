<?php
/**
 * index.php — Public Landing Page
 * Pure HTML5 + PHP 8 presentation wired to auth/login.php & auth/register.php.
 */

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/ui.php';

$alert    = statusMessage();
$loggedIn = isLoggedIn();

$steps = [
    ['user',      '01', 'Register free',         'Sign up with your phone number in under two minutes. Your unique policy ID is created instantly.'],
    ['phone',     '02', 'Save from UGX 2,000',   'Make small, flexible deposits with MTN MoMo, Airtel Money or a local cash agent.'],
    ['clipboard', '03', 'Claim your prescription','Visit a partner pharmacy, show your policy ID, and use your available health cover.'],
];

$kampalaPartners = [
    ['Kisenyi Care Pharmacy',     'KISENYI · KAMPALA CENTRAL'],
    ['Kawempe Community Chemist', 'KAWEMPE · BOMBO ROAD'],
    ['Katwe Hope Pharmacy',       'KATWE · MAKINDYE'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AfyaMicro Kampala — Micro Health Insurance</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="public-page">
    <header class="public-header">
        <div class="container header-inner">
            <?= uiLogo('index.php') ?>
            <nav id="public-nav" aria-label="Primary navigation">
                <a href="#how-it-works">How it works</a>
                <a href="#partner-pharmacies">Partner pharmacies</a>
                <?php if ($loggedIn): ?>
                    <a class="btn btn-primary btn-small" href="<?= e(dashboardUrl($_SESSION['role'])) ?>">My portal</a>
                    <a href="auth/logout.php">Sign out</a>
                <?php else: ?>
                    <a class="btn btn-secondary btn-small" href="auth/login.php">Portal login</a>
                <?php endif; ?>
            </nav>
            <button type="button" class="menu-button" data-menu-toggle aria-expanded="false" aria-controls="public-nav" aria-label="Toggle menu">
                <?= uiIcon('menu') ?>
            </button>
        </div>
    </header>

    <?php if ($alert): ?>
        <div class="container public-alert-wrap">
            <?= uiAlert($alert) ?>
        </div>
    <?php endif; ?>

    <main>
        <section class="hero">
            <div class="container hero-grid">
                <div class="hero-copy">
                    <div class="eyebrow">
                        <?= uiIcon('shield', 17) ?> Community health cover, made simple
                    </div>
                    <h1>
                        Save small. Get your medicine <span>when you need it.</span>
                    </h1>
                    <p>
                        Build affordable health cover from as little as UGX 2,000 using MTN MoMo or Airtel
                        Money, then claim prescriptions at trusted pharmacies near you.
                    </p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="auth/register.php">
                            Register as client <?= uiIcon('arrow') ?>
                        </a>
                        <a class="btn btn-secondary" href="auth/login.php">
                            Portal login
                        </a>
                    </div>
                    <div class="trust-row">
                        <span><?= uiIcon('check', 15) ?> No paperwork</span>
                        <span><?= uiIcon('check', 15) ?> No monthly lock-in</span>
                        <span><?= uiIcon('check', 15) ?> Secure mobile money</span>
                    </div>
                </div>

                <div class="hero-visual">
                    <div class="visual-glow"></div>
                    <div class="phone-card">
                        <div class="phone-top">
                            <span>Health wallet</span>
                            <?= uiIcon('shield') ?>
                        </div>
                        <small>AVAILABLE COVER</small>
                        <strong>UGX 12,000</strong>
                        <div class="wallet-progress"><i></i></div>
                        <div class="wallet-meta">
                            <span>Policy</span>
                            <b>MHI-KLA-1001</b>
                        </div>
                        <a class="phone-action" href="auth/register.php">
                            <span><?= uiIcon('plus', 18) ?></span>
                            <div>
                                <small>QUICK SAVE</small>
                                <b>Add UGX 2,000</b>
                            </div>
                            <?= uiIcon('chevron') ?>
                        </a>
                    </div>

                    <div class="float-card float-one">
                        <span class="float-icon"><?= uiIcon('check') ?></span>
                        <div>
                            <b>Cover active</b>
                            <small>Ready when you need it</small>
                        </div>
                    </div>

                    <div class="float-card float-two">
                        <span class="float-icon amber"><?= uiIcon('store') ?></span>
                        <div>
                            <b>3 pharmacies</b>
                            <small>Across Kampala</small>
                        </div>
                    </div>

                    <span class="plus-art plus-a">+</span>
                    <span class="plus-art plus-b">+</span>
                </div>
            </div>
        </section>

        <section class="how-section" id="how-it-works">
            <div class="container">
                <div class="section-heading">
                    <div>
                        <span class="section-kicker">HOW IT WORKS</span>
                        <h2>Health protection in three simple steps</h2>
                    </div>
                    <p>
                        No complicated forms. Start saving today and use your cover whenever you need
                        prescribed medicine.
                    </p>
                </div>
                <div class="steps-grid">
                    <?php foreach ($steps as [$icon, $num, $title, $text]): ?>
                        <article class="step-card">
                            <div class="step-top">
                                <span class="step-icon"><?= uiIcon($icon) ?></span>
                                <b><?= e($num) ?></b>
                            </div>
                            <h3><?= e($title) ?></h3>
                            <p><?= e($text) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section class="partners" id="partner-pharmacies">
            <div class="container partner-inner">
                <div>
                    <span class="section-kicker">TRUSTED LOCALLY</span>
                    <h2>Care close to home</h2>
                    <p>Use your cover at verified community pharmacies.</p>
                </div>
                <div class="partner-list">
                    <?php foreach ($kampalaPartners as [$name, $area]): ?>
                        <div class="partner-item">
                            <span><?= uiIcon('store') ?></span>
                            <div>
                                <b><?= e($name) ?></b>
                                <small><i></i> VERIFIED PARTNER · <?= e($area) ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="container footer-inner">
            <?= uiLogo('index.php', true) ?>
            <p>Affordable health cover for every Kampala family.</p>
            <span>Need help? <b>Call 0800 222 018</b></span>
        </div>
    </footer>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>

