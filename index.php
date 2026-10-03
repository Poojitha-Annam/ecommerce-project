
<?php
session_start();
include 'includes/db.php';

// Get search and category filters
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

// Fetch available categories
$categoryStmt = $conn->query(
    "SELECT DISTINCT category
     FROM products
     WHERE category IS NOT NULL AND category != ''
     ORDER BY category ASC"
);
$categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);

// Build product query
$sql = "SELECT * FROM products WHERE 1=1";
$params = [];

if ($search !== '') {
    $sql .= " AND (name LIKE :search_name
               OR description LIKE :search_description)";
    $params['search_name'] = '%' . $search . '%';
    $params['search_description'] = '%' . $search . '%';
}

if ($category !== '' && in_array($category, $categories, true)) {
    $sql .= " AND category = :category";
    $params['category'] = $category;
}

$sql .= " ORDER BY name ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Store</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

<header>
    <div class="header-container">
        <h1>Welcome to Our Store</h1>

        <nav>
            <a href="pages/login.php">Login</a>
            <a href="pages/register.php">Register</a>

            <a href="pages/cart.php" class="cart-link">
                <img src="images/cart-icon.png" alt="Cart" class="cart-icon">
                Cart
            </a>

            <form method="POST" style="display: inline;">
                <button type="submit" name="logout" class="logout-button">
                    Logout
                </button>
            </form>
        </nav>
    </div>
</header>

<div class="main-container">
    <main>
        <h2>Products</h2>

        <!-- Search and category filters -->
        <form method="GET" action="index.php" class="search-form">

            <input
                type="text"
                name="search"
                placeholder="Search products..."
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
            >

            <select name="category">
                <option value="">All Categories</option>

                <?php foreach ($categories as $cat) : ?>
                    <option
                        value="<?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>"
                        <?= $category === $cat ? 'selected' : ''; ?>
                    >
                        <?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Search / Filter</button>

            <?php if ($search !== '' || $category !== '') : ?>
                <a href="index.php">Show All Products</a>
            <?php endif; ?>

        </form>

        <div class="product-list">

            <?php if (empty($products)) : ?>

                <p>
                    <?php if ($search !== '' || $category !== '') : ?>
                        No products match your search or selected category.
                    <?php else : ?>
                        No products available.
                    <?php endif; ?>
                </p>

            <?php else : ?>

                <?php foreach ($products as $product) : ?>
                    <div class="product">

                        <h3>
                            <?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>
                        </h3>

                        <p>
                            Price: $<?= number_format((float) $product['price'], 2); ?>
                        </p>

                        <p>
                            <?= htmlspecialchars($product['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </p>

                        <?php if (!empty($product['category'])) : ?>
                            <p>
                                Category:
                                <?= htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($product['image'])) : ?>
                            <img
                                src="images/<?= htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>"
                                alt="<?= htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                                class="product-image"
                            >
                        <?php endif; ?>

                        <form method="POST" action="pages/cart.php">
                            <input
                                type="hidden"
                                name="product_id"
                                value="<?= (int) $product['id']; ?>"
                            >

                            <button
                                type="submit"
                                name="add_to_cart"
                                class="add-to-cart-button"
                            >
                                Add to Cart
                            </button>
                        </form>

                    </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </main>
</div>

<footer>
    <p>&copy; <?= date('Y'); ?> Online Store. All rights reserved.</p>
</footer>

</body>
</html>