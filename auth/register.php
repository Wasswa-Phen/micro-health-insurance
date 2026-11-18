<?php
/**
 * auth/register.php — New Client Registration Page
 * Wired to auth/fetch_data.php ($pharmacies) & POST auth/register_process.php
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';

$selectedPharmacy = old('pharmacy_id');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Client — AfyaMicro Kampala</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="access-page">
    <aside class="access-aside">
        <?= uiLogo('../index.php', true) ?>
        <div class="access-message">
            <span class="aside-icon"><?= uiIcon('shield', 31) ?></span>
            <h1>Health security that grows with every small save.</h1>
            <p>
                Join Kampala residents building reliable medicine cover, one flexible contribution at a
                time.
            </p>
            <div class="aside-proof">
                <div class="avatar-stack">
                    <i>MK</i>
                    <i>AN</i>
                    <i>+</i>
                </div>
                <span>
                    <b>Trusted across Kampala</b>
                    <small>Kisenyi · Kawempe · Katwe</small>
                </span>
            </div>
        </div>
        <small>© <?= date('Y') ?> AfyaMicro Kampala · Your health, within reach.</small>
    </aside>

    <main class="access-main">
        <a class="back-link" href="../index.php">← Back to home</a>

        <div class="access-card">
            <div class="access-title">
                <span class="mobile-logo"><?= uiLogo('../index.php') ?></span>
                <h2>Create your cover</h2>
                <p>Start saving for better health in just a minute.</p>
            </div>

            <div class="tabs two-tabs">
                <a href="login.php">Login</a>
                <a class="active" href="register.php">New client</a>
            </div>

            <?= uiAlert($alert) ?>

            <form method="POST" action="register_process.php">
                <?= csrfField() ?>

                <label>
                    Full name
                    <div class="input-wrap">
                        <?= uiIcon('user', 19) ?>
                        <input
                            type="text"
                            name="full_name"
                            required
                            minlength="3"
                            maxlength="120"
                            value="<?= old('full_name') ?>"
                            placeholder="e.g. Musa Kato"
                            autocomplete="name"
                        >
                    </div>
                </label>

                <label>
                    Phone number
                    <div class="input-wrap phone-input">
                        <span>UG</span>
                        <input
                            type="tel"
                            name="phone"
                            required
                            maxlength="13"
                            inputmode="numeric"
                            pattern="(\+?256|0)7[0-9]{8}"
                            value="<?= old('phone') ?>"
                            placeholder="0701555111"
                            autocomplete="tel"
                        >
                    </div>
                </label>

                <label>
                    Uganda National ID (NIN) <em>(optional)</em>
                    <div class="input-wrap">
                        <?= uiIcon('clipboard', 18) ?>
                        <input
                            type="text"
                            name="nin"
                            maxlength="14"
                            pattern="[A-Za-z0-9]{14}"
                            value="<?= old('nin') ?>"
                            placeholder="e.g. CM88021145PQRA"
                        >
                    </div>
                </label>

                <label>
                    Preferred home pharmacy
                    <select name="pharmacy_id" required>
                        <option value="">Select partner pharmacy...</option>
                        <?php foreach ($pharmacies as $p): ?>
                            <?php $pid = (string) (int) $p['pharmacy_id']; ?>
                            <option value="<?= $pid ?>" <?= $selectedPharmacy === $pid ? 'selected' : '' ?>>
                                <?= e($p['name']) ?> — <?= e($p['location']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <div class="field-row">
                    <label>
                        Password
                        <div class="input-wrap">
                            <?= uiIcon('lock', 18) ?>
                            <input
                                type="password"
                                name="password"
                                required
                                minlength="6"
                                placeholder="Min 6 chars"
                                autocomplete="new-password"
                            >
                            <button type="button" data-toggle-password aria-label="Show password" aria-pressed="false">
                                <?= uiIcon('eye', 18) ?>
                            </button>
                        </div>
                    </label>

                    <label>
                        Confirm password
                        <div class="input-wrap">
                            <?= uiIcon('lock', 18) ?>
                            <input
                                type="password"
                                name="confirm_password"
                                required
                                minlength="6"
                                placeholder="Repeat password"
                                autocomplete="new-password"
                            >
                            <button type="button" data-toggle-password aria-label="Show password" aria-pressed="false">
                                <?= uiIcon('eye', 18) ?>
                            </button>
                        </div>
                    </label>
                </div>

                <div class="policy-note">
                    <?= uiIcon('shield') ?>
                    <div>
                        <b>Your policy ID is created automatically</b>
                        <p>
                            After registration, you’ll receive a unique ID like
                            <strong>MHI-KLA-XXXX</strong> and enter your wallet straight away.
                        </p>
                    </div>
                </div>

                <button class="btn btn-primary full" type="submit">
                    Create free account <?= uiIcon('arrow') ?>
                </button>

                <p class="form-terms">
                    By registering, you agree to our Terms of Service and Privacy Policy.
                </p>
            </form>
        </div>
    </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>

