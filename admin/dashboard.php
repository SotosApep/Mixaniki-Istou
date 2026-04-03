<?php
session_start();

// ============================================================
// admin/dashboard.php
// Admin dashboard — session guard + live DB stats.
// Only accessible to users with role = 'admin'.
// ============================================================

// Session guard: must be logged in as admin.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$adminName = $_SESSION['username'];

// ── Live statistics queries ──────────────────────────────────
$totalUsers = $pdo->query(
    'SELECT COUNT(*) FROM users'
)->fetchColumn();

$totalLists = $pdo->query(
    'SELECT COUNT(DISTINCT CONCAT(list_year, "-", list_period)) FROM appointees'
)->fetchColumn();

$totalAppointed = $pdo->query(
    'SELECT COUNT(*) FROM appointees WHERE status = "appointed"'
)->fetchColumn();

$totalPending = $pdo->query(
    'SELECT COUNT(*) FROM appointees WHERE status = "pending"'
)->fetchColumn();

// ── Recent activity: last 5 appointee records ────────────────
$recentStmt = $pdo->query(
    'SELECT a.full_name, a.specialty, a.status, a.list_year, a.list_period,
            a.created_at, u.email
     FROM   appointees a
     JOIN   users u ON u.id = a.user_id
     ORDER  BY a.created_at DESC
     LIMIT  5'
);
$recentRecords = $recentStmt->fetchAll();

// ── Status label helper ──────────────────────────────────────
function statusLabel(string $s): string {
    return match($s) {
        'appointed' => 'Διορίστηκε',
        'rejected'  => 'Απορρίφθηκε',
        default     => 'Σε Αναμονή',
    };
}
function statusBadge(string $s): string {
    return match($s) {
        'appointed' => 'badge-success',
        'rejected'  => 'badge-danger',
        default     => 'badge-warning',
    };
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – Πίνακες Διοριστέων</title>
    <link rel="stylesheet" href="style.css">
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
                    Διαχειριστής:
                    <strong><?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?></strong>
                </span>
                <a href="../auth/logout.php" class="btn-logout">Αποσύνδεση</a>
            </div>
        </div>
    </header>

    <nav class="admin-nav">
        <div class="nav-inner">
            <ul>
                <li><a href="dashboard.php" class="active">🏠 Dashboard</a></li>
                <li><a href="manage_lists.php">📄 Διαχείριση Πινάκων</a></li>
                <li><a href="manage_users.php">👥 Διαχείριση Χρηστών</a></li>
                <li><a href="reports.php">📊 Αναφορές</a></li>
                <li><a href="../public/index.php">🌐 Δημόσια Σελίδα</a></li>
            </ul>
        </div>
    </nav>

    <main>

        <section id="welcome" class="welcome-section">
            <div class="section-inner">
                <h1>Admin Dashboard</h1>
                <p>Καλωσήρθατε,
                    <strong><?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?></strong>.
                    Επιλέξτε μια ενέργεια παρακάτω.
                </p>
            </div>
        </section>

        <!-- Live stats from DB -->
        <section id="quick-stats" class="panel-section">
            <div class="section-inner">
                <h2>Σύνοψη Συστήματος</h2>
                <div class="panel">

                    <div class="panel-stat">
                        <span class="stat-icon">👥</span>
                        <span class="stat-value">
                            <?= htmlspecialchars($totalUsers, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="stat-label">Χρήστες</span>
                    </div>

                    <div class="panel-stat">
                        <span class="stat-icon">📄</span>
                        <span class="stat-value">
                            <?= htmlspecialchars($totalLists, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="stat-label">Πίνακες</span>
                    </div>

                    <div class="panel-stat">
                        <span class="stat-icon">✅</span>
                        <span class="stat-value">
                            <?= htmlspecialchars($totalAppointed, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="stat-label">Διορισμοί</span>
                    </div>

                    <div class="panel-stat">
                        <span class="stat-icon">⏳</span>
                        <span class="stat-value">
                            <?= htmlspecialchars($totalPending, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span class="stat-label">Εκκρεμείς</span>
                    </div>

                </div>
            </div>
        </section>

        <section id="actions" class="actions-section">
            <div class="section-inner">
                <h2>Ενέργειες Διαχείρισης</h2>
                <div class="action-cards">

                    <article class="action-card">
                        <div class="action-icon">👥</div>
                        <h3>Διαχείριση Χρηστών</h3>
                        <p>Προβολή, αλλαγή ρόλου και διαγραφή εγγεγραμμένων χρηστών της πλατφόρμας.</p>
                        <a href="manage_users.php" class="btn btn-primary">Άνοιγμα</a>
                    </article>

                    <article class="action-card">
                        <div class="action-icon">📄</div>
                        <h3>Διαχείριση Πινάκων</h3>
                        <p>Προσθήκη, επεξεργασία και διαγραφή εγγραφών στους πίνακες διοριστέων.</p>
                        <a href="manage_lists.php" class="btn btn-primary">Άνοιγμα</a>
                    </article>

                    <article class="action-card">
                        <div class="action-icon">📊</div>
                        <h3>Αναφορές</h3>
                        <p>Στατιστικά ανά ειδικότητα, έτος και κατάσταση — live από τη βάση.</p>
                        <a href="reports.php" class="btn btn-primary">Άνοιγμα</a>
                    </article>

                </div>
            </div>
        </section>

        <!-- Recent activity pulled from DB -->
        <section id="activity" class="activity-section">
            <div class="section-inner">
                <h2>Πρόσφατη Δραστηριότητα</h2>
                <div class="panel activity-panel">
                    <table class="activity-table">
                        <thead>
                            <tr>
                                <th>Ημερομηνία</th>
                                <th>Ονοματεπώνυμο</th>
                                <th>Ειδικότητα</th>
                                <th>Περίοδος</th>
                                <th>Κατάσταση</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentRecords as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($row['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['full_name'],   ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['specialty'],   ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['list_year'] . ' – ' . $row['list_period'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <span class="badge <?= statusBadge($row['status']) ?>">
                                        <?= htmlspecialchars(statusLabel($row['status']), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <p class="footer-brand">📋 Πίνακες Διοριστέων – Admin Panel</p>
            <p class="footer-sub">
                Εκπαιδευτική Υπηρεσία Κύπρου &nbsp;|&nbsp;
                Πανεπιστημιακό Έργο – Ακαδημαϊκό Έτος 2024–2025
            </p>
            <nav class="footer-nav">
                <a href="../public/index.php">Αρχική</a>
                <a href="../candidate/dashboard.php">Υποψήφιοι</a>
                <a href="dashboard.php">Admin</a>
            </nav>
        </div>
    </footer>

</body>
</html>
