<?php
$pdo = getDB();
$message = '';
$error = '';

// Handle delete
if (isset($_GET['delete']) && (int)$_GET['delete'] > 0) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("SELECT photo FROM dishes WHERE id = ?");
        $stmt->execute([$id]);
        $dish = $stmt->fetch();
        if ($dish && $dish['photo'] !== 'images/placeholder.svg' && file_exists($dish['photo'])) {
            unlink($dish['photo']);
        }
        $pdo->prepare("DELETE FROM dishes WHERE id = ?")->execute([$id]);
        $message = 'Блюдо удалено';
    } catch (PDOException $e) {
        $error = 'Нельзя удалить блюдо, оно есть в заказах или корзине';
    }
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $country = trim($_POST['country'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $composition = trim($_POST['composition'] ?? '');
    $in_stock = (int)($_POST['in_stock'] ?? 0);
    $is_published = isset($_POST['is_published']) ? 1 : 0;
    $photo = $_POST['current_photo'] ?? '';

    // Handle file upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('dish_') . '.' . $ext;
        $dest = UPLOAD_DIR . $filename;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $dest)) {
            // Delete old photo if exists
            if ($photo && $photo !== 'images/placeholder.svg' && file_exists($photo)) {
                unlink($photo);
            }
            $photo = $dest;
        }
    } elseif (empty($photo)) {
        $photo = 'images/placeholder.svg';
    }

    if ($id > 0) {
        $stmt = $pdo->prepare("UPDATE dishes SET name=?, photo=?, price=?, country=?, category_id=?, composition=?, in_stock=?, is_published=? WHERE id=?");
        $stmt->execute([$name, $photo, $price, $country, $category_id, $composition, $in_stock, $is_published, $id]);
        $message = 'Блюдо обновлено';
    } else {
        $stmt = $pdo->prepare("INSERT INTO dishes (name, photo, price, country, category_id, composition, in_stock, is_published) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $photo, $price, $country, $category_id, $composition, $in_stock, $is_published]);
        $message = 'Блюдо добавлено';
    }

    header('Location: index.php?page=admin&admin_page=dishes&message=' . urlencode($message));
    exit;
}

$edit_dish = null;
if (isset($_GET['edit']) && (int)$_GET['edit'] > 0) {
    $stmt = $pdo->prepare("SELECT * FROM dishes WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit_dish = $stmt->fetch();
}

if (isset($_GET['message'])) {
    $message = $_GET['message'];
}

// Get all dishes
$dishes = $pdo->query("
    SELECT d.*, c.name as category_name 
    FROM dishes d 
    JOIN categories c ON d.category_id = c.id 
    ORDER BY d.created_at DESC
")->fetchAll();

// Get categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<h1 class="page-title">Управление блюдами</h1>

<?php if ($message): ?>
    <div class="success-message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="error-message"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <h2><?= $edit_dish ? 'Редактировать блюдо' : 'Добавить новое блюдо' ?></h2>
    <form method="POST" action="index.php?page=admin&admin_page=dishes" enctype="multipart/form-data">
        <?php if ($edit_dish): ?>
            <input type="hidden" name="id" value="<?= $edit_dish['id'] ?>">
            <input type="hidden" name="current_photo" value="<?= htmlspecialchars($edit_dish['photo']) ?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label for="name">Название *</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($edit_dish['name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="price">Цена *</label>
                <input type="number" id="price" name="price" step="0.01" min="0" value="<?= htmlspecialchars($edit_dish['price'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="country">Страна *</label>
                <input type="text" id="country" name="country" value="<?= htmlspecialchars($edit_dish['country'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="category_id">Категория *</label>
                <select id="category_id" name="category_id" required>
                    <option value="">Выберите категорию</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (isset($edit_dish) && (int)$edit_dish['category_id'] === (int)$cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="in_stock">Количество</label>
                <input type="number" id="in_stock" name="in_stock" min="0" value="<?= htmlspecialchars($edit_dish['in_stock'] ?? '0') ?>">
            </div>
            <div class="form-group">
                <label for="photo">Фото</label>
                <input type="file" id="photo" name="photo" accept="image/*">
                <?php if ($edit_dish && $edit_dish['photo'] !== 'images/placeholder.svg'): ?>
                    <p style="font-size:12px;color:#666;margin-top:5px;">Текущее: <?= basename($edit_dish['photo']) ?></p>
                <?php endif; ?>
            </div>
        </div>
        <div class="form-group">
            <label for="composition">Состав *</label>
            <textarea id="composition" name="composition" required><?= htmlspecialchars($edit_dish['composition'] ?? '') ?></textarea>
        </div>
        <div class="form-group form-checkbox">
            <input type="checkbox" id="is_published" name="is_published" value="1" <?= (isset($edit_dish) && $edit_dish['is_published']) ? 'checked' : '' ?>>
            <label for="is_published" style="margin:0;">Опубликовано</label>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= $edit_dish ? 'Сохранить' : 'Добавить' ?></button>
            <?php if ($edit_dish): ?>
                <a href="index.php?page=admin&admin_page=dishes" class="btn btn-secondary">Отмена</a>
            <?php endif; ?>
        </div>
    </form>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Фото</th>
            <th>Название</th>
            <th>Цена</th>
            <th>Категория</th>
            <th>Остаток</th>
            <th>Статус</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($dishes as $dish): ?>
        <tr>
            <td><?= $dish['id'] ?></td>
            <td><img src="<?= htmlspecialchars($dish['photo']) ?>" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:5px;" onerror="this.src='images/placeholder.svg'"></td>
            <td><?= htmlspecialchars($dish['name']) ?></td>
            <td><?= number_format($dish['price'], 0, ',', ' ') ?> ₽</td>
            <td><?= htmlspecialchars($dish['category_name']) ?></td>
            <td><?= $dish['in_stock'] ?></td>
            <td><?= $dish['is_published'] ? 'Опубликовано' : 'Черновик' ?></td>
            <td class="actions">
                <a href="index.php?page=admin&admin_page=dishes&edit=<?= $dish['id'] ?>" class="btn btn-secondary btn-small">Редактировать</a>
                <a href="index.php?page=admin&admin_page=dishes&delete=<?= $dish['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Удалить блюдо?')">Удалить</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
