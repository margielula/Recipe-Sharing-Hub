  <?php
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($email !== '' && $password !== '') {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables including ROLE and NAME
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['fullname'];
            $_SESSION['role'] = $user['role']; // <-- This is what grants admin access!

            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: index.php");
            }
            exit;
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Culinary Nexus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body style="background: #f8fafc; display: flex; justify-content: center; align-items: center; height: 100vh;">
    <div style="background: white; padding: 2.5rem; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); width: 100%; max-width: 400px;">
        <h2 style="margin-bottom: 1.5rem; color: #0f172a;">Welcome Back</h2>
        
        <?php if ($error): ?>
            <div style="color: #dc2626; background: #fee2e2; padding: 0.75rem; border-radius: 6px; margin-bottom: 1rem; font-size: 0.9rem;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 0.4rem;">Email Address</label>
                <input type="email" name="email" required style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #475569; margin-bottom: 0.4rem;">Password</label>
                <input type="password" name="password" required style="width: 100%; padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 6px;">
            </div>
            <button type="submit" style="width: 100%; background: var(--primary); color: white; border: none; padding: 0.75rem; border-radius: 6px; font-weight: 600; cursor: pointer;">Log In</button>
        </form>
        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: #64748b;">Don't have an account? <a href="register.php" style="color: var(--primary);">Sign Up</a></p>
    </div>
</body>
</html>
