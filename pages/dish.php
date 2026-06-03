<?php
$page_title = 'Блюдо — ' . SITE_NAME;
$pdo = getDB();

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'client') {
        header('Location: index.php?page=login');
        exit;
    }
    $dish_id = (int)$_POST['dish_id'];
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND dish_id = ?");
    $stmt->execute([$user_id, $dish_id]);
    $cart_item = $stmt->fetch();

    if ($cart_item) {
        $stmt = $pdo->prepare("SELECT in_stock FROM dishes WHERE id = ?");
        $stmt->execute([$dish_id]);
        $dish = $stmt->fetch();
        if ($cart_item['quantity'] + 1 <= $dish['in_stock']) {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
            $stmt->execute([$cart_item['id']]);
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, dish_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $dish_id]);
    }

    header('Location: index.php?page=dish&id=' . $dish_id);
    exit;
}

$dish_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($dish_id <= 0) {
    header('Location: index.php?page=menu');
    exit;
}

$stmt = $pdo->prepare("SELECT d.*, c.name as category_name FROM dishes d JOIN categories c ON d.category_id = c.id WHERE d.id = ?");
$stmt->execute([$dish_id]);
$dish = $stmt->fetch();

if (!$dish) {
    header('Location: index.php?page=menu');
    exit;
}
?>

<h1 class="page-title"><?= htmlspecialchars($dish['name']) ?></h1>

<div class="dish-detail">
    <div>
        <img src="<?= htmlspecialchars($dish['photo']) ?>" alt="<?= htmlspecialchars($dish['name']) ?>" onerror="this.src='images/placeholder.svg'">
    </div>
    <div>
        <h2><?= htmlspecialchars($dish['name']) ?></h2>
        <p class="price" style="font-size:28px;color:#c0392b;font-weight:bold;margin:15px 0;"><?= number_format($dish['price'], 0, ',', ' ') ?> ₽</p>
        <div class="dish-meta">
            <p><strong>Страна:</strong> <?= htmlspecialchars($dish['country']) ?></p>
            <p><strong>Категория:</strong> <?= htmlspecialchars($dish['category_name']) ?></p>
            <p><strong>В наличии:</strong> <?= $dish['in_stock'] ?> шт.</p>
        </div>
        <div>
            <h3>Состав:</h3>
            <p><?= nl2br(htmlspecialchars($dish['composition'])) ?></p>
        </div>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'client'): ?>
        <form method="POST" action="index.php?page=dish&id=<?= $dish['id'] ?>" style="margin-top:20px;">
            <input type="hidden" name="action" value="add_to_cart">
            <input type="hidden" name="dish_id" value="<?= $dish['id'] ?>">
            <button type="submit" class="btn btn-primary">В корзину</button>
        </form>
        <?php endif; ?>
    </div>
</div>
