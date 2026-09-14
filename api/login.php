<?php
// POST /api/login.php
//
// send:     { "login": "sam", "password": "hunter2" }
// returns:  { "id": 4, "firstName": "Sam", "lastName": "Borges", "error": "" }
//
// TODO: look up by login, password_verify(), set $_SESSION['userId'].
// Return the SAME generic "Invalid username or password" for a bad username and
// a bad password — a specific message tells an attacker which logins are real.

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

sendError('Not implemented');
