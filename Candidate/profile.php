<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

require_once __DIR__ . '/../includes/db.php';

$userId = $_SESSION['user_id'];

// Fetch full user record from DB
$stmt = $pdo->prepare('SELECT id, username, email, role, created_at FROM users WHERE id = :id');
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

// Fetch appointee summary for this user
$totalRecords   = $pdo->prepare('SELECT COUNT(*) FROM appointees WHERE user_id = :id');
$totalRecords->execute([':id' => $userId]);
$totalRecords = $totalRecords->fetchColumn();

$totalAppointed = $pdo->prepare('SELECT COUNT(*) FROM appointees WHERE user_id = :id AND status = "appointed"');
$totalAppointed->execute([':id' => $userId]);
$totalAppointed = $totalAppointed->fetchColumn();

$totalPending = $pdo->prepare('SELECT COUNT(*) FROM appointees WHERE user_id = :id AND status = "pending"');
$totalPending->execute([':id' => $userId]);
$totalPending = $totalPending->fetchColumn();

function hsc(mixed $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Το Προφίλ μου – Πίνακες Διοριστέων</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 40px;
        }
        @media (max-width: 600px) { .profile-grid { grid-template-columns: 1fr; } }

        .profile-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 32px;
            box-shadow: 0 2px 10px rgba(26,58,92,.08);
            border-top: 4px solid #c9a84c;
        }
        .profile-card h3 {
            font-size: 1rem;
            color: #5a7a9a;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }
        .profile-field { margin-bottom: 18px; }
        .profile-field label {
            display: block;
            font-size: 0.8rem;
            color: #5a7a9a;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }
        .profile-field p {
            font-size: 1rem;
            font-weight: 600;
            color: #1a3a5c;
        }
        .stat-row {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
        }
        .stat-box {
            flex: 1;
            min-width: 100px;
            background: #f4f6f8;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
        }
        .stat-box .num { font-size: 1.8rem; font-weight: 700; color: #1a3a5c; }
        .stat-box .lbl { font-size: 0.8rem; color: #5a7a9a; margin-top: 4px; }
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
                <span class="user-badge">🎓</span>
                <span class="user-name">
                    <strong><?= hsc($user['username']) ?></strong>
                </span>
                <a href="../auth/logout.php" class="btn-logout">Αποσύνδεση</a>
            </div>
        </div>
    </header>

    <nav class="candidate-nav">
        <div class="nav-inner">
            <ul>
                <li><a href="dashboard.php">🏠 Dashboard</a></li>
                <li><a href="profile.php" class="active">👤 Το Προφίλ μου</a></li>
                <li><a href="../modules/list.php?keyword=<?= urlencode($user['username']) ?>">📌 Παρακολούθηση Αιτήσεών μου</a></li>
                <li><a href="../modules/list.php">🔍 Αναζήτηση Πινάκων</a></li>
                <li><a href="../public/index.php">🌐 Αρχική</a></li>
            </ul>
        </div>
    </nav>

    <section class="welcome-section">
        <div class="section-inner">
            <h1>Το Προφίλ μου</h1>
            <p>Τα στοιχεία του λογαριασμού σας και η σύνοψη των εγγραφών σας.</p>
        </div>
    </section>

    <main class="page-content">

        <div class="profile-grid">

            <!-- Account info -->
            <div class="profile-card">
                <h3>Στοιχεία Λογαριασμού</h3>

                <div class="profile-field">
                    <label>Όνομα Χρήστη</label>
                    <p><?= hsc($user['username']) ?></p>
                </div>

                <div class="profile-field">
                    <label>Email</label>
                    <p><?= hsc($user['email']) ?></p>
                </div>

                <div class="profile-field">
                    <label>Ρόλος</label>
                    <p><?= hsc(ucfirst($user['role'])) ?></p>
                </div>

                <div class="profile-field">
                    <label>Μέλος από</label>
                    <p><?= hsc(date('d/m/Y', strtotime($user['created_at']))) ?></p>
                </div>
            </div>

            <!-- Stats summary -->
            <div class="profile-card">
                <h3>Σύνοψη Εγγραφών</h3>
                <div class="stat-row">
                    <div class="stat-box">
                        <div class="num"><?= hsc($totalRecords) ?></div>
                        <div class="lbl">Σύνολο</div>
                    </div>
                    <div class="stat-box">
                        <div class="num" style="color:#155724;"><?= hsc($totalAppointed) ?></div>
                        <div class="lbl">Διορισμοί</div>
                    </div>
                    <div class="stat-box">
                        <div class="num" style="color:#856404;"><?= hsc($totalPending) ?></div>
                        <div class="lbl">Σε Αναμονή</div>
                    </div>
                </div>

                <div style="margin-top:28px;">
                    <a href="../modules/list.php?keyword=<?= urlencode($user['username']) ?>"
                       class="btn btn-primary" style="width:100%;text-align:center;display:block;">
                        📌 Δείτε τις Αιτήσεις μου
                    </a>
                </div>
            </div>

        </div>

        <a href="dashboard.php" class="btn btn-secondary">← Επιστροφή στο Dashboard</a>

    </main>

    <footer class="site-footer">
        <div class="footer-inner">
            <p class="footer-brand">📋 Πίνακες Διοριστέων – Candidate Portal</p>
            <p class="footer-sub">Εκπαιδευτική Υπηρεσία Κύπρου</p>
            <nav class="footer-nav">
                <a href="../public/index.php">Αρχική</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="../auth/logout.php">Αποσύνδεση</a>
            </nav>
        </div>
    </footer>

</body>
</html>
