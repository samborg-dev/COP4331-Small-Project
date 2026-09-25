<?php
// POST /api/searchContacts.php
//
// send:     { "search": "smi" }
// returns:  { "results": [ {"id":1,"firstName":"John","lastName":"Smith",
//                           "phone":"407-555-0100","email":"j@x.com"} ], "error": "" }
//
// An empty search string matches everything, which is how contacts.js loads the
// full list on page load.

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

$userId = requireLogin();
$input = getJsonInput();

// wraps the name with '%' which allows for partial search
$searchTerm = '%' . ($input['search'] ?? '') . '%';

// WHERE UserID = ? is what keeps one user out of another user's contacts.
$searchQuery = $pdo->prepare(
    "SELECT ContactID, FirstName, LastName, Phone, Email FROM Contacts
     WHERE UserID = ?
       AND (CONCAT(FirstName, ' ', LastName) LIKE ? OR Phone LIKE ? OR Email LIKE ?)
     ORDER BY LastName, FirstName"
);
$searchQuery->execute([$userId, $searchTerm, $searchTerm, $searchTerm]);

// The front end renders its own "no contacts" row, so no matches is an empty
// list and not an error.
$results = [];
foreach ($searchQuery->fetchAll() as $row) {
    $results[] = [
        'id'        => (int) $row['ContactID'],
        'firstName' => $row['FirstName'],
        'lastName'  => $row['LastName'],
        'phone'     => $row['Phone'],
        'email'     => $row['Email'],
    ];
}

sendJson(['results' => $results, 'error' => '']);
