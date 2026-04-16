<?php
// ============================================================
//  PDO Singleton – includes/db.php
// ============================================================

require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            DB_HOST, DB_NAME, DB_CHARSET
        );
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log and show a generic error
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => 'Database connection error.']));
        }
    }

    return $pdo;
}
