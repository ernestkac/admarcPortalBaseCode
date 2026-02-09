<?php
// Harden session behavior
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');

// Session configuration: expire after inactivity
$session_lifetime = 600; // seconds (10 minutes).

// Make PHP respect the lifetime for garbage collection
ini_set('session.gc_maxlifetime', (string)$session_lifetime);

// Use a cookie lifetime that matches inactivity timeout and harden cookie flags
$cookieParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => $session_lifetime,
    'path'     => $cookieParams['path'] ?? '/',
    'domain'   => $cookieParams['domain'] ?? '',
    'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();

// Inactivity timeout handling
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $session_lifetime)) {
    // Inactivity timeout reached: clear and restart session (fresh)
    session_unset();
    session_destroy();

    // Start a new session so page can continue (CSRF token etc. will be regenerated)
    session_start();
    session_regenerate_id(true);
}

// Update last-activity timestamp
$_SESSION['LAST_ACTIVITY'] = time();

// Regenerate session ID periodically to mitigate fixation (optional)
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} elseif (time() - $_SESSION['CREATED'] > 600) { // regenerate every 10 minutes
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}
// ...existing code...