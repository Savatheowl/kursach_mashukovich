<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? SITE_NAME ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="container">
        <div class="logo">
            <a href="index.php">
                <img src="images/logo.svg" alt="Кулинарный Мир" class="logo-img" onerror="this.style.display='none'">
                Кулинарный Мир
            </a>
        </div>
        <nav>
            <a href="index.php?page=about" <?= ($page ?? 'about') === 'about' ? 'class="active"' : '' ?>>О нас</a>
            <a href="index.php?page=menu" <?= ($page ?? '') === 'menu' ? 'class="active"' : '' ?>>Меню</a>
            <a href="index.php?page=contacts" <?= ($page ?? '') === 'contacts' ? 'class="active"' : '' ?>>Где нас найти?</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($_SESSION['user_role'] === 'client'): ?>
                    <a href="index.php?page=cart" <?= ($page ?? '') === 'cart' ? 'class="active"' : '' ?>>Корзина</a>
                    <a href="index.php?page=my-orders" <?= ($page ?? '') === 'my-orders' ? 'class="active"' : '' ?>>Мои заказы</a>
                <?php endif; ?>
                <a href="index.php?page=logout">Выйти (<?= htmlspecialchars($_SESSION['user_name']) ?>)</a>
            <?php else: ?>
                <a href="index.php?page=login" <?= ($page ?? '') === 'login' ? 'class="active"' : '' ?>>Вход</a>
                <a href="index.php?page=register" <?= ($page ?? '') === 'register' ? 'class="active"' : '' ?>>Регистрация</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
    <div class="container">
