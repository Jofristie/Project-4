<?php
require_once __DIR__ . '/db_connect.php';
session_start();

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action'] ?? '';
    $product_id = (int)($_POST['product_id'] ?? 0);
    $qty        = max(1, (int)($_POST['qty'] ?? 1));

    if ($action === 'add' && $product_id > 0) {
        $stmt = $conn->prepare("SELECT id, name, price, stock, image FROM products WHERE id = ? AND active = 1");
        $stmt->bind_param('i', $product_id);
        $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();
        if ($p) {
            if (isset($_SESSION['cart'][$product_id])) {
                $new_qty = $_SESSION['cart'][$product_id]['qty'] + $qty;
                $_SESSION['cart'][$product_id]['qty'] = min($new_qty, $p['stock']);
            } else {
                $_SESSION['cart'][$product_id] = [
                    'id' => $p['id'], 'name' => $p['name'],
                    'price' => $p['price'], 'image' => $p['image'],
                    'qty' => min($qty, $p['stock']),
                ];
            }
        }
        header('Location: cart.php'); exit;
    }

    if ($action === 'update' && $product_id > 0) {
        if ($qty <= 0) unset($_SESSION['cart'][$product_id]);
        elseif (isset($_SESSION['cart'][$product_id])) $_SESSION['cart'][$product_id]['qty'] = $qty;
        header('Location: cart.php'); exit;
    }

    if ($action === 'remove' && $product_id > 0) {
        unset($_SESSION['cart'][$product_id]);
        header('Location: cart.php'); exit;
    }
}

$total = 0;
$cart  = $_SESSION['cart'];
foreach ($cart as $item) $total += $item['price'] * $item['qty'];
$cart_count = array_sum(array_column($cart, 'qty'));
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Winkelwagen — Chef's Choice</title>
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
        </nav>
        <div class="header-actions">
            <a href="cart.php" class="cart-btn active">🛒 <span class="cart-count"><?= $cart_count ?></span></a>
        </div>
    </div>
</header>

<main class="container">
    <h1 class="page-title">Uw winkelwagen</h1>

    <?php if (empty($cart)): ?>
        <div class="empty-state">
            <p>Uw winkelwagen is leeg.</p>
            <a href="webshop.php" class="btn-primary">Verder winkelen</a>
        </div>
    <?php else: ?>
    <div class="cart-layout">
        <div class="cart-items">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Product</th><th>Prijs</th><th>Aantal</th><th>Subtotaal</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart as $item): ?>
                    <tr>
                        <td class="cart-product">
                            <img src="<?= !empty($item['image']) ? '../images/products/' . htmlspecialchars($item['image']) : '../images/placeholder.jpg' ?>"
                                 alt="<?= htmlspecialchars($item['name']) ?>">
                            <span><?= htmlspecialchars($item['name']) ?></span>
                        </td>
                        <td>€<?= number_format($item['price'], 2, ',', '.') ?></td>
                        <td>
                            <form method="POST" action="cart.php" class="qty-form">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <input type="number" name="qty" value="<?= $item['qty'] ?>" min="0" max="99"
                                       onchange="this.form.submit()">
                            </form>
                        </td>
                        <td>€<?= number_format($item['price'] * $item['qty'], 2, ',', '.') ?></td>
                        <td>
                            <form method="POST" action="cart.php">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn-remove">✕</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="cart-summary">
            <h2>Overzicht</h2>
            <div class="summary-row"><span>Subtotaal</span><span>€<?= number_format($total, 2, ',', '.') ?></span></div>
            <div class="summary-row">
                <span>Verzendkosten</span>
                <span><?= $total >= 75 ? '<strong>Gratis</strong>' : '€5,95' ?></span>
            </div>
            <?php if ($total < 75): ?>
            <p class="shipping-note">Nog €<?= number_format(75 - $total, 2, ',', '.') ?> voor gratis verzending.</p>
            <?php endif; ?>
            <hr>
            <div class="summary-row total-row">
                <span>Totaal</span>
                <span>€<?= number_format($total + ($total >= 75 ? 0 : 5.95), 2, ',', '.') ?></span>
            </div>
            <a href="checkout.php" class="btn-primary btn-full">Bestelling plaatsen</a>
            <a href="webshop.php" class="btn-secondary btn-full">Verder winkelen</a>
        </div>
    </div>
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