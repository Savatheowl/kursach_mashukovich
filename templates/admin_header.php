<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Админ-панель — ' . SITE_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="logo">
            <a href="index.php">Кулинарный Мир</a>
        </div>
        <nav>
            <a href="index.php?page=about">На сайт</a>
            <a href="index.php?page=admin">Админ-панель</a>
            <a href="index.php?page=logout">Выйти (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a>
        </nav>
    </div>
</header>
<main>
    <div class="container">
        <div class="admin-nav">
            <a href="index.php?page=admin&admin_page=index" <?= ($admin_page ?? '') === 'index' ? 'class="active"' : '' ?>>Главная</a>
            <a href="index.php?page=admin&admin_page=dishes" <?= ($admin_page ?? '') === 'dishes' ? 'class="active"' : '' ?>>Блюда</a>
            <a href="index.php?page=admin&admin_page=categories" <?= ($admin_page ?? '') === 'categories' ? 'class="active"' : '' ?>>Категории</a>
            <a href="index.php?page=admin&admin_page=orders" <?= ($admin_page ?? '') === 'orders' ? 'class="active"' : '' ?>>Заказы</a>
        </div>
