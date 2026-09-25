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

// Phone numbers are stored as 123-123-1234. Accepts anything with 10 digits in
// it and returns that format; returns null for an empty phone (it's optional)
// and false when it isn't 10 digits, so the caller can send an error.
// The browser formats the field as you type, but the API is also called
// directly, so the format is enforced here too.
function normalizePhone(string $phone)
{
    $phone = trim($phone);
    if ($phone === '') {
        return null;
    }

    $digits = preg_replace('/\D/', '', $phone);
    if (strlen($digits) !== 10) {
        return false;
    }

    return substr($digits, 0, 3) . '-' . substr($digits, 3, 3) . '-' . substr($digits, 6);
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
