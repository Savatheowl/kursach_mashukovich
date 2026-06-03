<?php
$page_title = 'Мои заказы — ' . SITE_NAME;
$pdo = getDB();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
    header('Location: index.php?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle cancel order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $order_id = (int)$_POST['order_id'];
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', cancel_reason = ? WHERE id = ? AND user_id = ? AND status = 'new'");
    $stmt->execute(['Отменён пользователем', $order_id, $user_id]);

    // Restore stock
    $stmt = $pdo->prepare("SELECT dish_id, quantity FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();
    $restore = $pdo->prepare("UPDATE dishes SET in_stock = in_stock + ? WHERE id = ?");
    foreach ($items as $item) {
        $restore->execute([$item['quantity'], $item['dish_id']]);
    }

    header('Location: index.php?page=my-orders');
    exit;
}

// Get orders
$stmt = $pdo->prepare("
    SELECT o.*, 
           (SELECT SUM(oi.quantity * d.price) FROM order_items oi JOIN dishes d ON oi.dish_id = d.id WHERE oi.order_id = o.id) as total
    FROM orders o 
    WHERE o.user_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Get order items for each order
$order_items = [];
if (!empty($orders)) {
    $ids = array_column($orders, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("
        SELECT oi.*, d.name, d.price 
        FROM order_items oi 
        JOIN dishes d ON oi.dish_id = d.id 
        WHERE oi.order_id IN ($placeholders)
        ORDER BY d.name
    ");
    $stmt->execute($ids);
    $items = $stmt->fetchAll();
    foreach ($items as $item) {
        $order_items[$item['order_id']][] = $item;
    }
}
?>

<h1 class="page-title">Мои заказы</h1>

<?php if (empty($orders)): ?>
    <div class="empty-state">
        <p>У вас ещё нет заказов</p>
        <a href="index.php?page=menu" class="btn btn-primary">Перейти в меню</a>
    </div>
<?php else: ?>
<div class="orders-list">
    <?php foreach ($orders as $order): ?>
    <div class="order-card">
        <div class="order-header">
            <span class="order-number">Заказ №<?= $order['id'] ?> от <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></span>
            <span class="order-status status-<?= $order['status'] ?>">
                <?php
                $statuses = ['new' => 'Новый', 'confirmed' => 'Подтверждён', 'cancelled' => 'Отменён'];
                echo $statuses[$order['status']];
                ?>
            </span>
        </div>
        <div class="order-items">
            <?php if (isset($order_items[$order['id']])): ?>
                <?php foreach ($order_items[$order['id']] as $item): ?>
                <div class="order-item-row">
                    <span><?= htmlspecialchars($item['name']) ?> × <?= $item['quantity'] ?></span>
                    <span><?= number_format($item['price'] * $item['quantity'], 0, ',', ' ') ?> ₽</span>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php if ($order['status'] === 'cancelled' && $order['cancel_reason']): ?>
            <p style="color:#e74c3c;font-size:14px;margin-bottom:10px;"><strong>Причина:</strong> <?= htmlspecialchars($order['cancel_reason']) ?></p>
        <?php endif; ?>
        <div class="order-total">
            Итого: <?= number_format($order['total'] ?? 0, 0, ',', ' ') ?> ₽
        </div>
        <?php if ($order['status'] === 'new'): ?>
        <form method="POST" action="index.php?page=my-orders" style="margin-top:10px;text-align:right;">
            <input type="hidden" name="action" value="cancel">
            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
            <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Отменить заказ?')">Отменить заказ</button>
        </form>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
