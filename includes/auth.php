<?php
// ============================================================
//  Authentication helpers – includes/auth.php
// ============================================================

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session.php';

/**
 * Register a new customer.
 * Returns ['success'=>bool, 'message'=>string].
 */
function registerUser(string $name, string $email, string $phone, string $password): array {
    // Validate
    $name  = trim($name);
    $email = trim(strtolower($email));
    $phone = trim($phone);

    if ($name === '' || $email === '' || $password === '') {
        return ['success' => false, 'message' => 'Name, email and password are required.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email address.'];
    }
    if (strlen($password) < 8) {
        return ['success' => false, 'message' => 'Password must be at least 8 characters.'];
    }

    $pdo = getDB();

    // Check duplicate email
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'message' => 'Email already registered.'];
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);

    $ins = $pdo->prepare(
        'INSERT INTO users (name, email, phone, password_hash, role) VALUES (?, ?, ?, ?, ?)'
    );
    $ins->execute([$name, $email, $phone, $hash, 'customer']);

    return ['success' => true, 'message' => 'Registration successful. Please log in.'];
}

/**
 * Authenticate a user and populate the session.
 * Returns ['success'=>bool, 'message'=>string].
 */
function loginUser(string $email, string $password): array {
    $email = trim(strtolower($email));

    if ($email === '' || $password === '') {
        return ['success' => false, 'message' => 'Email and password are required.'];
    }

    $pdo  = getDB();
    $stmt = $pdo->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'message' => 'Invalid email or password.'];
    }

    // Populate session
    session_regenerate_id(true);
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_role'] = $user['role'];

    return ['success' => true, 'message' => 'Login successful.'];
}

/**
 * Destroy the current session.
 */
function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}
