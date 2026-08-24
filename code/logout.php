<?php
/**
 * Ends the session and returns to the login page.
 */

require_once __DIR__ . '/includes/functions.php';

$_SESSION = [];
session_destroy();
session_start();

set_flash('info', 'You have been logged out successfully.');
redirect('login.php');
