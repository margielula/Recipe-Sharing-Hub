 <?php
require_once 'db.php';

// Check if user is logged in as admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$message = '';
$error = '';

// 1. Handle User Deletion
if (isset($_POST['delete_user'])) {
    $user_id_to_delete = (int)$_POST['user_id'];
    if ($user_id_to_delete === (int)$_SESSION['user_id']) {
        $error = "You cannot delete your own active admin account!";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id_to_delete]);
        $message = "User deleted successfully!";
    }
}

// 2. Handle Recipe Deletion
if (isset($_POST['delete_recipe'])) {
    $recipe_id_to_delete = (int)$_POST['recipe_id'];
    $stmt = $pdo->prepare("DELETE FROM recipes WHERE id = ?");
    $stmt->execute([$recipe_id_to_delete]);
    $message = "Recipe removed successfully!";
}

// 3. Handle Feedback Response
if (isset($_POST['respond_feedback'])) {
    $feedback_id = (int)$_POST['feedback_id'];
    $response = trim($_POST['admin_response']);
    $stmt = $pdo->prepare("UPDATE feedback SET admin_response = ? WHERE id = ?");
    $stmt->execute([$response, $feedback_id]);
    $message = "Response saved successfully!";
}

// Fetch Metrics Counters
$total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_recipes = $pdo->query("SELECT COUNT(*) FROM recipes")->fetchColumn();
$total_feedback = $pdo->query("SELECT COUNT(*) FROM feedback")->fetchColumn();

// Fetch Data for Tables
$users = $pdo->query("SELECT id, fullname, email, role FROM users ORDER BY id DESC")->fetchAll();
$recipes = $pdo->query("SELECT id, title, category, author_name FROM recipes ORDER BY id DESC")->fetchAll();
$feedbacks = $pdo->query("SELECT f.*, u.fullname, u.email FROM feedback f JOIN users u ON f.user_id = u.id ORDER BY f.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Culinary Nexus</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .admin-section { background: white; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .admin-table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .admin-table th, .admin-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #e2e8f0; font-size: 0.9rem; }
        .admin-table th { background: #f8fafc; color: #475569; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; }
        .btn-danger { background: #dc2626; color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 4px; cursor: pointer; font-size: 0.8rem; }
        .btn-danger:hover { background: #b91c1c; }
        .badge-admin { background: #fef3c7; color: #b45309; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: bold; font-size: 0.75rem; }
        .badge-user { background: #e0f2fe; color: #0369a1; padding: 0.2rem 0.5rem; border-radius: 4px; font-weight: bold; font-size: 0.75rem; }
    </style>
</head>
<body style="background: #f1f5f9;">
    <nav class="navbar">
        <a href="index.php" class="logo-container">
            <div class="logo-icon">🍳</div>
            <span>CULINARY NEXUS</span>
        </a>
        <ul class="nav-links">
            <li><a href="browse.php">Browse Recipes</a></li>
            <li><a href="upload.php">Submit Recipe</a></li>
            <li><a href="favorites.php">My Favorites</a></li>
            <li><a href="admin.php" style="color: #b45309; font-weight: 700; background: #fef3c7; padding: 0.4rem 0.8rem; border-radius: 6px;">👑 Admin Dashboard</a></li>
            <li><a href="logout.php" style="color: #dc2626; font-weight: 600;">Logout</a></li>
        </ul>
    </nav>

    <div class="container" style="margin-top: 2rem;">
        <h1 style="margin-bottom: 1.5rem;">👑 System Admin Control Panel</h1>

        <?php if($message): ?>
            <div style="background: #dcfce7; color: #166534; padding: 0.8rem; border-radius: 6px; margin-bottom: 1rem;"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div style="background: #fee2e2; color: #991b1b; padding: 0.8rem; border-radius: 6px; margin-bottom: 1rem;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- METRICS CARDS -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
            <div class="admin-section" style="margin-bottom: 0; border-left: 4px solid #2563eb;">
                <p style="color: #64748b; font-size: 0.8rem; text-transform: uppercase; font-weight: 600;">Total Users</p>
                <h2 style="font-size: 2.2rem; color: #0f172a; margin-top: 0.3rem;"><?= $total_users ?></h2>
            </div>
            <div class="admin-section" style="margin-bottom: 0; border-left: 4px solid #d97706;">
                <p style="color: #64748b; font-size: 0.8rem; text-transform: uppercase; font-weight: 600;">Total Recipes</p>
                <h2 style="font-size: 2.2rem; color: #0f172a; margin-top: 0.3rem;"><?= $total_recipes ?></h2>
            </div>
            <div class="admin-section" style="margin-bottom: 0; border-left: 4px solid #059669;">
                <p style="color: #64748b; font-size: 0.8rem; text-transform: uppercase; font-weight: 600;">Total Feedback</p>
                <h2 style="font-size: 2.2rem; color: #0f172a; margin-top: 0.3rem;"><?= $total_feedback ?></h2>
            </div>
        </div>

        <!-- USERS MANAGEMENT TABLE -->
        <div class="admin-section">
            <h2>👥 Manage Registered Users</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $u): ?>
                        <tr>
                            <td>#<?= $u['id'] ?></td>
                            <td><strong><?= htmlspecialchars($u['fullname']) ?></strong></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <span class="<?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-user' ?>">
                                    <?= strtoupper(htmlspecialchars($u['role'])) ?>
                                </span>
                            </td>
                            <td>
                                <?php if($u['id'] !== (int)$_SESSION['user_id']): ?>
                                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" name="delete_user" class="btn-danger">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <span style="color: #94a3b8; font-size: 0.8rem;">Current Admin</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- RECIPES MANAGEMENT TABLE -->
        <div class="admin-section">
            <h2>🍳 Manage Published Recipes</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Author</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($recipes as $r): ?>
                        <tr>
                            <td>#<?= $r['id'] ?></td>
                            <td><strong><?= htmlspecialchars($r['title']) ?></strong></td>
                            <td><?= htmlspecialchars($r['category']) ?></td>
                            <td><?= htmlspecialchars($r['author_name']) ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Remove this recipe from the system?');" style="display:inline;">
                                    <input type="hidden" name="recipe_id" value="<?= $r['id'] ?>">
                                    <button type="submit" name="delete_recipe" class="btn-danger">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- USER FEEDBACK & RESPONSES -->
        <div class="admin-section">
            <h2>💬 User Feedback & Inquiries</h2>
            <?php if(count($feedbacks) > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                    <?php foreach($feedbacks as $fb): ?>
                        <div style="border: 1px solid #e2e8f0; padding: 1rem; border-radius: 6px; background: #fafafa;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.85rem;">
                                <strong><?= htmlspecialchars($fb['fullname']) ?> (<?= htmlspecialchars($fb['email']) ?>)</strong>
                                <span style="color: #64748b;"><?= $fb['created_at'] ?></span>
                            </div>
                            <p style="background: white; padding: 0.6rem; border: 1px solid #e2e8f0; border-radius: 4px; margin-bottom: 0.8rem; color: #334155;"><?= nl2br(htmlspecialchars($fb['message'])) ?></p>
                            
                            <?php if(!empty($fb['admin_response'])): ?>
                                <div style="background: #f0fdf4; border-left: 3px solid #16a34a; padding: 0.6rem; border-radius: 4px;">
                                    <strong style="font-size: 0.8rem; color: #166534;">Admin Response:</strong>
                                    <p style="font-size: 0.85rem; color: #14532d; margin-top: 0.2rem;"><?= nl2br(htmlspecialchars($fb['admin_response'])) ?></p>
                                </div>
                            <?php else: ?>
                                <form method="POST" style="display: flex; gap: 0.5rem;">
                                    <input type="hidden" name="feedback_id" value="<?= $fb['id'] ?>">
                                    <input type="text" name="admin_response" placeholder="Type reply as admin..." required style="flex: 1; padding: 0.5rem; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 0.9rem;">
                                    <button type="submit" name="respond_feedback" style="background: var(--primary); color: white; border: none; padding: 0.5rem 1rem; border-radius: 4px; cursor: pointer;">Send Reply</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="color: #64748b; margin-top: 1rem;">No feedback messages submitted by users yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
