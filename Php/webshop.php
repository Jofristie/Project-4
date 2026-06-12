<?php
require_once __DIR__ . '/db_connect.php';
session_start();

$categories = [];
$cat_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
if ($cat_result) {
    while ($row = $cat_result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$selected_cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$where = "WHERE p.active = 1";
if ($selected_cat > 0) $where .= " AND p.category_id = $selected_cat";
if (!empty($search))   $where .= " AND (p.name LIKE '%$search%' OR p.description LIKE '%$search%')";

$products = [];
$result = $conn->query("SELECT p.*, c.name AS category_name 
                         FROM products p
                         LEFT JOIN categories c ON p.category_id = c.id
                         $where
                         ORDER BY p.featured DESC, p.name ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) $products[] = $row;
}

$cart_count = 0;
if (isset($_SESSION['cart'])) {
    $cart_count = array_sum(array_column($_SESSION['cart'], 'qty'));
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chef's Choice — Webshop</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="webshop.php" class="logo-link">
            <img src="../images/logo.png" alt="Chef's Choice" class="logo">
        </a>
        <nav class="main-nav">
            <a href="webshop.php">Webshop</a>
            <a href="../Html/index.html">Home</a>
            <a href="../Html/menu.html">Over ons</a>
        </nav>
        <div class="header-actions">
            <form method="GET" action="webshop.php" class="search-form">
                <input type="text" name="search" placeholder="Zoek producten..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit">🔍</button>
            </form>
            <a href="cart.php" class="cart-btn">
                🛒 <span class="cart-count"><?= $cart_count ?></span>
            </a>
        </div>
    </div>
</header>

<?php if ($selected_cat === 0 && empty($search)): ?>
<section class="hero">
    <div class="hero-content">
        <p class="hero-eyebrow">Professioneel koksgereedschap</p>
        <h1>De keuze van<br><em>echte chefs</em></h1>
        <p class="hero-sub">Van messen tot pannen — alles wat een professional nodig heeft.</p>
        <a href="#products" class="btn-primary">Bekijk assortiment</a>
    </div>
</section>
<?php endif; ?>

<section class="category-bar">
    <div class="container">
        <a href="webshop.php" class="cat-pill <?= $selected_cat === 0 ? 'active' : '' ?>">Alle producten</a>
        <?php foreach ($categories as $cat): ?>
            <a href="webshop.php?cat=<?= $cat['id'] ?>"
               class="cat-pill <?= $selected_cat === $cat['id'] ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<main class="container" id="products">
    <?php if (empty($products)): ?>
        <div class="empty-state">
            <p>Geen producten gevonden.</p>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($products as $p): ?>
            <article class="product-card <?= $p['featured'] ? 'featured' : '' ?>">
                <?php if ($p['featured']): ?>
                    <span class="badge-featured">Uitgelicht</span>
                <?php endif; ?>
                <?php if ($p['stock'] <= 0): ?>
                    <span class="badge-out">Uitverkocht</span>
                <?php endif; ?>
                <a href="product.php?id=<?= $p['id'] ?>" class="product-img-link">
                    <img src="<?= !empty($p['image']) ? '../images/products/' . htmlspecialchars($p['image']) : '../images/placeholder.jpg' ?>"
                         alt="<?= htmlspecialchars($p['name']) ?>" class="product-img">
                </a>
                <div class="product-info">
                    <span class="product-cat"><?= htmlspecialchars($p['category_name'] ?? '') ?></span>
                    <h2 class="product-name">
                        <a href="product.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
                    </h2>
                    <p class="product-desc"><?= htmlspecialchars(substr($p['description'] ?? '', 0, 80)) ?>...</p>
                    <div class="product-footer">
                        <span class="price">€<?= number_format($p['price'], 2, ',', '.') ?></span>
                        <?php if ($p['stock'] > 0): ?>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn-cart">In winkelwagen</button>
                            </form>
                        <?php else: ?>
                            <button class="btn-cart disabled" disabled>Uitverkocht</button>
                        <?php endif; ?>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <img src="../images/logo.png" alt="Chef's Choice" class="footer-logo">
        <div class="footer-links">
            <a href="#">Privacybeleid</a>
            <a href="#">Leveringsinfo</a>
            <a href="#">Contact</a>
        </div>
        <p class="footer-copy">© <?= date('Y') ?> Chef's Choice. Alle rechten voorbehouden.</p>
    </div>
</footer>

<script src="../Js/Placeholder.js"></script>
</body>
</html>