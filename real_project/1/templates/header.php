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
            <?php if (isset($_SESSION['user_id'])): ?>
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
