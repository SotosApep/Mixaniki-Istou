<?php
session_start();

require_once __DIR__ . '/../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$username    = $_SESSION['username'];
$keyword     = trim($_GET['keyword'] ?? '');
$isMyTracking = ($keyword !== '' && $keyword === $username);

if ($keyword !== '') {
    $kw   = '%' . $keyword . '%';
    $stmt = $pdo->prepare(
        'SELECT id, full_name, specialty, rank_position, list_year, list_period, status
         FROM   appointees
         WHERE  full_name LIKE :kw1
            OR  specialty LIKE :kw2
         ORDER BY list_year DESC, rank_position ASC'
    );
    $stmt->execute([':kw1' => $kw, ':kw2' => $kw]);
} else {
    $stmt = $pdo->prepare(
        'SELECT id, full_name, specialty, rank_position, list_year, list_period, status
         FROM   appointees
         ORDER BY list_year DESC, rank_position ASC'
    );
    $stmt->execute();
}

$appointees = $stmt->fetchAll();

function statusBadge(string $s): string {
    return match($s) {
        'appointed' => 'badge-success',
        'rejected'  => 'badge-danger',
        default     => 'badge-warning',
    };
}

function statusLabel(string $s): string {
    return match($s) {
        'appointed' => 'Διορίστηκε',
        'rejected'  => 'Απορρίφθηκε',
        default     => 'Σε αναμονή',
    };
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Λίστα Διοριστέων – Πίνακες Διοριστέων</title>
    <link rel="stylesheet" href="../shared.css">
    <link rel="stylesheet" href="../Candidate/style.css">
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
                    Υποψήφιος: <strong><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></strong>
                </span>
                <a href="../auth/logout.php" class="btn-logout">Αποσύνδεση</a>
            </div>
        </div>
    </header>

    <nav class="candidate-nav">
        <div class="nav-inner">
            <ul>
                <li><a href="../Candidate/dashboard.php">🏠 Dashboard</a></li>
                <li><a href="../Candidate/profile.php">👤 Το Προφίλ μου</a></li>
                <li><a href="list.php?keyword=<?= urlencode($username) ?>"<?= $isMyTracking ? ' class="active"' : '' ?>>📌 Παρακολούθηση Αιτήσεών μου</a></li>
                <li><a href="list.php"<?= !$isMyTracking ? ' class="active"' : '' ?>>🔍 Αναζήτηση Πινάκων</a></li>
                <li><a href="../public/index.php">🌐 Αρχική</a></li>
            </ul>
        </div>
    </nav>

    <main class="page-content">

        <h2 class="section-heading">Πίνακας Διοριστέων</h2>

        <form method="GET" action="" style="margin-bottom: 32px;">
            <div class="search-bar">
                <input
                    type="text"
                    name="keyword"
                    class="search-input"
                    placeholder="Αναζήτηση με βάση ονοματεπώνυμο ή ειδικότητα…"
                    value="<?= htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') ?>"
                >
                <button type="submit" class="btn btn-primary search-btn">🔍 Αναζήτηση</button>
            </div>
            <?php if ($keyword !== ''): ?>
                <a href="list.php" class="btn btn-secondary" style="margin-top:8px;">
                    ✕ Εκκαθάριση
                </a>
            <?php endif; ?>
        </form>

        <?php if (empty($appointees)): ?>
            <div class="alert alert-info">Δεν βρέθηκαν αποτελέσματα.</div>
        <?php else: ?>
            <div class="data-table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Ονοματεπώνυμο</th>
                            <th>Ειδικότητα</th>
                            <th>Σειρά</th>
                            <th>Έτος</th>
                            <th>Περίοδος</th>
                            <th>Κατάσταση</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($appointees as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id'],            ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['full_name'],     ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['specialty'],     ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['rank_position'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['list_year'],     ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['list_period'],   ENT_QUOTES, 'UTF-8') ?></td>
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
            <p class="no-records" style="margin-top:12px;">
                Βρέθηκαν <?= count($appointees) ?> εγγραφές.
            </p>
        <?php endif; ?>

        <a href="../Candidate/dashboard.php" class="btn btn-secondary" style="margin-top:24px;">
            ← Επιστροφή στο Dashboard
        </a>

    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <p class="footer-brand">📋 Πίνακες Διοριστέων</p>
            <p class="footer-sub">Εκπαιδευτική Υπηρεσία Κύπρου</p>
            <nav class="footer-nav">
                <a href="../public/index.php">Αρχική</a>
                <a href="../Candidate/dashboard.php">Dashboard</a>
                <a href="../auth/logout.php">Αποσύνδεση</a>
            </nav>
        </div>
    </footer>

</body>
</html>
