<?php
require_once 'db.php';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT * FROM recipes WHERE title LIKE ? OR ingredients LIKE ? ORDER BY id DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM recipes ORDER BY id DESC");
}
$recipes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Culinary Nexus - Recipe Hub</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo-container">
            <div class="logo-icon">🍳</div>
            <span>CULINARY NEXUS</span>
        </a>
        <form method="GET" action="index.php" class="search-container">
            <input type="text" name="search" placeholder="Search recipes..." value="<?= htmlspecialchars($search) ?>">
        </form>
        <ul class="nav-links">
            <li><a href="index.php">Browse Recipes</a></li>
            <li><a href="upload.php">Submit Recipe</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="logout.php" style="color: #dc2626; font-weight: 600;">Logout (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 6px;">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="container">
        <h1 class="hero-title">Welcome to Culinary Nexus - The Hub for Every Home Cook</h1>
        
        <h2 style="font-size: 1.3rem; margin-bottom: 1rem; color: #334155;">Featured & Community Recipes</h2>
        
        <div class="grid">
            <?php if (count($recipes) > 0): ?>
                <?php foreach($recipes as $recipe): ?>
                    <div class="card">
                        <img src="<?= htmlspecialchars($recipe['image_url']) ?>" alt="Recipe">
                        <div class="card-body">
                            <h3><?= htmlspecialchars($recipe['title']) ?></h3>
                            <p style="font-size: 0.85rem; color: #64748b;">Category: <?= htmlspecialchars($recipe['category']) ?></p>
                            <div class="card-footer">
                                <div class="author-info">
                                    <div class="author-avatar"></div>
                                    <span><?= htmlspecialchars($recipe['author_name']) ?></span>
                                </div>
                                <div style="font-weight: 600; color: #b45309;">
                                    ★ <?= $recipe['rating'] ?> (<?= $recipe['reviews_count'] ?>)
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No recipes found matching your search.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
