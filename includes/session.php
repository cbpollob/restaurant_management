<?php
// ============================================================
//  Session management – includes/session.php
// ============================================================

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => false,   // set true in production (HTTPS)
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Regenerate session ID periodically to prevent fixation
if (!isset($_SESSION['_initiated'])) {
    session_regenerate_id(true);
    $_SESSION['_initiated'] = true;
}

/**
 * Redirect to login if the user is not authenticated.
 */
function requireLogin(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit;
    }
}

/**
 * Redirect to home if the user is not an admin.
 */
function requireAdmin(): void {
    requireLogin();
    if (($_SESSION['user_role'] ?? '') !== 'admin') {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

/**
 * Return true if any user is logged in.
 */
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

/**
 * Return true if the logged-in user is an admin.
 */
function isAdmin(): bool {
    return ($_SESSION['user_role'] ?? '') === 'admin';
}
