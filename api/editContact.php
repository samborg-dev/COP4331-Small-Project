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

sendError('Not implemented');
