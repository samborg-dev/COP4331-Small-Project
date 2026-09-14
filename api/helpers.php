<?php
// Shared helpers used by every endpoint.
// Coordinate before renaming anything here — the endpoints call these by name.

session_start();

// Read the request body and decode it. Returns [] if the body is missing or
// isn't valid JSON, so callers can validate fields without a null check first.
function getJsonInput(): array
{
    $raw = file_get_contents('php://input');
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

// Send an array as a JSON response and stop.
function sendJson(array $payload): void
{
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

// Every response carries an `error` field: "" means success.
function sendError(string $message): void
{
    sendJson(['error' => $message]);
}

// Guard for contact endpoints. The server decides who the user is, from the
// session — never trust a userId sent by the browser.
function requireLogin(): int
{
    if (empty($_SESSION['userId'])) {
        sendError('Not logged in');
    }
    return (int) $_SESSION['userId'];
}
