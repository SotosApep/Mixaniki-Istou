<?php
// api/appointee.php
// GET    ?id=N → get one appointee
// PUT    ?id=N → update an appointee
// DELETE ?id=N → delete an appointee

require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

// All methods require an id parameter
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'A valid id parameter is required']);
    exit;
}

// Helper: fetch one record or return 404
function fetchAppointee(PDO $pdo, int $id): ?array {
    $stmt = $pdo->prepare("SELECT * FROM appointees WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

// ── GET ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $record = fetchAppointee($pdo, $id);
    if (!$record) {
        http_response_code(404);
        echo json_encode(['error' => "Appointee with id $id not found"]);
        exit;
    }
    http_response_code(200);
    echo json_encode($record);
    exit;
}

// ── PUT ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $record = fetchAppointee($pdo, $id);
    if (!$record) {
        http_response_code(404);
        echo json_encode(['error' => "Appointee with id $id not found"]);
        exit;
    }

    $body = json_decode(file_get_contents('php://input'), true);
    if (!$body) {
        http_response_code(400);
        echo json_encode(['error' => 'Request body must be valid JSON']);
        exit;
    }

    $allowed_statuses = ['pending', 'appointed', 'rejected'];
    $status = $body['status'] ?? $record['status'];
    if (!in_array($status, $allowed_statuses)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status. Must be pending, appointed, or rejected']);
        exit;
    }

    $stmt = $pdo->prepare(
        "UPDATE appointees
         SET full_name     = :fn,
             specialty     = :sp,
             rank_position = :rp,
             list_year     = :ly,
             list_period   = :lp,
             status        = :st
         WHERE id = :id"
    );
    $stmt->execute([
        ':fn'  => $body['full_name']     ?? $record['full_name'],
        ':sp'  => $body['specialty']     ?? $record['specialty'],
        ':rp'  => (int) ($body['rank_position'] ?? $record['rank_position']),
        ':ly'  => (int) ($body['list_year']      ?? $record['list_year']),
        ':lp'  => $body['list_period']   ?? $record['list_period'],
        ':st'  => $status,
        ':id'  => $id,
    ]);

    http_response_code(200);
    echo json_encode(['message' => 'Appointee updated']);
    exit;
}

// ── DELETE ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $record = fetchAppointee($pdo, $id);
    if (!$record) {
        http_response_code(404);
        echo json_encode(['error' => "Appointee with id $id not found"]);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM appointees WHERE id = :id");
    $stmt->execute([':id' => $id]);

    http_response_code(200);
    echo json_encode(['message' => 'Appointee deleted']);
    exit;
}

// ── Method not allowed ────────────────────────────────────────
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
