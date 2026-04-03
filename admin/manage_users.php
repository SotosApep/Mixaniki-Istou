<?php
session_start();

// ── Session guard: admin only ────────────────────────────────
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$action  = $_GET['action'] ?? 'list';
$id      = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);
$errors  = [];

// ============================================================
// DELETE USER (prevent self-deletion)
// ============================================================
if ($action === 'delete' && $id > 0) {
    if ($id === (int) $_SESSION['user_id']) {
        $_SESSION['flash_success'] = ''; // not a success
        $errors[] = 'Δεν μπορείτε να διαγράψετε τον δικό σας λογαριασμό.';
    } else {
        $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $_SESSION['flash_success'] = 'Ο χρήστης διαγράφηκε επιτυχώς.';
        header('Location: manage_users.php');
        exit;
    }
}

// ============================================================
// CHANGE ROLE (POST)
// ============================================================
if ($action === 'role' && $_SERVER['REQUEST_METHOD'] === 'POST' && $id > 0) {
    $newRole = $_POST['role'] ?? '';
    if (in_array($newRole, ['admin','candidate'])) {
        if ($id === (int) $_SESSION['user_id']) {
            $errors[] = 'Δεν μπορείτε να αλλάξετε τον ρόλο του δικού σας λογαριασμού.';
        } else {
            $stmt = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
            $stmt->execute([':role' => $newRole, ':id' => $id]);
            $_SESSION['flash_success'] = 'Ο ρόλος ενημερώθηκε επιτυχώς.';
            header('Location: manage_users.php');
            exit;
        }
    }
}

// ============================================================
// LIST all users with their appointee count
// ============================================================
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $kw   = '%' . $search . '%';
    $stmt = $pdo->prepare(
        'SELECT u.*, COUNT(a.id) AS appt_count
         FROM users u LEFT JOIN appointees a ON a.user_id = u.id
         WHERE u.username LIKE :kw1 OR u.email LIKE :kw2
         GROUP BY u.id ORDER BY u.created_at DESC'
    );
    $stmt->execute([':kw1' => $kw, ':kw2' => $kw]);
} else {
    $stmt = $pdo->query(
        'SELECT u.*, COUNT(a.id) AS appt_count
         FROM users u LEFT JOIN appointees a ON a.user_id = u.id
         GROUP BY u.id ORDER BY u.created_at DESC'
    );
}
$users = $stmt->fetchAll();

function val(array $r, string $k, mixed $d = ''): string {
    return htmlspecialchars($r[$k] ?? $d, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Χρηστών – Admin</title>
    <link rel="stylesheet" href="../shared.css">
    <style>
        .top-bar { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:28px; }
        .btn-danger  { background-color:#dc3545; color:#fff; border-color:#dc3545; }
        .btn-danger:hover  { background-color:#b02a37; border-color:#b02a37; }
        .btn-warning { background-color:#c9a84c; color:#1a3a5c; border-color:#c9a84c; }
        .btn-warning:hover { background-color:#b8922e; border-color:#b8922e; }
        .btn-sm { padding:6px 14px; font-size:0.82rem; }
        .role-form { display:inline-flex; align-items:center; gap:6px; }
        .role-select { padding:5px 10px; border:2px solid #dde4ec; border-radius:5px; font-family:'Inter',sans-serif; font-size:0.82rem; color:#1a3a5c; cursor:pointer; }
        .self-badge { font-size:0.75rem; background:#e8f0fa; color:#1a3a5c; padding:2px 8px; border-radius:10px; }
    </style>
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <div class="logo">
            <span class="logo-icon">📋</span>
            <span class="logo-text">Πίνακες Διοριστέων</span>
        </div>
        <div class="header-user">
            <span class="user-badge">⚙️</span>
            <span class="user-name">
                <strong><?= htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') ?></strong>
            </span>
            <a href="../auth/logout.php" class="btn-logout">Αποσύνδεση</a>
        </div>
    </div>
</header>

<nav class="sub-nav">
    <div class="nav-inner">
        <ul>
            <li><a href="dashboard.php">🏠 Dashboard</a></li>
            <li><a href="manage_lists.php">📄 Διαχείριση Πινάκων</a></li>
            <li><a href="manage_users.php" class="active">👥 Διαχείριση Χρηστών</a></li>
            <li><a href="reports.php">📊 Αναφορές</a></li>
            <li><a href="../public/index.php">🌐 Αρχική</a></li>
        </ul>
    </div>
</nav>

<main class="page-content">

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <ul><?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <div class="top-bar">
        <h2 class="section-heading" style="margin-bottom:0;">Χρήστες Πλατφόρμας</h2>
        <span style="color:#5a7a9a;font-size:0.9rem;"><?= count($users) ?> χρήστες σύνολο</span>
    </div>

    <!-- Search -->
    <form method="GET" action="manage_users.php" style="margin-bottom:20px;">
        <div class="search-bar">
            <input type="text" name="search" class="search-input"
                   placeholder="Αναζήτηση με βάση όνομα ή email…"
                   value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn-primary search-btn">🔍 Αναζήτηση</button>
        </div>
        <?php if ($search !== ''): ?>
            <a href="manage_users.php" class="btn btn-secondary" style="padding:6px 14px;font-size:0.82rem;margin-top:8px;">✕ Εκκαθάριση</a>
        <?php endif; ?>
    </form>

    <?php if (empty($users)): ?>
        <div class="alert alert-info">Δεν βρέθηκαν χρήστες.</div>
    <?php else: ?>
    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Όνομα Χρήστη</th>
                    <th>Email</th>
                    <th>Ρόλος</th>
                    <th>Εγγραφές</th>
                    <th>Εγγραφή στις</th>
                    <th>Ενέργειες</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <?php $isSelf = (int)$u['id'] === (int)$_SESSION['user_id']; ?>
                <tr>
                    <td><?= val($u,'id') ?></td>
                    <td>
                        <?= val($u,'username') ?>
                        <?php if ($isSelf): ?>
                            <span class="self-badge">εσείς</span>
                        <?php endif; ?>
                    </td>
                    <td><?= val($u,'email') ?></td>
                    <td>
                        <?php if (!$isSelf): ?>
                        <!-- Inline role-change form -->
                        <form method="POST"
                              action="manage_users.php?action=role&id=<?= val($u,'id') ?>"
                              class="role-form">
                            <select name="role" class="role-select"
                                    onchange="this.form.submit()">
                                <option value="candidate" <?= $u['role']==='candidate' ? 'selected':'' ?>>candidate</option>
                                <option value="admin"     <?= $u['role']==='admin'     ? 'selected':'' ?>>admin</option>
                            </select>
                        </form>
                        <?php else: ?>
                            <span class="badge badge-info"><?= val($u,'role') ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= val($u,'appt_count') ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($u['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                    <td>
                        <?php if (!$isSelf): ?>
                        <a href="manage_users.php?action=delete&id=<?= val($u,'id') ?>"
                           class="btn btn-danger btn-sm"
                           onclick="return confirm('Να διαγραφεί ο χρήστης <?= val($u,'username') ?>; Θα διαγραφούν και όλες οι εγγραφές του.');">
                            🗑️ Διαγραφή
                        </a>
                        <?php else: ?>
                        <span style="color:#aaa;font-size:0.82rem;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</main>

<footer class="site-footer">
    <div class="footer-inner">
        <p class="footer-brand">📋 Πίνακες Διοριστέων – Admin Panel</p>
        <p class="footer-sub">Εκπαιδευτική Υπηρεσία Κύπρου</p>
        <nav class="footer-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="../public/index.php">Αρχική</a>
            <a href="../auth/logout.php">Αποσύνδεση</a>
        </nav>
    </div>
</footer>

</body>
</html>
