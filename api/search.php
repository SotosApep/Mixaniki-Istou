<?php
// api/search.php
// GET ?keyword=X           → search appointees by name or specialty
// GET ?keyword=X&status=Y  → also filter by status
// GET ?keyword=X&year=Y    → also filter by year

require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
if ($keyword === '') {
    http_response_code(400);
    echo json_encode(['error' => 'keyword parameter is required']);
    exit;
}

// Build query with optional filters
$sql    = "SELECT * FROM appointees WHERE (full_name LIKE :kw OR specialty LIKE :kw)";
$params = [':kw' => '%' . $keyword . '%'];

$allowed_statuses = ['pending', 'appointed', 'rejected'];
if (!empty($_GET['status']) && in_array($_GET['status'], $allowed_statuses)) {
    $sql .= " AND status = :status";
    $params[':status'] = $_GET['status'];
}

if (!empty($_GET['year']) && is_numeric($_GET['year'])) {
    $sql .= " AND list_year = :year";
    $params[':year'] = (int) $_GET['year'];
}

$sql .= " ORDER BY list_year DESC, rank_position ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

http_response_code(200);
echo json_encode([
    'keyword' => $keyword,
    'count'   => count($results),
    'results' => $results,
]);
