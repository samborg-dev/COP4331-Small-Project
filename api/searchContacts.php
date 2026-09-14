<?php
// POST /api/searchContacts.php
//
// send:     { "search": "smi" }
// returns:  { "results": [ { "id": 1, "firstName": "John", "lastName": "Smith",
//                            "phone": "407-555-0100", "email": "j@x.com" } ],
//             "error": "" }
//
// This is the 5-point rubric item. Requirements:
//   - partial match — wrap the term in % on BOTH sides so "smi" finds "Smith"
//   - scoped to the session user with WHERE UserID = ?
//   - an empty search term becomes %% and returns everything (that's intended —
//     it's how the full list loads)
//   - prepared statement, never string concatenation

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

$userId = requireLogin();

sendError('Not implemented');
