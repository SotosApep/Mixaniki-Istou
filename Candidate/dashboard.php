<?php
session_start();

// ============================================================
// candidate/dashboard.php
// Candidate dashboard — session guard + live DB data for the
// logged-in user's appointee records.
// ============================================================

// Session guard: must be logged in.
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$userId   = $_SESSION['user_id'];
$username = $_SESSION['username'];

// ── Fetch this candidate's appointee records ─────────────────
$stmt = $pdo->prepare(
    'SELECT * FROM appointees
     WHERE  user_id = :uid
     ORDER  BY list_year DESC, created_at DESC'
);
$stmt->execute([':uid' => $userId]);
$records = $stmt->fetchAll();

// Latest record drives the summary cards at the top.
$latest = $records[0] ?? null;

// ── Status helpers ───────────────────────────────────────────
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
function statusCardClass(string $s): string {
    return match($s) {
        'appointed' => 'status-appointed',
        'rejected'  => 'status-rejected',
        default     => 'status-pending',
    };
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Dashboard – Πίνακες Διοριστέων</title>
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
                <span class="user-badge">🎓</span>
                <span class="user-name">
                    Υποψήφιος:
                    <strong><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></strong>
                </span>
                <a href="../auth/logout.php" class="btn-logout">Αποσύνδεση</a>
            </div>
        </div>
    </header>

    <nav class="candidate-nav">
        <div class="nav-inner">
            <ul>
                <li><a href="dashboard.php" class="active">🏠 Dashboard</a></li>
                <li><a href="profile.php">👤 Το Προφίλ μου</a></li>
                <li><a href="../modules/list.php?keyword=<?= urlencode($username) ?>">📌 Παρακολούθηση Αιτήσεών μου</a></li>
                <li><a href="../modules/list.php">🔍 Αναζήτηση Πινάκων</a></li>
                <li><a href="../public/index.php">🌐 Αρχική</a></li>
            </ul>
        </div>
    </nav>

    <main>

        <section id="welcome" class="welcome-section">
            <div class="section-inner">
                <h1>Καλωσήρθατε, <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>!</h1>
                <p>Παρακολουθήστε την κατάστασή σας στους πίνακες διοριστέων και ενημερωθείτε άμεσα.</p>
            </div>
        </section>

        <!-- Summary cards from DB — shows latest record -->
        <section id="status-summary" class="summary-section">
            <div class="section-inner">
                <h2>Σύνοψη Κατάστασης</h2>

                <?php if ($latest): ?>
                <div class="summary-cards">

                    <div class="card summary-card">
                        <div class="card-icon">📄</div>
                        <div class="card-info">
                            <h3>Ειδικότητα</h3>
                            <p><?= htmlspecialchars($latest['specialty'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>

                    <div class="card summary-card">
                        <div class="card-icon">🔢</div>
                        <div class="card-info">
                            <h3>Σειρά Κατάταξης</h3>
                            <p>#<?= htmlspecialchars($latest['rank_position'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>

                    <div class="card summary-card">
                        <div class="card-icon">📅</div>
                        <div class="card-info">
                            <h3>Περίοδος</h3>
                            <p><?= htmlspecialchars($latest['list_year'] . ' – ' . $latest['list_period'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>

                    <div class="card summary-card <?= statusCardClass($latest['status']) ?>">
                        <div class="card-icon">
                            <?= $latest['status'] === 'appointed' ? '✅' : ($latest['status'] === 'rejected' ? '❌' : '⏳') ?>
                        </div>
                        <div class="card-info">
                            <h3>Κατάσταση</h3>
                            <p><?= htmlspecialchars(statusLabel($latest['status']), ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                    </div>

                </div>
                <?php else: ?>
                <p class="no-records">Δεν βρέθηκαν εγγραφές για τον λογαριασμό σας.</p>
                <?php endif; ?>

            </div>
        </section>

        <section id="actions" class="actions-section">
            <div class="section-inner">
                <h2>Επιλογές</h2>
                <div class="action-cards">

                    <article class="action-card">
                        <div class="action-icon">👤</div>
                        <h3>Το Προφίλ μου</h3>
                        <p>Δείτε τα στοιχεία του λογαριασμού σας και τη σύνοψη των εγγραφών σας.</p>
                        <a href="profile.php" class="btn btn-primary">Προβολή Προφίλ</a>
                    </article>

                    <article class="action-card">
                        <div class="action-icon">📌</div>
                        <h3>Παρακολούθηση Αιτήσεών μου</h3>
                        <p>Ελέγξτε την τρέχουσα κατάταξή σας και την πρόοδο των αιτήσεών σας.</p>
                        <a href="../modules/list.php?keyword=<?= urlencode($username) ?>" class="btn btn-primary">Προβολή Ιστορικού</a>
                    </article>

                    <article class="action-card">
                        <div class="action-icon">🔍</div>
                        <h3>Αναζήτηση Πινάκων</h3>
                        <p>Αναζητήστε εγγραφές στους πίνακες διοριστέων βάσει ονόματος ή ειδικότητας.</p>
                        <a href="../modules/list.php" class="btn btn-primary">Αναζήτηση</a>
                    </article>

                </div>
            </div>
        </section>

        <!-- Full history from DB -->
        <section id="history" class="history-section">
            <div class="section-inner">
                <h2>Ιστορικό Αιτήσεων</h2>

                <?php if (empty($records)): ?>
                    <p class="no-records">Δεν υπάρχουν εγγραφές.</p>
                <?php else: ?>
                <div class="history-table-wrap">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Έτος</th>
                                <th>Περίοδος</th>
                                <th>Ειδικότητα</th>
                                <th>Σειρά</th>
                                <th>Κατάσταση</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($records as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['list_year'],     ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['list_period'],   ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['specialty'],     ENT_QUOTES, 'UTF-8') ?></td>
                                <td>#<?= htmlspecialchars($row['rank_position'], ENT_QUOTES, 'UTF-8') ?></td>
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
                <?php endif; ?>

            </div>
        </section>

    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <p class="footer-brand">📋 Πίνακες Διοριστέων – Candidate Portal</p>
            <p class="footer-sub">
                Εκπαιδευτική Υπηρεσία Κύπρου &nbsp;|&nbsp;
                Πανεπιστημιακό Έργο – Ακαδημαϊκό Έτος 2024–2025
            </p>
            <nav class="footer-nav">
                <a href="../public/index.php">Αρχική</a>
                <a href="dashboard.php">Υποψήφιοι</a>
                <a href="../admin/dashboard.php">Admin</a>
            </nav>
        </div>
    </footer>

</body>
</html>
