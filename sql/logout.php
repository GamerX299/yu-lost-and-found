<?php
/** logout.php - ends the login (clears the session data) and returns to the home page. */
require_once __DIR__ . '/config.php';

$_SESSION = [];                        // forget who was logged in
session_regenerate_id(true);           // new session id for the next visitor
flash('success', 'You have been logged out.');
redirect('../html/index.php');
