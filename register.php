<?php
require_once 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    if (!empty($fullname) && !empty($email) && !empty($_POST['password'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$fullname, $email, $password]);
            header("Location: login.php");
            exit;
        } catch (\PDOException $e) {
            $error = "Email is already registered.";
        }
    } else {
        $error = "All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Register - Culinary Nexus</title><link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-wrapper">
        <h2>Create Account</h2>
        <?php if($error): ?><p style="color: #dc2626; margin-bottom: 1rem; font-size: 0.9rem;"><?= $error ?></p><?php endif; ?>
        <form method="POST">
            <div class="form-group"><label>Full Name</label><input type="text" name="fullname" required></div>
            <div class="form-group"><label>Email Address</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            <button type="submit" class="btn-submit">Sign Up</button>
        </form>
        <p style="text-align: center; margin-top: 1.2rem; font-size: 0.9rem;">Already have an account? <a href="login.php" style="color: var(--primary);">Login</a></p>
    </div>
</body>
</html>
