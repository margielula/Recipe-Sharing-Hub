<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT r.* FROM recipes r JOIN favorites f ON r.id = f.recipe_id WHERE f.user_id = ? ORDER BY f.id DESC");
$stmt->execute([$user_id]);
$favorites = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Favorite Recipes - Culinary Nexus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo-container">
            <div class="logo-icon">🍳</div>
            <span>CULINARY NEXUS</span>
        </a>
        <ul class="nav-links">
            <li><a href="browse.php">Browse Recipes</a></li>
            <li><a href="upload.php">Submit Recipe</a></li>
            <li><a href="favorites.php" style="color: var(--primary); font-weight: 600;">My Favorites</a></li>
            <li><a href="logout.php" style="color: #dc2626; font-weight: 600;">Logout (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a></li>
        </ul>
    </nav>

    <div class="container" style="margin-top: 2rem;">
        <h1>❤️ My Saved Favorite Recipes</h1>
        <div class="grid" style="margin-top: 1.5rem;">
            <?php if (count($favorites) > 0): ?>
                <?php foreach($favorites as $recipe): ?>
                    <div class="card">
                        <img src="<?= htmlspecialchars($recipe['image_url']) ?>" alt="Recipe">
                        <div class="card-body">
                            <h3><?= htmlspecialchars($recipe['title']) ?></h3>
                            <p style="font-size: 0.85rem; color: #64748b;">Category: <?= htmlspecialchars($recipe['category']) ?></p>
                            <div class="card-footer" style="margin-top: 1rem;">
                                <div class="author-info">
                                    <div class="author-avatar"></div>
                                    <span><?= htmlspecialchars($recipe['author_name']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; color: #64748b; padding: 3rem;">You haven't saved any favorite recipes yet. Browse recipes and click the heart icon to save them here!</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
