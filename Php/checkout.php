<?php
require_once __DIR__ . '/db_connect.php';
session_start();

if (empty($_SESSION['cart'])) { header('Location: cart.php'); exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $naam      = trim($_POST['naam'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telefoon  = trim($_POST['telefoon'] ?? '');
    $adres     = trim($_POST['adres'] ?? '');
    $postcode  = trim($_POST['postcode'] ?? '');
    $stad      = trim($_POST['stad'] ?? '');
    $opmerking = trim($_POST['opmerking'] ?? '');

    if (empty($naam))     $errors[] = 'Naam is verplicht.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Ongeldig e-mailadres.';
    if (empty($adres))    $errors[] = 'Adres is verplicht.';
    if (empty($postcode)) $errors[] = 'Postcode is verplicht.';
    if (empty($stad))     $errors[] = 'Stad is verplicht.';

    if (empty($errors)) {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) $total += $item['price'] * $item['qty'];
        $verzendkosten = $total >= 75 ? 0 : 5.95;
        $totaal_incl   = $total + $verzendkosten;

        $stmt = $conn->prepare("INSERT INTO orders (naam, email, telefoon, adres, postcode, stad, opmerking, totaal, verzendkosten, status, aangemaakt)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'nieuw', NOW())");
        $stmt->bind_param('sssssssdd', $naam, $email, $telefoon, $adres, $postcode, $stad, $opmerking, $totaal_incl, $verzendkosten);
        $stmt->execute();
        $order_id = $stmt->insert_id;

        $line = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, qty, prijs) VALUES (?, ?, ?, ?, ?)");
        foreach ($_SESSION['cart'] as $item) {
            $line->bind_param('iisid', $order_id, $item['id'], $item['name'], $item['qty'], $item['price']);
            $line->execute();
            $conn->query("UPDATE products SET stock = stock - {$item['qty']} WHERE id = {$item['id']} AND stock >= {$item['qty']}");
        }

        $_SESSION['cart'] = [];
        $_SESSION['last_order_id'] = $order_id;
        header('Location: succes.php'); exit;
    }
}

$total = 0;
foreach ($_SESSION['cart'] as $item) $total += $item['price'] * $item['qty'];
$verzendkosten = $total >= 75 ? 0 : 5.95;
$cart_count = array_sum(array_column($_SESSION['cart'], 'qty'));
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Afrekenen — Chef's Choice</title>
    <link rel="stylesheet" href="../Css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>

<header class="site-header">
    <div class="header-inner">
        <a href="webshop.php" class="logo-link">
            <img src="../images/logo.png" alt="Chef's Choice" class="logo">
        </a>
        <nav class="main-nav"><a href="webshop.php">Webshop</a></nav>
        <div class="header-actions">
            <a href="cart.php" class="cart-btn">🛒 <span class="cart-count"><?= $cart_count ?></span></a>
        </div>
    </div>
</header>

<main class="container">
    <h1 class="page-title">Afrekenen</h1>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-error">
        <ul><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
    <?php endif; ?>

    <div class="checkout-layout">
        <form method="POST" action="checkout.php" class="checkout-form">
            <fieldset>
                <legend>Uw gegevens</legend>
                <div class="form-row">
                    <label>Volledige naam *</label>
                    <input type="text" name="naam" value="<?= htmlspecialchars($_POST['naam'] ?? '') ?>" required>
                </div>
                <div class="form-row">
                    <label>E-mailadres *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-row">
                    <label>Telefoonnummer</label>
                    <input type="tel" name="telefoon" value="<?= htmlspecialchars($_POST['telefoon'] ?? '') ?>">
                </div>
            </fieldset>
            <fieldset>
                <legend>Bezorgadres</legend>
                <div class="form-row">
                    <label>Straat + huisnummer *</label>
                    <input type="text" name="adres" value="<?= htmlspecialchars($_POST['adres'] ?? '') ?>" required>
                </div>
                <div class="form-row-half">
                    <div class="form-row">
                        <label>Postcode *</label>
                        <input type="text" name="postcode" value="<?= htmlspecialchars($_POST['postcode'] ?? '') ?>" required>
                    </div>
                    <div class="form-row">
                        <label>Plaats *</label>
                        <input type="text" name="stad" value="<?= htmlspecialchars($_POST['stad'] ?? '') ?>" required>
                    </div>
                </div>
            </fieldset>
            <fieldset>
                <legend>Opmerking (optioneel)</legend>
                <div class="form-row">
                    <textarea name="opmerking" rows="3"><?= htmlspecialchars($_POST['opmerking'] ?? '') ?></textarea>
                </div>
            </fieldset>
            <button type="submit" class="btn-primary btn-large btn-full">Bestelling bevestigen</button>
        </form>

        <div class="checkout-summary">
            <h2>Uw bestelling</h2>
            <?php foreach ($_SESSION['cart'] as $item): ?>
            <div class="checkout-item">
                <span><?= htmlspecialchars($item['name']) ?> <em>× <?= $item['qty'] ?></em></span>
                <span>€<?= number_format($item['price'] * $item['qty'], 2, ',', '.') ?></span>
            </div>
            <?php endforeach; ?>
            <hr>
            <div class="summary-row"><span>Subtotaal</span><span>€<?= number_format($total, 2, ',', '.') ?></span></div>
            <div class="summary-row">
                <span>Verzendkosten</span>
                <span><?= $verzendkosten === 0 ? '<strong>Gratis</strong>' : '€' . number_format($verzendkosten, 2, ',', '.') ?></span>
            </div>
            <hr>
            <div class="summary-row total-row">
                <span>Totaal</span>
                <span>€<?= number_format($total + $verzendkosten, 2, ',', '.') ?></span>
            </div>
        </div>
    </div>
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