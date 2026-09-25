// Last updated: 2024-06-04
// By Arwa
<?php
// POST /api/addContact.php
//
// send:     { "firstName": "", "lastName": "", "phone": "", "email": "" }
// returns:  { "id": 0, "error": "" }
//

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

$userId = requireLogin();


$input = getJsonInput();

$stmt = $pdo->prepare("INSERT INTO Contacts (FirstName, LastName, Phone, Email, UserID) VALUES(? ,? ,? ,? ,?)");

$stmt->execute([$input['firstName'], $input['lastName'], $input['phone'], $input['email'], $userId]);

sendJson(['id' => (int) $pdo->lastInsertId(), 'error' => '']);


