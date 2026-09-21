<?php
// POST /api/login.php
//
// send:     { "login": "sam", "password": "hunter2" }
// returns:  { "id": 4, "firstName": "Sam", "lastName": "Borges", "error": "" }
//
// Return the SAME generic "Invalid username or password" for a bad username and
// a bad password — a specific message tells an attacker which logins are real.

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

$input = getJsonInput();
$login = trim($input['login'] ?? '');
$password = $input['password'] ?? '';

if ($login === '' || $password === '') {
    sendError('Username and password are required');
}

$stmt = $pdo->prepare('SELECT UserID, FirstName, LastName, PasswordHash FROM Users WHERE UserName = ?');
$stmt->execute([$login]);
$user = $stmt->fetch();

if (!$user || !password_verify($password, $user['PasswordHash'])) {
    sendJson(['id' => 0, 'firstName' => '', 'lastName' => '', 'error' => 'Invalid username or password']);
}

// New session ID on login so a pre-login session ID can't be reused.
session_regenerate_id(true);
$_SESSION['userId'] = (int) $user['UserID'];

sendJson([
    'id'        => (int) $user['UserID'],
    'firstName' => $user['FirstName'],
    'lastName'  => $user['LastName'],
    'error'     => '',
]);
