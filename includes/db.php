<?php
// ============================================================
// includes/db.php
// Creates and returns a PDO database connection.
// Include this file with require_once wherever DB access is needed.
// ============================================================

// -- Database credentials --
// Adjust these values to match your local MySQL setup.
define('DB_HOST', 'localhost');
define('DB_NAME', 'mixaniki_istou');
define('DB_USER', 'root');      // change to your MySQL username
define('DB_PASS', '');          // change to your MySQL password
define('DB_CHARSET', 'utf8mb4');

$dsn = 'mysql:host=' . DB_HOST
     . ';dbname='    . DB_NAME
     . ';charset='   . DB_CHARSET;

$options = [
    // Throw exceptions on database errors instead of silent failures.
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

    // Return rows as associative arrays by default (no numeric indices).
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

    // Disable emulated prepared statements for true server-side preparation.
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // NEVER expose $e->getMessage() — it can leak credentials or schema details.
    die('Σφάλμα σύνδεσης με τη βάση δεδομένων. Παρακαλώ δοκιμάστε αργότερα.');
}
// $pdo is now available to any file that does require_once __DIR__ . '/../includes/db.php';
