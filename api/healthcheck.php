<?php
// GET /api/healthcheck.php
//
// Proves the whole stack is wired up: Apache is serving PHP, config.php exists,
// and MySQL accepts the app credentials. Hit this first after deploying —
// before debugging any real endpoint.
//
// Expected: { "php": "8.x.x", "database": "connected", "error": "" }

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

$pdo->query('SELECT 1');

sendJson([
    'php'      => PHP_VERSION,
    'database' => 'connected',
    'error'    => '',
]);
