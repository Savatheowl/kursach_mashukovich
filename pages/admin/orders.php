<?php
$pdo = getDB();
$message = '';

// Handle confirm
if (isset($_GET['confirm']) && (int)$_GET['confirm'] > 0) {
    $id = (int)$_GET['confirm'];
    $stmt = $pdo->prepare("UPDATE orders SET status = 'confirmed' WHERE id = ? AND status = 'new'");
    $stmt->execute([$id]);
    $message = 'Заказ подтверждён';
}

// Handle cancel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $id = (int)$_POST['order_id'];
    $reason = trim($_POST['cancel_reason'] ?? '');
    if (!empty($reason)) {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled', cancel_reason = ? WHERE id = ? AND status = 'new'");
        $stmt->execute([$reason, $id]);

        // Restore stock
        $stmt = $pdo->prepare("SELECT dish_id, quantity FROM order_items WHERE order_id = ?");
        $stmt->execute([$id]);
        $items = $stmt->fetchAll();
        $restore = $pdo->prepare("UPDATE dishes SET in_stock = in_stock + ? WHERE id = ?");
        foreach ($items as $item) {
            $restore->execute([$item['quantity'], $item['dish_id']]);
        }

        $message = 'Заказ отменён';
    }
}

// Filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'new';
$allowed_statuses = ['new', 'confirmed', 'cancelled'];
if (!in_array($status_filter, $allowed_statuses)) {
    $status_filter = 'new';
}

$stmt = $pdo->prepare("
    SELECT o.*, u.name, u.surname, u.patronymic,
           (SELECT SUM(oi.quantity) FROM order_items oi WHERE oi.order_id = o.id) as total_items
    FROM orders o
    JOIN users u ON o.user_id = u.id
    WHERE o.status = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$status_filter]);
$orders = $stmt->fetchAll();

// Get order items
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

<h1 class="page-title">Управление заказами</h1>

<?php if ($message): ?>
    <div class="success-message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="filters">
    <form method="GET" action="index.php">
        <input type="hidden" name="page" value="admin">
        <input type="hidden" name="admin_page" value="orders">
        <div class="form-group">
            <label for="status">Статус</label>
            <select name="status" id="status">
                <option value="new" <?= $status_filter === 'new' ? 'selected' : '' ?>>Новые</option>
                <option value="confirmed" <?= $status_filter === 'confirmed' ? 'selected' : '' ?>>Подтверждённые</option>
                <option value="cancelled" <?= $status_filter === 'cancelled' ? 'selected' : '' ?>>Отменённые</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Фильтр</button>
    </form>
</div>

<?php if (empty($orders)): ?>
    <div class="empty-state">
        <p>Заказы не найдены</p>
    </div>
<?php else: ?>
    <?php foreach ($orders as $order): ?>
    <div class="order-card">
        <div class="order-header">
            <span class="order-number">Заказ №<?= $order['id'] ?> — <?= htmlspecialchars($order['surname'] . ' ' . $order['name'] . ($order['patronymic'] ? ' ' . $order['patronymic'] : '')) ?></span>
            <span class="order-status status-<?= $order['status'] ?>">
                <?php $statuses = ['new' => 'Новый', 'confirmed' => 'Подтверждён', 'cancelled' => 'Отменён'];
                echo $statuses[$order['status']]; ?>
            </span>
        </div>
        <p style="font-size:14px;color:#666;margin-bottom:10px;"><?= date('d.m.Y H:i', strtotime($order['created_at'])) ?> — Всего позиций: <?= $order['total_items'] ?></p>
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
        <div class="order-total" style="margin-top:10px;">
            <?php 
            $total = 0;
            if (isset($order_items[$order['id']])) {
                foreach ($order_items[$order['id']] as $item) {
                    $total += $item['price'] * $item['quantity'];
                }
            }
            ?>
            Итого: <?= number_format($total, 0, ',', ' ') ?> ₽
        </div>
        <?php if ($order['status'] === 'new'): ?>
        <div style="margin-top:15px;display:flex;gap:10px;justify-content:flex-end;align-items:flex-start;">
            <a href="index.php?page=admin&admin_page=orders&confirm=<?= $order['id'] ?>" class="btn btn-success btn-small" onclick="return confirm('Подтвердить заказ?')">Подтвердить</a>
            <form method="POST" action="index.php?page=admin&admin_page=orders" style="display:flex;gap:8px;align-items:flex-end;">
                <input type="hidden" name="action" value="cancel">
                <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                <div class="form-group" style="margin:0;">
                    <input type="text" name="cancel_reason" placeholder="Причина отказа" required>
                </div>
                <button type="submit" class="btn btn-danger btn-small" onclick="return confirm('Отменить заказ?')">Отменить</button>
            </form>
        </div>
        <?php elseif ($order['status'] === 'cancelled' && $order['cancel_reason']): ?>
            <p style="color:#e74c3c;font-size:14px;margin-top:10px;"><strong>Причина:</strong> <?= htmlspecialchars($order['cancel_reason']) ?></p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
<?php endif; ?>
