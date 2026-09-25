<?php
// POST /api/editContact.php
//
// send:     { "id": 1, "firstName": "", "lastName": "", "phone": "", "email": "" }
// returns:  { "error": "" }
//
// TODO: UPDATE ... WHERE ContactID = ? AND UserID = ?
// The `AND UserID = ?` is not optional — without it, user A can edit user B's
// contacts by guessing an ID.

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

$userId = requireLogin();

$input = getJsonInput();

$phone = normalizePhone($input['phone'] ?? '');
if ($phone === false) {
    sendError('Phone must be 10 digits, like 123-123-1234');
}

$stmt = $pdo->prepare("UPDATE Contacts SET FirstName = ?, LastName = ?, Phone = ?, Email = ? WHERE ContactID = ? AND UserID = ?");

$stmt->execute([$input['firstName'], $input['lastName'], $phone, $input['email'], $input['id'], $userId]);

if ($stmt->rowCount() === 0) {
    sendError('Contact not found');
}

sendJson(['error' => '']);
