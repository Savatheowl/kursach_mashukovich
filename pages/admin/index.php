<?php
$pdo = getDB();

$dish_count = $pdo->query("SELECT COUNT(*) FROM dishes")->fetchColumn();
$category_count = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$user_count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$order_count = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'new'")->fetchColumn();
?>
<h1 class="page-title">Админ-панель</h1>

<div class="dishes-grid" style="grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
    <div class="dish-card" style="padding:25px;text-align:center;">
        <h3 style="font-size:36px;color:#c0392b;"><?= $dish_count ?></h3>
        <p>Блюд</p>
        <a href="index.php?page=admin&admin_page=dishes" class="btn btn-primary btn-small" style="margin-top:10px;">Управлять</a>
    </div>
    <div class="dish-card" style="padding:25px;text-align:center;">
        <h3 style="font-size:36px;color:#2c3e50;"><?= $category_count ?></h3>
        <p>Категорий</p>
        <a href="index.php?page=admin&admin_page=categories" class="btn btn-primary btn-small" style="margin-top:10px;">Управлять</a>
    </div>
    <div class="dish-card" style="padding:25px;text-align:center;">
        <h3 style="font-size:36px;color:#27ae60;"><?= $user_count ?></h3>
        <p>Пользователей</p>
    </div>
    <div class="dish-card" style="padding:25px;text-align:center;">
        <h3 style="font-size:36px;color:#f39c12;"><?= $order_count ?></h3>
        <p>Новых заказов</p>
        <a href="index.php?page=admin&admin_page=orders" class="btn btn-primary btn-small" style="margin-top:10px;">Управлять</a>
    </div>
</div>
