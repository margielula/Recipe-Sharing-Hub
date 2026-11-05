 <?php
require_once 'db.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';

// Handle Favorite Toggle
if (isset($_POST['toggle_favorite']) && isset($_SESSION['user_id'])) {
    $recipe_id = (int)$_POST['recipe_id'];
    $user_id = $_SESSION['user_id'];
    
    // Check if already favorited
    $check = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?");
    $check->execute([$user_id, $recipe_id]);
    if ($check->fetch()) {
        $del = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $del->execute([$user_id, $recipe_id]);
    } else {
        $ins = $pdo->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)");
        $ins->execute([$user_id, $recipe_id]);
    }
    header("Location: browse.php?search=" . urlencode($search) . "&category=" . urlencode($category));
    exit;
}

// Fetch categories for filter dropdown
$cat_stmt = $pdo->query("SELECT DISTINCT category FROM recipes WHERE category IS NOT NULL AND category != ''");
$categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);

// Build query based on search & category
$query = "SELECT r.*";
if (isset($_SESSION['user_id'])) {
    $query .= ", (SELECT COUNT(*) FROM favorites f WHERE f.recipe_id = r.id AND f.user_id = ?) as is_fav";
}
$query .= " FROM recipes r WHERE 1=1";
$params = [];

if (isset($_SESSION['user_id'])) {
    $params[] = $_SESSION['user_id'];
}

if ($search !== '') {
    $query .= " AND (r.title LIKE ? OR r.ingredients LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($category !== '') {
    $query .= " AND r.category = ?";
    $params[] = $category;
}

$query .= " ORDER BY r.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$recipes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Browse Recipes - Culinary Nexus</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo-container">
            <div class="logo-icon">🍳</div>
            <span>CULINARY NEXUS</span>
        </a>
        <form method="GET" action="browse.php" class="search-container">
            <input type="text" name="search" placeholder="Search by title or ingredients..." value="<?= htmlspecialchars($search) ?>">
        </form>
        <ul class="nav-links">
            <li><a href="browse.php" style="color: var(--primary); font-weight: 600;">Browse Recipes</a></li>
            <li><a href="upload.php">Submit Recipe</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="favorites.php">My Favorites</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li><a href="admin.php" style="color: #b45309; font-weight: 700; background: #fef3c7; padding: 0.4rem 0.8rem; border-radius: 6px;">👑 Admin Dashboard</a></li>
                <?php endif; ?>
                <li><a href="logout.php" style="color: #dc2626; font-weight: 600;">Logout (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php" style="background: var(--primary); color: white; padding: 0.5rem 1rem; border-radius: 6px;">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="container" style="margin-top: 2rem;">
        <h1>Browse & Search Recipes</h1>
        
        <!-- Filter Form -->
        <form method="GET" action="browse.php" style="display: flex; gap: 1rem; margin: 1.5rem 0; align-items: center;">
            <input type="text" name="search" placeholder="Search keyword..." value="<?= htmlspecialchars($search) ?>" style="padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 6px; flex: 1;">
            <select name="category" style="padding: 0.6rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                <option value="">All Categories</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" style="background: var(--primary); color: white; border: none; padding: 0.6rem 1.2rem; border-radius: 6px; cursor: pointer;">Filter</button>
            <a href="browse.php" style="color: #64748b; text-decoration: none;">Reset</a>
        </form>

        <div class="grid">
            <?php if (count($recipes) > 0): ?>
                <?php foreach($recipes as $recipe): ?>
                    <div class="card" style="position: relative;">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <form method="POST" style="position: absolute; top: 10px; right: 10px; z-index: 10;">
                                <input type="hidden" name="recipe_id" value="<?= $recipe['id'] ?>">
                                <button type="submit" name="toggle_favorite" style="background: white; border: none; border-radius: 50%; width: 35px; height: 35px; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.2); font-size: 1.1rem; display: flex; align-items: center; justify-content: center;">
                                    <?= (isset($recipe['is_fav']) && $recipe['is_fav'] > 0) ? '❤️' : '🤍' ?>
                                </button>
                            </form>
                        <?php endif; ?>
                        <img src="<?= htmlspecialchars($recipe['image_url']) ?>" alt="Recipe">
                        <div class="card-body">
                            <h3><?= htmlspecialchars($recipe['title']) ?></h3>
                            <p style="font-size: 0.85rem; color: #64748b;">Category: <?= htmlspecialchars($recipe['category']) ?></p>
                            <p style="font-size: 0.85rem; margin-top: 0.5rem; color: #334155;"><strong>Ingredients:</strong> <?= htmlspecialchars(substr($recipe['ingredients'], 0, 60)) ?>...</p>
                            <div class="card-footer" style="margin-top: 1rem;">
                                <div class="author-info">
                                    <div class="author-avatar"></div>
                                    <span><?= htmlspecialchars($recipe['author_name']) ?></span>
                                </div>
                                <div style="font-weight: 600; color: #b45309;">
                                    ★ <?= $recipe['rating'] ?? '4.8' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; color: #64748b; padding: 3rem;">No recipes found matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
