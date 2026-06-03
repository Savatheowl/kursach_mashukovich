<?php
$page_title = 'Корзина — ' . SITE_NAME;
$pdo = getDB();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header('Location: index.php?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $dish_id = (int)($_POST['dish_id'] ?? 0);

    if ($action === 'increase') {
        $stmt = $pdo->prepare("SELECT c.quantity, d.in_stock FROM cart c JOIN dishes d ON c.dish_id = d.id WHERE c.user_id = ? AND c.dish_id = ?");
        $stmt->execute([$user_id, $dish_id]);
        $item = $stmt->fetch();
        if ($item && $item['quantity'] < $item['in_stock']) {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE user_id = ? AND dish_id = ?");
            $stmt->execute([$user_id, $dish_id]);
        }
    } elseif ($action === 'decrease') {
        $stmt = $pdo->prepare("SELECT quantity FROM cart WHERE user_id = ? AND dish_id = ?");
        $stmt->execute([$user_id, $dish_id]);
        $item = $stmt->fetch();
        if ($item && $item['quantity'] > 1) {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity - 1 WHERE user_id = ? AND dish_id = ?");
            $stmt->execute([$user_id, $dish_id]);
        } else {
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND dish_id = ?");
            $stmt->execute([$user_id, $dish_id]);
        }
    } elseif ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ? AND dish_id = ?");
        $stmt->execute([$user_id, $dish_id]);
    }

    header('Location: index.php?page=cart');
    exit;
}

// Get cart items
$stmt = $pdo->prepare("
    SELECT c.*, d.name, d.price, d.photo, d.in_stock 
    FROM cart c 
    JOIN dishes d ON c.dish_id = d.id 
    WHERE c.user_id = ?
    ORDER BY d.name
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
?>

<h1 class="page-title">Корзина</h1>

<?php if (empty($cart_items)): ?>
    <div class="empty-state">
        <p>Корзина пуста</p>
        <a href="index.php?page=menu" class="btn btn-primary">Перейти в меню</a>
    </div>
<?php else: ?>
<div class="cart-items">
    <?php foreach ($cart_items as $item): ?>
    <div class="cart-item">
        <div class="cart-item-info">
            <img src="<?= htmlspecialchars($item['photo']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.src='images/placeholder.svg'">
            <div class="cart-item-details">
                <h3><?= htmlspecialchars($item['name']) ?></h3>
                <span class="price"><?= number_format($item['price'], 0, ',', ' ') ?> ₽</span>
            </div>
        </div>
        <div class="cart-item-quantity">
            <form method="POST" action="index.php?page=cart" style="display:inline;">
                <input type="hidden" name="action" value="decrease">
                <input type="hidden" name="dish_id" value="<?= $item['dish_id'] ?>">
                <button type="submit" class="btn btn-secondary btn-small">-</button>
            </form>
            <span><?= $item['quantity'] ?></span>
            <form method="POST" action="index.php?page=cart" style="display:inline;">
                <input type="hidden" name="action" value="increase">
                <input type="hidden" name="dish_id" value="<?= $item['dish_id'] ?>">
                <button type="submit" class="btn btn-secondary btn-small" <?= $item['quantity'] >= $item['in_stock'] ? 'disabled' : '' ?>>+</button>
            </form>
        </div>
        <div class="cart-item-total">
            <?= number_format($item['price'] * $item['quantity'], 0, ',', ' ') ?> ₽
        </div>
        <div class="cart-item-actions">
            <form method="POST" action="index.php?page=cart" style="display:inline;">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="dish_id" value="<?= $item['dish_id'] ?>">
                <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Удалить позицию?')">Удалить</button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="cart-summary">
    <div class="total">Итого: <?= number_format($total, 0, ',', ' ') ?> ₽</div>
    <div class="actions">
        <a href="index.php?page=menu" class="btn btn-secondary">Продолжить покупки</a>
        <a href="index.php?page=checkout" class="btn btn-success">Сформировать заказ</a>
    </div>
</div>
<?php endif; ?>
