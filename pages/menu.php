<?php
$page_title = 'Меню — ' . SITE_NAME;
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
        $new_qty = $cart_item['quantity'] + 1;
        if ($new_qty <= $dish['in_stock']) {
            $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
            $stmt->execute([$cart_item['id']]);
        }
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, dish_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $dish_id]);
    }

    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : 'index.php?page=menu';
    header("Location: $redirect");
    exit;
}

// Sorting & filtering
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC' ? 'ASC' : 'DESC';
$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$allowed_sorts = ['name', 'price', 'country', 'created_at'];
if (!in_array($sort, $allowed_sorts)) {
    $sort = 'created_at';
}

$sql = "SELECT d.*, c.name as category_name FROM dishes d JOIN categories c ON d.category_id = c.id WHERE d.is_published = 1 AND d.in_stock > 0";
$params = [];

if ($category_filter > 0) {
    $sql .= " AND d.category_id = ?";
    $params[] = $category_filter;
}

$sql .= " ORDER BY d.$sort $order";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$dishes = $stmt->fetchAll();

// Get categories for filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<h1 class="page-title">Меню</h1>

<div class="filters">
    <form method="GET" action="index.php">
        <input type="hidden" name="page" value="menu">
        <div class="form-group">
            <label for="sort">Сортировка</label>
            <select name="sort" id="sort">
                <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>Новые</option>
                <option value="name" <?= $sort === 'name' ? 'selected' : '' ?>>Названию</option>
                <option value="price" <?= $sort === 'price' ? 'selected' : '' ?>>Цене</option>
                <option value="country" <?= $sort === 'country' ? 'selected' : '' ?>>Стране</option>
            </select>
        </div>
        <div class="form-group">
            <label for="order">Порядок</label>
            <select name="order" id="order">
                <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>По убыванию</option>
                <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>По возрастанию</option>
            </select>
        </div>
        <div class="form-group">
            <label for="category">Категория</label>
            <select name="category" id="category">
                <option value="0">Все категории</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= $category_filter === (int)$cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Применить</button>
    </form>
</div>

<?php if (empty($dishes)): ?>
    <div class="empty-state">
        <p>Блюда не найдены</p>
    </div>
<?php else: ?>
<div class="dishes-grid">
    <?php foreach ($dishes as $dish): ?>
    <div class="dish-card">
        <a href="index.php?page=dish&id=<?= $dish['id'] ?>">
            <img src="<?= htmlspecialchars($dish['photo']) ?>" alt="<?= htmlspecialchars($dish['name']) ?>" onerror="this.src='images/placeholder.svg'">
            <div class="dish-info">
                <h3><?= htmlspecialchars($dish['name']) ?></h3>
                <p class="price"><?= number_format($dish['price'], 0, ',', ' ') ?> ₽</p>
            </div>
        </a>
        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'client'): ?>
        <div class="dish-actions">
            <form method="POST" action="index.php?page=menu">
                <input type="hidden" name="action" value="add_to_cart">
                <input type="hidden" name="dish_id" value="<?= $dish['id'] ?>">
                <input type="hidden" name="redirect" value="index.php?page=menu">
                <button type="submit" class="btn btn-primary btn-small">В корзину</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
