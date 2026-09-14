<?php
// POST /api/register.php
//
// send:     { "firstName": "", "lastName": "", "login": "", "password": "" }
// returns:  { "id": 0, "firstName": "", "lastName": "", "error": "" }
//
// TODO: validate fields are non-empty, reject a login that's already taken,
// password_hash() the password, INSERT, return the new user.

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';

sendError('Not implemented');
