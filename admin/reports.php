<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

// ── Aggregate stats ──────────────────────────────────────────
$totalUsers      = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$totalCandidates = $pdo->query('SELECT COUNT(*) FROM users WHERE role="candidate"')->fetchColumn();
$totalAdmins     = $pdo->query('SELECT COUNT(*) FROM users WHERE role="admin"')->fetchColumn();
$totalRecords    = $pdo->query('SELECT COUNT(*) FROM appointees')->fetchColumn();
$totalAppointed  = $pdo->query('SELECT COUNT(*) FROM appointees WHERE status="appointed"')->fetchColumn();
$totalPending    = $pdo->query('SELECT COUNT(*) FROM appointees WHERE status="pending"')->fetchColumn();
$totalRejected   = $pdo->query('SELECT COUNT(*) FROM appointees WHERE status="rejected"')->fetchColumn();

// ── Breakdowns ───────────────────────────────────────────────
$bySpecialty = $pdo->query(
    'SELECT specialty,
            COUNT(*) AS total,
            SUM(status="appointed") AS appointed,
            SUM(status="pending")   AS pending,
            SUM(status="rejected")  AS rejected
     FROM appointees GROUP BY specialty ORDER BY total DESC'
)->fetchAll();

$byYear = $pdo->query(
    'SELECT list_year,
            COUNT(*) AS total,
            SUM(status="appointed") AS appointed,
            SUM(status="pending")   AS pending,
            SUM(status="rejected")  AS rejected
     FROM appointees GROUP BY list_year ORDER BY list_year DESC'
)->fetchAll();

$topCandidates = $pdo->query(
    'SELECT u.username, u.email, COUNT(a.id) AS records
     FROM users u JOIN appointees a ON a.user_id = u.id
     GROUP BY u.id ORDER BY records DESC LIMIT 10'
)->fetchAll();

function hsc(mixed $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Αναφορές – Admin</title>
    <link rel="stylesheet" href="../shared.css">
    <style>
        .report-section { margin-bottom: 48px; }
        .report-section h3 {
            font-size: 1.2rem; color: #1a3a5c;
            border-left: 4px solid #c9a84c;
            padding-left: 12px; margin-bottom: 20px;
        }
        .pct-bar-wrap { background:#e8edf2; border-radius:20px; height:10px; flex:1; overflow:hidden; }
        .pct-bar      { height:100%; border-radius:20px; background:#c9a84c; }
        .pct-row      { display:flex; align-items:center; gap:12px; font-size:0.88rem; }
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
                <strong><?= hsc($_SESSION['username']) ?></strong>
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
            <li><a href="manage_users.php">👥 Διαχείριση Χρηστών</a></li>
            <li><a href="reports.php" class="active">📊 Αναφορές</a></li>
            <li><a href="../public/index.php">🌐 Αρχική</a></li>
        </ul>
    </div>
</nav>

<main class="page-content">

    <h2 class="section-heading">Αναφορές & Στατιστικά</h2>

    <!-- ── KPI Cards ─────────────────────────────────────────── -->
    <div class="report-section">
        <h3>Γενική Επισκόπηση</h3>
        <div class="cards-grid">

            <div class="card">
                <div class="card-icon">👥</div>
                <div class="card-value"><?= hsc($totalUsers) ?></div>
                <div class="card-label">Σύνολο Χρηστών</div>
            </div>

            <div class="card">
                <div class="card-icon">🎓</div>
                <div class="card-value"><?= hsc($totalCandidates) ?></div>
                <div class="card-label">Υποψήφιοι</div>
            </div>

            <div class="card">
                <div class="card-icon">📄</div>
                <div class="card-value"><?= hsc($totalRecords) ?></div>
                <div class="card-label">Εγγραφές Πινάκων</div>
            </div>

            <div class="card">
                <div class="card-icon">✅</div>
                <div class="card-value"><?= hsc($totalAppointed) ?></div>
                <div class="card-label">Διορισμοί</div>
            </div>

            <div class="card">
                <div class="card-icon">⏳</div>
                <div class="card-value"><?= hsc($totalPending) ?></div>
                <div class="card-label">Σε Αναμονή</div>
            </div>

            <div class="card">
                <div class="card-icon">❌</div>
                <div class="card-value"><?= hsc($totalRejected) ?></div>
                <div class="card-label">Απορρίψεις</div>
            </div>

        </div>
    </div>

    <!-- ── Status breakdown bar ──────────────────────────────── -->
    <?php if ($totalRecords > 0): ?>
    <div class="report-section">
        <h3>Κατανομή Καταστάσεων</h3>
        <div style="background:#fff;border-radius:10px;padding:28px;box-shadow:0 2px 10px rgba(26,58,92,.08);">

            <?php
            $statuses = [
                ['label'=>'Διορίστηκε', 'count'=>$totalAppointed, 'color'=>'#28a745'],
                ['label'=>'Σε Αναμονή', 'count'=>$totalPending,   'color'=>'#c9a84c'],
                ['label'=>'Απορρίφθηκε','count'=>$totalRejected,  'color'=>'#dc3545'],
            ];
            foreach ($statuses as $st):
                $pct = $totalRecords > 0 ? round(($st['count'] / $totalRecords) * 100) : 0;
            ?>
            <div style="margin-bottom:16px;">
                <div class="pct-row" style="margin-bottom:6px;">
                    <span style="min-width:140px;font-weight:500;"><?= hsc($st['label']) ?></span>
                    <div class="pct-bar-wrap">
                        <div class="pct-bar" style="width:<?= $pct ?>%;background:<?= $st['color'] ?>;"></div>
                    </div>
                    <span style="min-width:60px;text-align:right;">
                        <?= hsc($st['count']) ?> <span style="color:#5a7a9a;">(<?= $pct ?>%)</span>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>

        </div>
    </div>
    <?php endif; ?>

    <!-- ── By Specialty ──────────────────────────────────────── -->
    <?php if (!empty($bySpecialty)): ?>
    <div class="report-section">
        <h3>Ανά Ειδικότητα</h3>
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Ειδικότητα</th>
                        <th>Σύνολο</th>
                        <th>Διορίστηκε</th>
                        <th>Σε Αναμονή</th>
                        <th>Απορρίφθηκε</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bySpecialty as $r): ?>
                    <tr>
                        <td><?= hsc($r['specialty']) ?></td>
                        <td><strong><?= hsc($r['total']) ?></strong></td>
                        <td><span class="badge badge-success"><?= hsc($r['appointed']) ?></span></td>
                        <td><span class="badge badge-warning"><?= hsc($r['pending']) ?></span></td>
                        <td><span class="badge badge-danger"><?= hsc($r['rejected']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── By Year ───────────────────────────────────────────── -->
    <?php if (!empty($byYear)): ?>
    <div class="report-section">
        <h3>Ανά Έτος</h3>
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Έτος</th>
                        <th>Σύνολο</th>
                        <th>Διορίστηκε</th>
                        <th>Σε Αναμονή</th>
                        <th>Απορρίφθηκε</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($byYear as $r): ?>
                    <tr>
                        <td><strong><?= hsc($r['list_year']) ?></strong></td>
                        <td><?= hsc($r['total']) ?></td>
                        <td><span class="badge badge-success"><?= hsc($r['appointed']) ?></span></td>
                        <td><span class="badge badge-warning"><?= hsc($r['pending']) ?></span></td>
                        <td><span class="badge badge-danger"><?= hsc($r['rejected']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- ── Top Candidates ────────────────────────────────────── -->
    <?php if (!empty($topCandidates)): ?>
    <div class="report-section">
        <h3>Top Υποψήφιοι (ανά αριθμό εγγραφών)</h3>
        <div class="data-table-wrap">
            <table class="data-table">
                <thead>
                    <tr><th>#</th><th>Όνομα Χρήστη</th><th>Email</th><th>Εγγραφές</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($topCandidates as $i => $r): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><?= hsc($r['username']) ?></td>
                        <td><?= hsc($r['email']) ?></td>
                        <td><strong><?= hsc($r['records']) ?></strong></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
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
