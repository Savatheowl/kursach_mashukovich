<?php
$pdo = getDB();
$error = '';
$message = '';

// Handle add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    if (!empty($name)) {
        $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        $message = 'Категория добавлена';
    } else {
        $error = 'Введите название категории';
    }
}

// Handle delete
if (isset($_GET['delete']) && (int)$_GET['delete'] > 0) {
    $id = (int)$_GET['delete'];
    try {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        $message = 'Категория удалена';
    } catch (PDOException $e) {
        $error = 'Нельзя удалить категорию, в ней есть блюда';
    }
}

$categories = $pdo->query("SELECT c.*, (SELECT COUNT(*) FROM dishes WHERE category_id = c.id) as dish_count FROM categories c ORDER BY name")->fetchAll();
?>

<h1 class="page-title">Управление категориями</h1>

<?php if ($message): ?>
    <div class="success-message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="error-message"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card">
    <h2>Добавить категорию</h2>
    <form method="POST" action="index.php?page=admin&admin_page=categories">
        <div class="form-row">
            <div class="form-group" style="flex:1;">
                <label for="name">Название категории</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group" style="display:flex;align-items:flex-end;">
                <button type="submit" class="btn btn-primary">Добавить</button>
            </div>
        </div>
    </form>
</div>

<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Блюд</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($categories as $cat): ?>
        <tr>
            <td><?= $cat['id'] ?></td>
            <td><?= htmlspecialchars($cat['name']) ?></td>
            <td><?= $cat['dish_count'] ?></td>
            <td class="actions">
                <a href="index.php?page=admin&admin_page=categories&delete=<?= $cat['id'] ?>" class="btn btn-danger btn-small" onclick="return confirm('Удалить категорию?')">Удалить</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
