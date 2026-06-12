<?php
require_once __DIR__ . '/db_connect.php';
session_start();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: webshop.php'); exit; }

$stmt = $conn->prepare("SELECT p.*, c.name AS category_name 
                         FROM products p 
                         LEFT JOIN categories c ON p.category_id = c.id
                         WHERE p.id = ? AND p.active = 1");
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) { header('Location: webshop.php'); exit; }

$related = [];
$rel = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND active = 1 LIMIT 4");
$rel->bind_param('ii', $product['category_id'], $id);
$rel->execute();
$rel_result = $rel->get_result();
while ($row = $rel_result->fetch_assoc()) $related[] = $row;

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
    <title><?= htmlspecialchars($product['name']) ?> — Chef's Choice</title>
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
    <nav class="breadcrumb">
        <a href="webshop.php">Webshop</a> &rsaquo;
        <a href="webshop.php?cat=<?= $product['category_id'] ?>"><?= htmlspecialchars($product['category_name'] ?? '') ?></a> &rsaquo;
        <span><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <div class="product-detail">
        <div class="product-detail-img">
            <img src="<?= !empty($product['image']) ? '../images/products/' . htmlspecialchars($product['image']) : '../images/placeholder.jpg' ?>"
                 alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
        <div class="product-detail-info">
            <span class="product-cat"><?= htmlspecialchars($product['category_name'] ?? '') ?></span>
            <h1><?= htmlspecialchars($product['name']) ?></h1>
            <p class="detail-price">€<?= number_format($product['price'], 2, ',', '.') ?></p>
            <p class="detail-desc"><?= nl2br(htmlspecialchars($product['description'] ?? '')) ?></p>

            <?php if (!empty($product['specs'])): ?>
            <div class="product-specs">
                <h3>Specificaties</h3>
                <?= nl2br(htmlspecialchars($product['specs'])) ?>
            </div>
            <?php endif; ?>

            <div class="stock-status <?= $product['stock'] > 0 ? 'in-stock' : 'out-stock' ?>">
                <?= $product['stock'] > 0 ? "✓ Op voorraad ({$product['stock']} stuks)" : "✗ Uitverkocht" ?>
            </div>

            <?php if ($product['stock'] > 0): ?>
            <form method="POST" action="cart.php" class="add-to-cart-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <div class="qty-row">
                    <label for="qty">Aantal:</label>
                    <input type="number" name="qty" id="qty" value="1" min="1" max="<?= $product['stock'] ?>">
                </div>
                <button type="submit" class="btn-primary btn-large">In winkelwagen</button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!empty($related)): ?>
    <section class="related-products">
        <h2>Gerelateerde producten</h2>
        <div class="product-grid">
            <?php foreach ($related as $r): ?>
            <article class="product-card">
                <a href="product.php?id=<?= $r['id'] ?>" class="product-img-link">
                    <img src="<?= !empty($r['image']) ? '../images/products/' . htmlspecialchars($r['image']) : '../images/placeholder.jpg' ?>"
                         alt="<?= htmlspecialchars($r['name']) ?>" class="product-img">
                </a>
                <div class="product-info">
                    <h2 class="product-name">
                        <a href="product.php?id=<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></a>
                    </h2>
                    <div class="product-footer">
                        <span class="price">€<?= number_format($r['price'], 2, ',', '.') ?></span>
                        <a href="product.php?id=<?= $r['id'] ?>" class="btn-cart">Bekijk</a>
                    </div>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
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