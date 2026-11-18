<?php
/**
 * config/ui.php
 * ---------------------------------------------------------------------
 * Pure presentation helpers (inline SVG icons, brand logo, alert box,
 * status badges, and portal shell wrapper). Contains ZERO SQL queries.
 * ---------------------------------------------------------------------
 */

declare(strict_types=1);

/** Return an inline 24x24 stroke SVG matching the Figma Make icon set. */
function uiIcon(string $name, int $size = 20): string
{
    static $paths = [
        'arrow'     => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
        'bank'      => '<path d="m3 10 9-6 9 6"/><path d="M5 10v8M9 10v8M15 10v8M19 10v8M3 21h18"/>',
        'check'     => '<path d="m5 12 4 4L19 6"/>',
        'chevron'   => '<path d="m9 18 6-6-6-6"/>',
        'clipboard' => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4.5V3h6v1.5M9 11h6M9 15h4"/>',
        'close'     => '<path d="m6 6 12 12M18 6 6 18"/>',
        'download'  => '<path d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14"/>',
        'eye'       => '<path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="2.5"/>',
        'heart'     => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.7-7.5 1.1-1.1a5.5 5.5 0 0 0 0-7.8Z"/>',
        'home'      => '<path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10M9 20v-6h6v6"/>',
        'lock'      => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'menu'      => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'phone'     => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.4 2.1L8.1 10a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.6 1.9Z"/>',
        'plus'      => '<path d="M12 5v14M5 12h14"/>',
        'print'     => '<path d="M6 9V3h12v6M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="7"/>',
        'receipt'   => '<path d="M5 3v18l3-2 4 2 4-2 3 2V3l-3 2-4-2-4 2-3-2Z"/><path d="M9 10h6M9 14h6"/>',
        'search'    => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'shield'    => '<path d="M12 22s8-3.5 8-10V5l-8-3-8 3v7c0 6.5 8 10 8 10Z"/><path d="m9 12 2 2 4-5"/>',
        'store'     => '<path d="M4 10v10h16V10M3 4h18l-2 6a3 3 0 0 1-4 0 3 3 0 0 1-6 0 3 3 0 0 1-4 0L3 4Z"/><path d="M9 20v-5h6v5"/>',
        'user'      => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'users'     => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
        'wallet'    => '<path d="M3 6h15a3 3 0 0 1 3 3v10H5a2 2 0 0 1-2-2V6Z"/><path d="M3 6a3 3 0 0 1 3-3h11v3M16 11h5v5h-5a2.5 2.5 0 0 1 0-5Z"/>',
    ];

    $body = $paths[$name] ?? '';
    return '<svg aria-hidden="true" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $body . '</svg>';
}

/** Render the AfyaMicro Kampala brand mark. */
function uiLogo(string $href, bool $inverse = false): string
{
    $cls = $inverse ? 'logo logo-inverse' : 'logo';
    return '<a class="' . $cls . '" href="' . e($href) . '" aria-label="AfyaMicro Kampala home">'
        . '<span class="logo-mark">' . uiIcon('heart', 21) . '<span class="logo-plus">+</span></span>'
        . '<span><b>Afya</b>Micro <small>KAMPALA</small></span>'
        . '</a>';
}

/** Render a dismissible flash / status alert banner from $alert. */
function uiAlert(?array $alert, string $extraDetail = ''): string
{
    if (!$alert) {
        return '';
    }
    $type = in_array($alert['type'], ['success', 'error', 'info'], true) ? $alert['type'] : 'info';
    $icon = $type === 'success' ? 'check' : ($type === 'error' ? 'close' : 'shield');
    $detail = $extraDetail !== '' ? ' <strong>' . e($extraDetail) . '</strong>' : '';

    return '<div class="alert alert-' . e($type) . '" role="alert">'
        . uiIcon($icon, 16)
        . '<span>' . e($alert['message']) . $detail . '</span>'
        . '<button type="button" class="alert-dismiss" data-alert-dismiss aria-label="Dismiss">' . uiIcon('close', 14) . '</button>'
        . '</div>';
}

/** Extract 2 uppercase initials from a person's full name. */
function uiInitials(string $fullName): string
{
    $parts = preg_split('/\s+/', trim($fullName)) ?: [];
    $parts = array_values(array_filter($parts, static fn ($p) => $p !== ''));
    if (count($parts) === 0) {
        return 'AM';
    }
    if (count($parts) === 1) {
        return strtoupper(mb_substr($parts[0], 0, 2));
    }
    return strtoupper(mb_substr($parts[0], 0, 1) . mb_substr($parts[count($parts) - 1], 0, 1));
}

/** Format a MySQL datetime/date safely for display. */
function uiDate(?string $datetime, string $format = 'd M Y · h:i A'): string
{
    if (!$datetime) {
        return '—';
    }
    $ts = strtotime($datetime);
    return $ts === false ? e($datetime) : date($format, $ts);
}

/** Render a status badge matching the Figma palette. */
function uiStatusBadge(string $status, bool $withDot = false): string
{
    $s = strtolower(trim($status));
    $cls = match ($s) {
        'active', 'completed', 'approved', 'success' => 'success',
        'pending', 'partial'                         => 'warning',
        default                                      => 'danger',
    };
    $dot = $withDot ? '<i></i> ' : '';
    return '<span class="badge ' . $cls . '">' . $dot . e(strtoupper($status)) . '</span>';
}

/** Render the opening HTML5 + portal sidebar + top header shell. */
function uiPortalStart(array $cfg): void
{
    $pageTitle  = $cfg['pageTitle'] ?? APP_NAME;
    $title      = $cfg['title'] ?? '';
    $subtitle   = $cfg['subtitle'] ?? '';
    $userName   = $cfg['userName'] ?? ($_SESSION['full_name'] ?? 'User');
    $roleLabel  = $cfg['roleLabel'] ?? ucfirst((string) ($_SESSION['role'] ?? 'Portal'));
    $navItems   = $cfg['navItems'] ?? [];
    $homeHref   = $cfg['homeHref'] ?? '../index.php';
    $logoutHref = $cfg['logoutHref'] ?? '../auth/logout.php';
    $initials   = uiInitials($userName);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> — AfyaMicro Kampala</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="portal-shell">
    <aside class="sidebar" id="portal-sidebar">
        <div class="sidebar-top">
            <?= uiLogo($homeHref, true) ?>
            <button type="button" class="sidebar-close" data-sidebar-close aria-label="Close navigation">
                <?= uiIcon('close') ?>
            </button>
        </div>
        <p>WORKSPACES</p>
        <nav aria-label="Portal navigation">
            <?php foreach ($navItems as $item): ?>
                <a href="<?= e($item['href']) ?>" class="<?= !empty($item['active']) ? 'active' : '' ?>">
                    <?= uiIcon($item['icon']) ?>
                    <span><?= e($item['label']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-help">
            <?= uiIcon('heart') ?>
            <b>Need assistance?</b>
            <span>Our support team is here.</span>
            <strong>0800 222 018</strong>
        </div>
        <a class="sidebar-user" href="<?= e($logoutHref) ?>" title="Sign out of <?= e($userName) ?>">
            <span><?= e($initials) ?></span>
            <div>
                <b><?= e($userName) ?></b>
                <small>Sign out</small>
            </div>
            <?= uiIcon('chevron', 17) ?>
        </a>
    </aside>
    <button type="button" class="nav-scrim" id="nav-scrim" aria-label="Close navigation"></button>
    <div class="portal-content">
        <header class="portal-header">
            <button type="button" class="mobile-menu" data-sidebar-open aria-label="Open navigation">
                <?= uiIcon('menu') ?>
            </button>
            <div>
                <h1><?= e($title) ?></h1>
                <p><?= e($subtitle) ?></p>
            </div>
            <div class="header-actions">
                <span class="role-chip"><?= uiIcon('shield', 14) ?> <?= e($roleLabel) ?></span>
                <span class="header-avatar" title="<?= e($userName) ?>"><?= e($initials) ?></span>
            </div>
        </header>
        <main class="portal-main">
    <?php
}

/** Close the portal shell and include assets/js/app.js. */
function uiPortalEnd(): void
{
    ?>
        </main>
    </div>
</div>
<script src="../assets/js/app.js"></script>
</body>
</html>
    <?php
}

