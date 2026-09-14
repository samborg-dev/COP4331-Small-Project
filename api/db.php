<?php
// Shared PDO connection. Every endpoint does `require_once __DIR__ . '/db.php';`
// and then uses $pdo. Nobody duplicates this file.
//
// Note: this file prints nothing on success. Any stray echo here would end up
// in front of the JSON body of every response and break the client.

$config = require __DIR__ . '/config.php';

$dsn = "mysql:host={$config['host']};dbname={$config['name']};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    // Log the real reason, but don't leak it to the browser.
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}
