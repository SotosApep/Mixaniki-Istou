<?php
// session_start() must come before any session operations.
session_start();

// ============================================================
// auth/logout.php
// Destroys the current session and redirects to login.
// ============================================================

// Wipe all session data and the session cookie.
session_destroy();

// Redirect to the login page, then stop execution.
header('Location: login.php');
exit;
