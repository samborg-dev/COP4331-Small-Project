<?php
// POST /api/logout.php
//
// send:     {}
// returns:  { "error": "" }

require_once __DIR__ . '/helpers.php';

// Clear the session data, then the session itself, so the old cookie can't be
// reused to get back in.
$_SESSION = [];
session_destroy();

sendJson(['error' => '']);
