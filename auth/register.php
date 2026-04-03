<?php
require_once __DIR__ . '/../includes/db.php';

$errors  = [];
$username = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username         = trim($_POST['username']         ?? '');
    $email            = trim($_POST['email']            ?? '');
    $password         =      $_POST['password']         ?? '';
    $confirm_password =      $_POST['confirm_password'] ?? '';

    if ($username === '')        $errors[] = 'Το όνομα χρήστη είναι υποχρεωτικό.';
    if ($email === '')           $errors[] = 'Το email είναι υποχρεωτικό.';
    if ($password === '')        $errors[] = 'Ο κωδικός πρόσβασης είναι υποχρεωτικός.';
    if ($confirm_password === '') $errors[] = 'Η επιβεβαίωση κωδικού είναι υποχρεωτική.';

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = 'Η μορφή του email δεν είναι έγκυρη.';

    if ($password !== '' && strlen($password) < 8)
        $errors[] = 'Ο κωδικός πρέπει να έχει τουλάχιστον 8 χαρακτήρες.';

    if ($password !== '' && $confirm_password !== '' && $password !== $confirm_password)
        $errors[] = 'Οι κωδικοί δεν ταιριάζουν.';

    if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) $errors[] = 'Αυτό το email χρησιμοποιείται ήδη.';
    }

    if (empty($errors)) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO users (username, email, password_hash, role)
             VALUES (:username, :email, :password_hash, :role)'
        );
        $stmt->execute([
            ':username'      => $username,
            ':email'         => $email,
            ':password_hash' => $password_hash,
            ':role'          => 'candidate',
        ]);
        header('Location: login.php?registered=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Εγγραφή – Πίνακες Διοριστέων</title>
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
                    <li><a href="login.php">Σύνδεση</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="auth-wrapper">
        <div class="auth-card">

            <div class="auth-card-header">
                <h2>Εγγραφή</h2>
                <p>Δημιουργήστε τον λογαριασμό σας</p>
            </div>

            <div class="auth-card-body">

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="register.php" novalidate>

                    <div class="form-group">
                        <label for="username">Όνομα Χρήστη</label>
                        <input type="text" id="username" name="username"
                               class="form-control"
                               value="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email"
                               class="form-control"
                               value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                               required>
                    </div>

                    <div class="form-group">
                        <label for="password">Κωδικός Πρόσβασης</label>
                        <input type="password" id="password" name="password"
                               class="form-control" required>
                        <span class="form-hint">Τουλάχιστον 8 χαρακτήρες.</span>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Επιβεβαίωση Κωδικού</label>
                        <input type="password" id="confirm_password" name="confirm_password"
                               class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">
                        Εγγραφή
                    </button>

                </form>
            </div>

            <div class="auth-card-footer">
                Έχετε ήδη λογαριασμό; <a href="login.php">Σύνδεση</a>
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
