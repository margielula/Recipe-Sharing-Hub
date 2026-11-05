<?php
require_once 'db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['fullname'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Login - Culinary Nexus</title><link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-wrapper">
        <h2>Welcome Back</h2>
        <?php if($error): ?><p style="color: #dc2626; margin-bottom: 1rem; font-size: 0.9rem;"><?= $error ?></p><?php endif; ?>
        <form method="POST">
            <div class="form-group"><label>Email Address</label><input type="email" name="email" required></div>
            <div class="form-group"><label>Password</label><input type="password" name="password" required></div>
            <button type="submit" class="btn-submit">Log In</button>
        </form>
        <p style="text-align: center; margin-top: 1.2rem; font-size: 0.9rem;">Don't have an account? <a href="register.php" style="color: var(--primary);">Sign Up</a></p>
    </div>
</body>
</html>
