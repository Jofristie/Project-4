<?php
require_once __DIR__ . '/db_connect.php';
session_start();

$order_id    = $_SESSION['last_order_id'] ?? 0;
$order       = null;
$order_items = [];

if ($order_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();

    $items_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $items_stmt->bind_param('i', $order_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    while ($row = $items_result->fetch_assoc()) $order_items[] = $row;

    unset($_SESSION['last_order_id']);
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestelling geplaatst — Chef's Choice</title>
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
    </div>
</header>

<main class="container">
    <div class="succes-box">
        <div class="succes-icon">✓</div>
        <h1>Bedankt voor uw bestelling!</h1>

        <?php if ($order): ?>
        <p>Bestelling <strong>#<?= $order['id'] ?></strong> is geplaatst.<br>
        Bevestiging verstuurd naar <strong><?= htmlspecialchars($order['email']) ?></strong>.</p>

        <div class="order-summary-box">
            <h2>Besteldetails</h2>
            <div class="order-meta">
                <p><strong>Bezorgadres:</strong> <?= htmlspecialchars($order['adres']) ?>, <?= htmlspecialchars($order['postcode']) ?> <?= htmlspecialchars($order['stad']) ?></p>
            </div>
            <table class="cart-table">
                <thead>
                    <tr><th>Product</th><th>Aantal</th><th>Prijs</th><th>Subtotaal</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($order_items as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']) ?></td>
                        <td><?= $item['qty'] ?></td>
                        <td>€<?= number_format($item['prijs'], 2, ',', '.') ?></td>
                        <td>€<?= number_format($item['prijs'] * $item['qty'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr><td colspan="3"><strong>Verzendkosten</strong></td><td>€<?= number_format($order['verzendkosten'], 2, ',', '.') ?></td></tr>
                    <tr class="total-row"><td colspan="3"><strong>Totaal</strong></td><td><strong>€<?= number_format($order['totaal'], 2, ',', '.') ?></strong></td></tr>
                </tfoot>
            </table>
        </div>
        <?php else: ?>
        <p>Uw bestelling is verwerkt.</p>
        <?php endif; ?>

        <div class="succes-actions">
            <a href="webshop.php" class="btn-primary">Verder winkelen</a>
            <a href="../Html/index.html" class="btn-secondary">Terug naar home</a>
        </div>
    </div>
</main>

<footer class="site-footer">
    <div class="container footer-inner">
        <img src="../images/logo.png" alt="Chef's Choice" class="footer-logo">
        <p class="footer-copy">© <?= date('Y') ?> Chef's Choice.</p>
    </div>
</footer>
</body>
</html>