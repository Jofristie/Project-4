<?php
require_once __DIR__ . '/db_connect.php';
session_start();

$products = [];
$result = $conn->query("SELECT p.*, c.name AS category_name 
                         FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id
                         WHERE p.active = 1
                         ORDER BY c.name ASC, p.name ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[$row['category_name']][] = $row;
    }
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
    <title>Alle producten — Chef's Choice</title>
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
            <a href="cart.php" class="cart-btn">🛒 <span class="cart-count"><?= $cart_count ?></span></a>
        </div>
    </div>
</header>

<main class="container">
    <h1 class="page-title">Alle producten</h1>
    <?php if (empty($products)): ?>
        <div class="empty-state"><p>Geen producten beschikbaar.</p></div>
    <?php else: ?>
        <?php foreach ($products as $category => $items): ?>
        <section class="category-section">
            <h2 class="category-heading"><?= htmlspecialchars($category ?: 'Overig') ?></h2>
            <div class="product-grid">
                <?php foreach ($items as $p): ?>
                <article class="product-card">
                    <a href="product.php?id=<?= $p['id'] ?>" class="product-img-link">
                        <img src="<?= !empty($p['image']) ? '../images/' . htmlspecialchars($p['image']) : '../images/placeholder.jpg' ?>"
                             alt="<?= htmlspecialchars($p['name']) ?>" class="product-img">
                    </a>
                    <div class="product-info">
                        <h3 class="product-name">
                            <a href="product.php?id=<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></a>
                        </h3>
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
                                <span class="badge-out">Uitverkocht</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endforeach; ?>
    <?php endif; ?>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <img src="../images/logo.png" alt="Chef's Choice" class="footer-logo">
        <p class="footer-copy">© <?= date('Y') ?> Chef's Choice.</p>
    </div>
</footer>
<script src="../Js/Placeholder.js"></script>
</body>
</html>