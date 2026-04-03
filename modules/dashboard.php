<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$username = $_SESSION['username'];
$role     = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard – Πίνακες Διοριστέων</title>
    <link rel="stylesheet" href="../shared.css">
</head>
<body>

    <header class="site-header">
        <div class="header-inner">
            <div class="logo">
                <span class="logo-icon">📋</span>
                <span class="logo-text">Πίνακες Διοριστέων</span>
            </div>
            <div class="header-user">
                <span class="user-badge"><?= $role === 'admin' ? '⚙️' : '🎓' ?></span>
                <span class="user-name">
                    <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>
                    &nbsp;<span style="color:#a8c4d8;font-size:0.82rem;">
                        (<?= htmlspecialchars($role, ENT_QUOTES, 'UTF-8') ?>)
                    </span>
                </span>
                <a href="../auth/logout.php" class="btn-logout">Αποσύνδεση</a>
            </div>
        </div>
    </header>

    <section class="welcome-section">
        <div class="section-inner">
            <h1>Καλωσήρθατε, <?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>!</h1>
            <p>Επιλέξτε μια ενέργεια παρακάτω.</p>
        </div>
    </section>

    <main class="page-content">

        <h2 class="section-heading">Επιλογές</h2>

        <div class="action-cards">

            <article class="action-card">
                <div class="action-icon">📄</div>
                <h3>Πίνακας Διοριστέων</h3>
                <p>Αναζητήστε εγγραφές στους πίνακες διοριστέων.</p>
                <a href="list.php" class="btn btn-primary">Προβολή Λίστας</a>
            </article>

            <?php if ($role === 'candidate'): ?>
            <article class="action-card">
                <div class="action-icon">👤</div>
                <h3>Το Προφίλ μου</h3>
                <p>Δείτε τα στοιχεία του λογαριασμού σας και την κατάστασή σας.</p>
                <a href="../candidate/dashboard.php" class="btn btn-primary">Προβολή</a>
            </article>
            <?php endif; ?>

            <?php if ($role === 'admin'): ?>
            <article class="action-card">
                <div class="action-icon">⚙️</div>
                <h3>Διαχείριση</h3>
                <p>Επιλογές διαχειριστή — διαχείριση χρηστών και πινάκων.</p>
                <a href="../admin/dashboard.php" class="btn btn-primary">Διαχείριση</a>
            </article>
            <?php endif; ?>

        </div>

    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <p class="footer-brand">📋 Πίνακες Διοριστέων</p>
            <p class="footer-sub">Εκπαιδευτική Υπηρεσία Κύπρου</p>
            <nav class="footer-nav">
                <a href="../public/index.php">Αρχική</a>
                <a href="list.php">Λίστα</a>
                <a href="../auth/logout.php">Αποσύνδεση</a>
            </nav>
        </div>
    </footer>

</body>
</html>
