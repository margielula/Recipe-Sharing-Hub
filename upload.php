<?php
require_once 'db.php';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $category = trim($_POST['category']);
    $ingredients = trim($_POST['ingredients']);
    $instructions = trim($_POST['instructions']);
    $image_url = trim($_POST['image_url']) ?: 'https://images.unsplash.com/photo-1498837167922-ddd27525d352';
    $author = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Home Cook';
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

    if (!empty($title) && !empty($ingredients) && !empty($instructions)) {
        $stmt = $pdo->prepare("INSERT INTO recipes (title, category, ingredients, instructions, image_url, author_name, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $category, $ingredients, $instructions, $image_url, $author, $user_id]);
        $msg = "Recipe published successfully!";
    } else {
        $msg = "Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Submit Recipe - Culinary Nexus</title><link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <a href="index.php" class="logo-container"><div class="logo-icon">🍳</div><span>CULINARY NEXUS</span></a>
        <ul class="nav-links">
            <li><a href="index.php">Browse Recipes</a></li>
            <li><a href="upload.php">Submit Recipe</a></li>
        </ul>
    </nav>

    <div class="form-wrapper" style="max-width: 600px;">
        <h2>Submit a New Recipe</h2>
        <?php if($msg): ?><p style="color: green; margin-bottom: 1rem;"><?= $msg ?></p><?php endif; ?>
        <form method="POST">
            <div class="form-group"><label>Recipe Title</label><input type="text" name="title" required></div>
            <div class="form-group">
                <label>Category</label>
                <select name="category">
                    <option value="Breakfast">Breakfast</option>
                    <option value="Vegan">Vegan</option>
                    <option value="Healthy">Healthy</option>
                    <option value="Dessert">Dessert</option>
                </select>
            </div>
            <div class="form-group"><label>Ingredients (comma separated)</label><textarea name="ingredients" rows="3" required></textarea></div>
            <div class="form-group"><label>Cooking Instructions</label><textarea name="instructions" rows="4" required></textarea></div>
            <div class="form-group"><label>Image URL (Optional)</label><input type="text" name="image_url" placeholder="https://..."></div>
            <button type="submit" class="btn-submit">Publish Recipe</button>
        </form>
    </div>
</body>
</html>
