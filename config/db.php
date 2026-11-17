<?php
/**
 * config/db.php
 * ---------------------------------------------------------------------
 * Central bootstrap: PDO database connection, secure session start and
 * shared helper functions. Include at the top of every PHP page:
 *
 *     require_once __DIR__ . '/../config/db.php';
 *
 * NOTE: DB_PORT is 3307 to match this developer's local XAMPP MySQL.
 *       On a standard XAMPP install change it to 3306.
 * ---------------------------------------------------------------------
 */

declare(strict_types=1);

// ---------------------------------------------------------------------
// Application constants
// ---------------------------------------------------------------------
define('DB_HOST',    '127.0.0.1');
define('DB_PORT',    '3307');          // Standard XAMPP = 3306
define('DB_NAME',    'micro_health_db');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'Micro Health Insurance');
define('BASE_URL', '/micro-health-insurance');   // URL path of the project root

date_default_timezone_set('Africa/Kampala');

// ---------------------------------------------------------------------
// PDO connection
// ---------------------------------------------------------------------
$dsn = sprintf(
    'mysql:host=%s;port=%s;dbname=%s;charset=%s',
    DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
);

$pdoOptions = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,   // real prepared statements
    PDO::ATTR_STRINGIFY_FETCHES  => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdoOptions);
    $pdo->exec("SET time_zone = '+03:00'");
} catch (PDOException $e) {
    // Never leak connection details to the browser
    error_log('[MHI] Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Service temporarily unavailable. Please try again later.');
}

// ---------------------------------------------------------------------
// Secure session start (only once, and only if headers not yet sent)
// ---------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');

    session_name('MHI_SESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE_URL . '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---------------------------------------------------------------------
// Helper functions
// ---------------------------------------------------------------------

/**
 * Restrict a page to one role (or a list of roles).
 * Session keys set at login: user_id, role, full_name, pharmacy_id.
 *
 * @param string|array $role 'admin' | 'attendant' | 'client' (or array of these)
 */
function requireRole(string|array $role): void
{
    $allowed = (array) $role;

    if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
        setFlash('error', 'Please log in to continue.');
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }

    if (!in_array($_SESSION['role'], $allowed, true)) {
        http_response_code(403);
        exit('403 Forbidden: you do not have permission to access this page.');
    }
}

/**
 * Format an amount as Ugandan Shillings, e.g. 10500 -> "UGX 10,500".
 * UGX has no minor unit in everyday use, so decimals are dropped.
 */
function formatUGX(float|int|string|null $amount): string
{
    return 'UGX ' . number_format((float) ($amount ?? 0), 0, '.', ',');
}

/** HTML-escape output (use for every user-supplied value echoed into HTML). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Return (and lazily create) the CSRF token for the current session. */
function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Hidden input to drop inside every POST <form>. */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

/** Validate the CSRF token on POST requests; aborts with 419 on failure. */
function verifyCsrf(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals(csrfToken(), $sent)) {
        http_response_code(419);
        exit('Session expired or invalid request. Please go back and try again.');
    }
}

/** Store a one-time flash message ('success' | 'error' | 'info'). */
function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/** Retrieve and clear the flash message (returns null if none). */
function getFlash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

/** Validate a Ugandan mobile number in local format 07XXXXXXXX. */
function isValidUgPhone(string $phone): bool
{
    return (bool) preg_match('/^07[0-9]{8}$/', $phone);
}
