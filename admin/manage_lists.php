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
$errors  = [];
$success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

// ── Fetch all candidates for the user_id dropdown ────────────
$candidates = $pdo->query(
    'SELECT id, username, email FROM users WHERE role = "candidate" ORDER BY username'
)->fetchAll();

// ============================================================
// DELETE
// ============================================================
if ($action === 'delete' && $id > 0) {
    $stmt = $pdo->prepare('DELETE FROM appointees WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $_SESSION['flash_success'] = 'Η εγγραφή διαγράφηκε επιτυχώς.';
    header('Location: manage_lists.php');
    exit;
}

// ============================================================
// ADD — handle POST
// ============================================================
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id       = (int)   ($_POST['user_id']       ?? 0);
    $full_name     = trim(    $_POST['full_name']      ?? '');
    $specialty     = trim(    $_POST['specialty']      ?? '');
    $rank_position = (int)   ($_POST['rank_position']  ?? 0);
    $list_year     = (int)   ($_POST['list_year']      ?? 0);
    $list_period   = trim(    $_POST['list_period']    ?? '');
    $status        = trim(    $_POST['status']         ?? 'pending');

    if ($user_id <= 0)         $errors[] = 'Επιλέξτε υποψήφιο.';
    if ($full_name === '')     $errors[] = 'Το ονοματεπώνυμο είναι υποχρεωτικό.';
    if ($specialty === '')     $errors[] = 'Η ειδικότητα είναι υποχρεωτική.';
    if ($rank_position <= 0)   $errors[] = 'Η σειρά κατάταξης πρέπει να είναι θετικός αριθμός.';
    if ($list_year < 2000 || $list_year > 2100) $errors[] = 'Εισάγετε έγκυρο έτος.';
    if ($list_period === '')   $errors[] = 'Η περίοδος είναι υποχρεωτική.';
    if (!in_array($status, ['pending','appointed','rejected'])) $status = 'pending';

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            'INSERT INTO appointees (user_id, full_name, specialty, rank_position, list_year, list_period, status)
             VALUES (:user_id, :full_name, :specialty, :rank_position, :list_year, :list_period, :status)'
        );
        $stmt->execute([
            ':user_id'       => $user_id,
            ':full_name'     => $full_name,
            ':specialty'     => $specialty,
            ':rank_position' => $rank_position,
            ':list_year'     => $list_year,
            ':list_period'   => $list_period,
            ':status'        => $status,
        ]);
        $_SESSION['flash_success'] = 'Η εγγραφή προστέθηκε επιτυχώς.';
        header('Location: manage_lists.php');
        exit;
    }
}

// ============================================================
// EDIT — fetch existing row & handle POST
// ============================================================
$editRow = null;
if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM appointees WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $editRow = $stmt->fetch();
    if (!$editRow) {
        header('Location: manage_lists.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id       = (int)   ($_POST['user_id']       ?? 0);
        $full_name     = trim(    $_POST['full_name']      ?? '');
        $specialty     = trim(    $_POST['specialty']      ?? '');
        $rank_position = (int)   ($_POST['rank_position']  ?? 0);
        $list_year     = (int)   ($_POST['list_year']      ?? 0);
        $list_period   = trim(    $_POST['list_period']    ?? '');
        $status        = trim(    $_POST['status']         ?? 'pending');

        if ($user_id <= 0)         $errors[] = 'Επιλέξτε υποψήφιο.';
        if ($full_name === '')     $errors[] = 'Το ονοματεπώνυμο είναι υποχρεωτικό.';
        if ($specialty === '')     $errors[] = 'Η ειδικότητα είναι υποχρεωτική.';
        if ($rank_position <= 0)   $errors[] = 'Η σειρά κατάταξης πρέπει να είναι θετικός αριθμός.';
        if ($list_year < 2000 || $list_year > 2100) $errors[] = 'Εισάγετε έγκυρο έτος.';
        if ($list_period === '')   $errors[] = 'Η περίοδος είναι υποχρεωτική.';
        if (!in_array($status, ['pending','appointed','rejected'])) $status = 'pending';

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                'UPDATE appointees
                 SET user_id=:user_id, full_name=:full_name, specialty=:specialty,
                     rank_position=:rank_position, list_year=:list_year,
                     list_period=:list_period, status=:status
                 WHERE id=:id'
            );
            $stmt->execute([
                ':user_id'       => $user_id,
                ':full_name'     => $full_name,
                ':specialty'     => $specialty,
                ':rank_position' => $rank_position,
                ':list_year'     => $list_year,
                ':list_period'   => $list_period,
                ':status'        => $status,
                ':id'            => $id,
            ]);
            $_SESSION['flash_success'] = 'Η εγγραφή ενημερώθηκε επιτυχώς.';
            header('Location: manage_lists.php');
            exit;
        }

        // Re-populate editRow with submitted values on error
        $editRow = compact('user_id','full_name','specialty','rank_position','list_year','list_period','status');
        $editRow['id'] = $id;
    }
}

// ============================================================
// LIST — fetch all appointees
// ============================================================
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $kw   = '%' . $search . '%';
    $stmt = $pdo->prepare(
        'SELECT a.*, u.username FROM appointees a JOIN users u ON u.id = a.user_id
         WHERE a.full_name LIKE :kw1 OR a.specialty LIKE :kw2
         ORDER BY a.list_year DESC, a.rank_position ASC'
    );
    $stmt->execute([':kw1' => $kw, ':kw2' => $kw]);
} else {
    $stmt = $pdo->query(
        'SELECT a.*, u.username FROM appointees a JOIN users u ON u.id = a.user_id
         ORDER BY a.list_year DESC, a.rank_position ASC'
    );
}
$appointees = $stmt->fetchAll();

// ── Helpers ──────────────────────────────────────────────────
function sBadge(string $s): string {
    return match($s) { 'appointed' => 'badge-success', 'rejected' => 'badge-danger', default => 'badge-warning' };
}
function sLabel(string $s): string {
    return match($s) { 'appointed' => 'Διορίστηκε', 'rejected' => 'Απορρίφθηκε', default => 'Σε αναμονή' };
}
function val(array $row, string $key, mixed $default = ''): string {
    return htmlspecialchars($row[$key] ?? $default, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Πινάκων – Admin</title>
    <link rel="stylesheet" href="../shared.css">
    <style>
        .top-bar { display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:28px; }
        .form-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:16px; }
        .form-actions { display:flex; gap:12px; margin-top:8px; }
        .btn-danger { background-color:#dc3545; color:#fff; border-color:#dc3545; }
        .btn-danger:hover { background-color:#b02a37; border-color:#b02a37; }
        .btn-sm { padding:6px 14px; font-size:0.82rem; }
        .form-card { background:#fff; border-radius:10px; padding:32px; box-shadow:0 2px 10px rgba(26,58,92,.08); margin-bottom:36px; border-top:4px solid #c9a84c; }
        .form-card h3 { margin-bottom:24px; color:#1a3a5c; }
        select.form-control { cursor:pointer; }
        .data-table-wrap { overflow-x: auto; }
        .action-btns { display:flex; flex-direction:column; gap:6px; min-width:140px; }
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
            <li><a href="manage_lists.php" class="active">📄 Διαχείριση Πινάκων</a></li>
            <li><a href="manage_users.php">👥 Διαχείριση Χρηστών</a></li>
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

    <!-- ======================================================
         ADD FORM (shown when action=add or always at top)
    ====================================================== -->
    <?php if ($action === 'add'): ?>

    <div class="form-card">
        <h3>➕ Προσθήκη Νέας Εγγραφής</h3>
        <form method="POST" action="manage_lists.php?action=add" novalidate>
            <div class="form-row">

                <div class="form-group">
                    <label for="user_id">Υποψήφιος</label>
                    <select id="user_id" name="user_id" class="form-control" required>
                        <option value="">— Επιλέξτε —</option>
                        <?php foreach ($candidates as $c): ?>
                        <option value="<?= val($c,'id') ?>"
                            <?= isset($_POST['user_id']) && (int)$_POST['user_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= val($c,'username') ?> (<?= val($c,'email') ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="full_name">Ονοματεπώνυμο</label>
                    <input type="text" id="full_name" name="full_name" class="form-control"
                           value="<?= htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="form-group">
                    <label for="specialty">Ειδικότητα</label>
                    <input type="text" id="specialty" name="specialty" class="form-control"
                           placeholder="π.χ. ΠΕ02 - Φιλόλογοι"
                           value="<?= htmlspecialchars($_POST['specialty'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="form-group">
                    <label for="rank_position">Σειρά Κατάταξης</label>
                    <input type="number" id="rank_position" name="rank_position" class="form-control"
                           min="1" value="<?= htmlspecialchars($_POST['rank_position'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="form-group">
                    <label for="list_year">Έτος</label>
                    <input type="number" id="list_year" name="list_year" class="form-control"
                           min="2000" max="2100" value="<?= htmlspecialchars($_POST['list_year'] ?? date('Y'), ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="form-group">
                    <label for="list_period">Περίοδος</label>
                    <input type="text" id="list_period" name="list_period" class="form-control"
                           placeholder="π.χ. Α΄ Περίοδος 2024"
                           value="<?= htmlspecialchars($_POST['list_period'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="form-group">
                    <label for="status">Κατάσταση</label>
                    <select id="status" name="status" class="form-control">
                        <?php foreach (['pending'=>'Σε αναμονή','appointed'=>'Διορίστηκε','rejected'=>'Απορρίφθηκε'] as $val => $lbl): ?>
                        <option value="<?= $val ?>" <?= (($_POST['status'] ?? 'pending') === $val) ? 'selected' : '' ?>>
                            <?= $lbl ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Αποθήκευση</button>
                <a href="manage_lists.php" class="btn btn-secondary">Ακύρωση</a>
            </div>
        </form>
    </div>

    <!-- ======================================================
         EDIT FORM
    ====================================================== -->
    <?php elseif ($action === 'edit' && $editRow): ?>

    <div class="form-card">
        <h3>✏️ Επεξεργασία Εγγραφής #<?= val($editRow,'id') ?></h3>
        <form method="POST" action="manage_lists.php?action=edit&id=<?= val($editRow,'id') ?>" novalidate>
            <div class="form-row">

                <div class="form-group">
                    <label for="user_id">Υποψήφιος</label>
                    <select id="user_id" name="user_id" class="form-control" required>
                        <option value="">— Επιλέξτε —</option>
                        <?php foreach ($candidates as $c): ?>
                        <option value="<?= val($c,'id') ?>"
                            <?= (int)$editRow['user_id'] === (int)$c['id'] ? 'selected' : '' ?>>
                            <?= val($c,'username') ?> (<?= val($c,'email') ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="full_name">Ονοματεπώνυμο</label>
                    <input type="text" id="full_name" name="full_name" class="form-control"
                           value="<?= val($editRow,'full_name') ?>" required>
                </div>

                <div class="form-group">
                    <label for="specialty">Ειδικότητα</label>
                    <input type="text" id="specialty" name="specialty" class="form-control"
                           value="<?= val($editRow,'specialty') ?>" required>
                </div>

                <div class="form-group">
                    <label for="rank_position">Σειρά Κατάταξης</label>
                    <input type="number" id="rank_position" name="rank_position" class="form-control"
                           min="1" value="<?= val($editRow,'rank_position') ?>" required>
                </div>

                <div class="form-group">
                    <label for="list_year">Έτος</label>
                    <input type="number" id="list_year" name="list_year" class="form-control"
                           min="2000" max="2100" value="<?= val($editRow,'list_year') ?>" required>
                </div>

                <div class="form-group">
                    <label for="list_period">Περίοδος</label>
                    <input type="text" id="list_period" name="list_period" class="form-control"
                           value="<?= val($editRow,'list_period') ?>" required>
                </div>

                <div class="form-group">
                    <label for="status">Κατάσταση</label>
                    <select id="status" name="status" class="form-control">
                        <?php foreach (['pending'=>'Σε αναμονή','appointed'=>'Διορίστηκε','rejected'=>'Απορρίφθηκε'] as $sv => $sl): ?>
                        <option value="<?= $sv ?>" <?= $editRow['status'] === $sv ? 'selected' : '' ?>><?= $sl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Αποθήκευση</button>
                <a href="manage_lists.php" class="btn btn-secondary">Ακύρωση</a>
            </div>
        </form>
    </div>

    <?php endif; ?>

    <!-- ======================================================
         LIST TABLE
    ====================================================== -->
    <div class="top-bar">
        <h2 class="section-heading" style="margin-bottom:0;">Εγγραφές Πινάκων</h2>
        <a href="manage_lists.php?action=add" class="btn btn-primary">➕ Νέα Εγγραφή</a>
    </div>

    <!-- Search -->
    <form method="GET" action="manage_lists.php" style="margin-bottom:20px;">
        <div class="search-bar">
            <input type="text" name="search" class="search-input"
                   placeholder="Αναζήτηση ονόματος ή ειδικότητας…"
                   value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn btn-primary search-btn">🔍 Αναζήτηση</button>
        </div>
        <?php if ($search !== ''): ?>
            <a href="manage_lists.php" class="btn btn-secondary btn-sm" style="margin-top:8px;">✕ Εκκαθάριση</a>
        <?php endif; ?>
    </form>

    <?php if (empty($appointees)): ?>
        <div class="alert alert-info">Δεν βρέθηκαν εγγραφές.</div>
    <?php else: ?>
    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Υποψήφιος</th>
                    <th>Ονοματεπώνυμο</th>
                    <th>Ειδικότητα</th>
                    <th>Σειρά</th>
                    <th>Έτος</th>
                    <th>Περίοδος</th>
                    <th>Κατάσταση</th>
                    <th>Ενέργειες</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointees as $row): ?>
                <tr>
                    <td><?= val($row,'id') ?></td>
                    <td><?= val($row,'username') ?></td>
                    <td><?= val($row,'full_name') ?></td>
                    <td><?= val($row,'specialty') ?></td>
                    <td><?= val($row,'rank_position') ?></td>
                    <td><?= val($row,'list_year') ?></td>
                    <td><?= val($row,'list_period') ?></td>
                    <td>
                        <span class="badge <?= sBadge($row['status']) ?>">
                            <?= htmlspecialchars(sLabel($row['status']), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="manage_lists.php?action=edit&id=<?= val($row,'id') ?>"
                               class="btn btn-secondary btn-sm">✏️ Επεξεργασία</a>
                            <a href="manage_lists.php?action=delete&id=<?= val($row,'id') ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Να διαγραφεί η εγγραφή; Αυτή η ενέργεια δεν αναιρείται.');">
                                🗑️ Διαγραφή
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p style="color:#5a7a9a;font-size:0.85rem;margin-top:12px;">
        <?= count($appointees) ?> εγγραφές
    </p>
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
