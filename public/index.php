<?php
// ============================================================
// public/index.php
// Public landing page — statistics pulled live from the DB.
// No session required.
// ============================================================
require_once __DIR__ . '/../includes/db.php';

// ── Live statistics queries ──────────────────────────────────
$totalCandidates = $pdo->query(
    'SELECT COUNT(*) FROM users WHERE role = "candidate"'
)->fetchColumn();

$activeLists = $pdo->query(
    'SELECT COUNT(DISTINCT CONCAT(list_year, "-", list_period)) FROM appointees'
)->fetchColumn();

$totalAppointed = $pdo->query(
    'SELECT COUNT(*) FROM appointees WHERE status = "appointed"'
)->fetchColumn();

$totalSpecialties = $pdo->query(
    'SELECT COUNT(DISTINCT specialty) FROM appointees'
)->fetchColumn();
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Πίνακες Διοριστέων – Αρχική</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header class="site-header">
        <div class="header-inner">
            <div class="logo">
                <span class="logo-icon">📋</span>
                <span class="logo-text">Πίνακες Διοριστέων</span>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="index.php" class="active">Αρχική</a></li>
                    <li><a href="../candidate/dashboard.php">Υποψήφιοι</a></li>
                    <li><a href="../admin/dashboard.php">Διαχειριστές</a></li>
                    <li><a href="../auth/register.php" class="btn-nav">Εγγραφή</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>

        <section id="hero" class="hero-section">
            <div class="hero-content">
                <h1>Παρακολουθήστε τη θέση σας<br>στους Πίνακες Διοριστέων</h1>
                <p class="hero-subtitle">
                    Η επίσημη πλατφόρμα παρακολούθησης των πινάκων διοριστέων
                    της Εκπαιδευτικής Υπηρεσίας Κύπρου. Ενημερωθείτε άμεσα για
                    την κατάταξή σας και τις τελευταίες αλλαγές.
                </p>
                <div class="hero-actions">
                    <a href="../candidate/dashboard.php" class="btn btn-primary">Ξεκινήστε Τώρα</a>
                    <a href="#search" class="btn btn-outline">Αναζήτηση</a>
                </div>
            </div>
            <div class="hero-image" aria-hidden="true">
                <div class="hero-illustration">🏛️</div>
            </div>
        </section>

        <section id="search" class="search-section">
            <div class="section-inner">
                <h2>Αναζήτηση στους Πίνακες</h2>
                <p class="section-subtitle">
                    Βρείτε εγγραφές βάσει ονόματος, ειδικότητας ή έτους.
                </p>
                <form class="search-form" action="../modules/list.php" method="get">
                    <div class="search-bar">
                        <input
                            type="text"
                            name="keyword"
                            placeholder="π.χ. Φιλόλογοι, ΠΕ02, 2024…"
                            class="search-input"
                        >
                        <button type="submit" class="btn btn-primary search-btn">
                            🔍 Αναζήτηση
                        </button>
                    </div>
                    <div class="search-filters">
                        <select name="specialty" class="filter-select">
                            <option value="">Όλες οι ειδικότητες</option>
                            <option value="PE02">ΠΕ02 – Φιλόλογοι</option>
                            <option value="PE04">ΠΕ04 – Φυσικοί / Χημικοί</option>
                            <option value="PE06">ΠΕ06 – Αγγλική Φιλολογία</option>
                            <option value="PE07">ΠΕ07 – Γαλλική Φιλολογία</option>
                        </select>
                        <select name="year" class="filter-select">
                            <option value="">Όλα τα έτη</option>
                            <option value="2024">2024</option>
                            <option value="2023">2023</option>
                            <option value="2022">2022</option>
                        </select>
                    </div>
                </form>
            </div>
        </section>

        <!-- Statistics pulled from the database -->
        <section id="statistics" class="stats-section">
            <div class="section-inner">
                <h2>Στατιστικά Πλατφόρμας</h2>
                <p class="section-subtitle">Στοιχεία απευθείας από τη βάση δεδομένων.</p>
                <div class="cards-grid">

                    <article class="card">
                        <div class="card-icon">👥</div>
                        <h3 class="card-value">
                            <?= htmlspecialchars($totalCandidates, ENT_QUOTES, 'UTF-8') ?>
                        </h3>
                        <p class="card-label">Εγγεγραμμένοι Υποψήφιοι</p>
                    </article>

                    <article class="card">
                        <div class="card-icon">📄</div>
                        <h3 class="card-value">
                            <?= htmlspecialchars($activeLists, ENT_QUOTES, 'UTF-8') ?>
                        </h3>
                        <p class="card-label">Ενεργοί Πίνακες</p>
                    </article>

                    <article class="card">
                        <div class="card-icon">✅</div>
                        <h3 class="card-value">
                            <?= htmlspecialchars($totalAppointed, ENT_QUOTES, 'UTF-8') ?>
                        </h3>
                        <p class="card-label">Συνολικοί Διορισμοί</p>
                    </article>

                    <article class="card">
                        <div class="card-icon">🏫</div>
                        <h3 class="card-value">
                            <?= htmlspecialchars($totalSpecialties, ENT_QUOTES, 'UTF-8') ?>
                        </h3>
                        <p class="card-label">Ειδικότητες</p>
                    </article>

                </div>
            </div>
        </section>

        <section id="how-it-works" class="how-section">
            <div class="section-inner">
                <h2>Πώς Λειτουργεί</h2>
                <div class="steps-grid">
                    <div class="step">
                        <div class="step-number">1</div>
                        <h3>Εγγραφή</h3>
                        <p>Δημιουργήστε τον λογαριασμό σας με τα στοιχεία σας.</p>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <h3>Αναζήτηση</h3>
                        <p>Βρείτε τον πίνακα της ειδικότητάς σας και ελέγξτε την κατάταξή σας.</p>
                    </div>
                    <div class="step">
                        <div class="step-number">3</div>
                        <h3>Παρακολούθηση</h3>
                        <p>Παρακολουθήστε τις ενημερώσεις και ενημερωθείτε για αλλαγές.</p>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <p class="footer-brand">📋 Πίνακες Διοριστέων</p>
            <p class="footer-sub">
                Εκπαιδευτική Υπηρεσία Κύπρου &nbsp;|&nbsp;
                Πανεπιστημιακό Έργο – Ακαδημαϊκό Έτος 2024–2025
            </p>
            <nav class="footer-nav">
                <a href="index.php">Αρχική</a>
                <a href="../candidate/dashboard.php">Υποψήφιοι</a>
                <a href="../admin/dashboard.php">Διαχειριστές</a>
            </nav>
        </div>
    </footer>

</body>
</html>
