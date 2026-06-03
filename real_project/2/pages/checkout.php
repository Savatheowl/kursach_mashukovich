<?php
$page_title = 'Оформление заказа — ' . SITE_NAME;
$pdo = getDB();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header('Location: index.php?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';

// Check if cart has items
$stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$cart_count = $stmt->fetchColumn();

if ($cart_count == 0) {
    header('Location: index.php?page=cart');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!password_verify($password, $user['password'])) {
        $error = 'Неверный пароль';
    } else {
        // Get cart items
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll();

        // Check stock availability
        $stock_ok = true;
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("SELECT in_stock FROM dishes WHERE id = ?");
            $stmt->execute([$item['dish_id']]);
            $dish = $stmt->fetch();
            if (!$dish || $dish['in_stock'] < $item['quantity']) {
                $stock_ok = false;
                $error = 'Недостаточно товара в наличии';
                break;
            }
        }

        if ($stock_ok) {
            try {
                $pdo->beginTransaction();

                // Create order
                $stmt = $pdo->prepare("INSERT INTO orders (user_id, status) VALUES (?, 'new')");
                $stmt->execute([$user_id]);
                $order_id = $pdo->lastInsertId();

                // Move cart items to order_items
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, dish_id, quantity) VALUES (?, ?, ?)");
                $update_stock = $pdo->prepare("UPDATE dishes SET in_stock = in_stock - ? WHERE id = ?");
                foreach ($cart_items as $item) {
                    $stmt->execute([$order_id, $item['dish_id'], $item['quantity']]);
                    $update_stock->execute([$item['quantity'], $item['dish_id']]);
                }

                // Clear cart
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$user_id]);

                $pdo->commit();
                header('Location: index.php?page=my-orders');
                exit;
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = 'Ошибка при оформлении заказа';
            }
        }
    }
}
?>

<div class="auth-form">
    <div class="card">
        <h2>Оформление заказа</h2>
        <p style="text-align:center;margin-bottom:15px;color:#666;">Для подтверждения заказа введите ваш пароль</p>
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="index.php?page=checkout">
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-success" style="width:100%">Подтвердить заказ</button>
        </form>
        <div class="auth-link">
            <a href="index.php?page=cart">Вернуться в корзину</a>
        </div>
    </div>
</div>
