<?php
// api/stats.php
// GET → aggregate statistics about the system

require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Overall counts
$overall = $pdo->query(
    "SELECT
        COUNT(*)                        AS total_records,
        SUM(status = 'appointed')       AS appointed,
        SUM(status = 'pending')         AS pending,
        SUM(status = 'rejected')        AS rejected,
        COUNT(DISTINCT specialty)       AS specialties,
        COUNT(DISTINCT list_year)       AS years_covered
     FROM appointees"
)->fetch();

// User counts
$users = $pdo->query(
    "SELECT
        COUNT(*)                              AS total_users,
        SUM(role = 'candidate')               AS candidates,
        SUM(role = 'admin')                   AS admins
     FROM users"
)->fetch();

// Breakdown by specialty
$bySpecialty = $pdo->query(
    "SELECT
        specialty,
        COUNT(*)                    AS total,
        SUM(status = 'appointed')   AS appointed,
        SUM(status = 'pending')     AS pending,
        SUM(status = 'rejected')    AS rejected
     FROM appointees
     GROUP BY specialty
     ORDER BY total DESC"
)->fetchAll();

// Breakdown by year
$byYear = $pdo->query(
    "SELECT
        list_year,
        COUNT(*)                    AS total,
        SUM(status = 'appointed')   AS appointed,
        SUM(status = 'pending')     AS pending,
        SUM(status = 'rejected')    AS rejected
     FROM appointees
     GROUP BY list_year
     ORDER BY list_year DESC"
)->fetchAll();

http_response_code(200);
echo json_encode([
    'overview'     => $overall,
    'users'        => $users,
    'by_specialty' => $bySpecialty,
    'by_year'      => $byYear,
]);
