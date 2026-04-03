<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

$loginError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['role']     = $user['role'];
        $_SESSION['username'] = $user['username'];
        header('Location: ../modules/dashboard.php');
        exit;
    } else {
        $loginError = true;
    }
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Σύνδεση – Πίνακες Διοριστέων</title>
    <link rel="stylesheet" href="../shared.css">
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
                    <li><a href="../public/index.php">Αρχική</a></li>
                    <li><a href="register.php">Εγγραφή</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="auth-card-header">
                <h2>Σύνδεση</h2>
                <p>Εισάγετε τα στοιχεία σας για να συνδεθείτε</p>
            </div>

            <div class="auth-card-body">

                <?php if (isset($_GET['registered'])): ?>
                    <div class="alert alert-success">
                        Εγγραφή επιτυχής! Μπορείτε τώρα να συνδεθείτε.
                    </div>
                <?php endif; ?>

                <?php if ($loginError): ?>
                    <div class="alert alert-danger">
                        Λανθασμένα στοιχεία σύνδεσης.
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" novalidate>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                               class="form-control" required autofocus>
                    </div>

                    <div class="form-group">
                        <label for="password">Κωδικός Πρόσβασης</label>
                        <input type="password" id="password" name="password"
                               class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Σύνδεση
                    </button>

                </form>
            </div>

            <div class="auth-card-footer">
                Δεν έχετε λογαριασμό; <a href="register.php">Εγγραφή</a>
            </div>

        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-inner">
            <p class="footer-brand">📋 Πίνακες Διοριστέων</p>
            <p class="footer-sub">Εκπαιδευτική Υπηρεσία Κύπρου</p>
        </div>
    </footer>

</body>
</html>
