<?php
/**
 * auth/login.php — Portal Login Page
 * Wired to auth/fetch_data.php & POST auth/login_process.php
 */

declare(strict_types=1);

require_once __DIR__ . '/fetch_data.php';
require_once __DIR__ . '/../config/ui.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Login — AfyaMicro Kampala</title>
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
                <h2>Welcome back</h2>
                <p>Log in securely to your AfyaMicro portal.</p>
            </div>

            <div class="tabs two-tabs">
                <a class="active" href="login.php">Login</a>
                <a href="register.php">New client</a>
            </div>

            <?= uiAlert($alert) ?>

            <form method="POST" action="login_process.php">
                <?= csrfField() ?>

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
                    Password
                    <div class="input-wrap">
                        <?= uiIcon('lock', 18) ?>
                        <input
                            type="password"
                            name="password"
                            required
                            minlength="6"
                            placeholder="Enter your password"
                            autocomplete="current-password"
                        >
                        <button type="button" data-toggle-password aria-label="Show password" aria-pressed="false">
                            <?= uiIcon('eye', 18) ?>
                        </button>
                    </div>
                </label>

                <button class="btn btn-primary full" type="submit">
                    Log in securely <?= uiIcon('arrow') ?>
                </button>

                <p class="form-terms">
                    Your portal (Client, Pharmacy Attendant, or Admin) opens automatically based on your account.
                </p>
            </form>

            <div class="demo-hint">
                <b>Demo accounts (password: <code>password</code>):</b><br>
                Client: <code>0701555111</code> · Attendant (Kisenyi): <code>0772100101</code> · Admin: <code>0700000001</code>
            </div>
        </div>
    </main>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>

