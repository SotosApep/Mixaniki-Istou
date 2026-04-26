<?php
// api/appointees.php
// GET  → list all appointees
// POST → create a new appointee

require_once __DIR__ . '/../includes/db.php';
header('Content-Type: application/json');

// ── GET ──────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query(
        "SELECT * FROM appointees ORDER BY list_year DESC, rank_position ASC"
    );
    $data = $stmt->fetchAll();
    http_response_code(200);
    echo json_encode($data);
    exit;
}

// ── POST ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    // Validate required fields
    $required = ['user_id', 'full_name', 'specialty', 'rank_position', 'list_year', 'list_period'];
    foreach ($required as $field) {
        if (empty($body[$field])) {
            http_response_code(400);
            echo json_encode(['error' => "Field '$field' is required"]);
            exit;
        }
    }

    $allowed_statuses = ['pending', 'appointed', 'rejected'];
    $status = $body['status'] ?? 'pending';
    if (!in_array($status, $allowed_statuses)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid status. Must be pending, appointed, or rejected']);
        exit;
    }

    // Verify the user exists
    $check = $pdo->prepare("SELECT id FROM users WHERE id = :id");
    $check->execute([':id' => (int) $body['user_id']]);
    if (!$check->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'User not found']);
        exit;
    }

    $stmt = $pdo->prepare(
        "INSERT INTO appointees (user_id, full_name, specialty, rank_position, list_year, list_period, status)
         VALUES (:uid, :fn, :sp, :rp, :ly, :lp, :st)"
    );
    $stmt->execute([
        ':uid' => (int) $body['user_id'],
        ':fn'  => $body['full_name'],
        ':sp'  => $body['specialty'],
        ':rp'  => (int) $body['rank_position'],
        ':ly'  => (int) $body['list_year'],
        ':lp'  => $body['list_period'],
        ':st'  => $status,
    ]);

    http_response_code(201);
    echo json_encode(['message' => 'Appointee created', 'id' => (int) $pdo->lastInsertId()]);
    exit;
}

// ── Method not allowed ────────────────────────────────────────
http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
