<?php
// POST /api/deleteContact.php
//
// send:     { "id": 1 }
// returns:  { "error": "" }
//
// TODO: DELETE ... WHERE ContactID = ? AND UserID = ?
// Same rule as editContact: the UserID clause is what stops cross-user deletes.

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

$userId = requireLogin();

sendError('Not implemented');
