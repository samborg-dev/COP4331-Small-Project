<?php
// POST /api/register.php
//
// send:     { "firstName": "", "lastName": "", "phone": "", "login": "", "password": "" }
// returns:  { "id": 0, "firstName": "", "lastName": "", "error": "" }
//
// On success the new user is logged in, same as login.php.

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

$input = getJsonInput();
$firstName = trim($input['firstName'] ?? '');
$lastName  = trim($input['lastName'] ?? '');
$phone     = trim($input['phone'] ?? '');
$login     = trim($input['login'] ?? '');
$password  = $input['password'] ?? '';

if ($firstName === '' || $lastName === '' || $login === '' || $password === '') {
    sendError('First name, last name, username, and password are required');
}

$stmt = $pdo->prepare('SELECT 1 FROM Users WHERE UserName = ?');
$stmt->execute([$login]);
if ($stmt->fetch()) {
    sendError('That username is already taken');
}

$stmt = $pdo->prepare(
    'INSERT INTO Users (FirstName, LastName, Phone, UserName, PasswordHash) VALUES (?, ?, ?, ?, ?)'
);

try {
    $stmt->execute([$firstName, $lastName, $phone === '' ? null : $phone, $login,
                    password_hash($password, PASSWORD_DEFAULT)]);
} catch (PDOException $e) {
    // 23000 = the UNIQUE key on UserName, if two signups race past the check above.
    if ($e->getCode() === '23000') {
        sendError('That username is already taken');
    }
    throw $e;
}

$userId = (int) $pdo->lastInsertId();

session_regenerate_id(true);
$_SESSION['userId'] = $userId;

sendJson([
    'id'        => $userId,
    'firstName' => $firstName,
    'lastName'  => $lastName,
    'error'     => '',
]);
