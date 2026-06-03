<?php
$page_title = 'О нас — ' . SITE_NAME;
$stmt = getDB()->prepare("SELECT id, name, photo, price FROM dishes WHERE is_published = 1 AND in_stock > 0 ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$new_dishes = $stmt->fetchAll();
?>
<div class="about-hero">
    <img src="images/logo.svg" alt="Кулинарный Мир" class="logo-img" onerror="this.style.display='none'">
    <h1>Кулинарный Мир</h1>
    <p class="motto">Откройте для себя вкус настоящей кухни!</p>
</div>

<?php if (!empty($new_dishes)): ?>
<div class="slider-container">
    <h2 class="section-title">Новинки компании</h2>
    <div class="slider">
        <?php foreach ($new_dishes as $dish): ?>
        <div class="slide-item">
            <a href="index.php?page=dish&id=<?= $dish['id'] ?>">
                <img src="<?= htmlspecialchars($dish['photo']) ?>" alt="<?= htmlspecialchars($dish['name']) ?>" onerror="this.src='images/placeholder.svg'">
                <div class="slide-info">
                    <h3><?= htmlspecialchars($dish['name']) ?></h3>
                    <span class="price"><?= number_format($dish['price'], 0, ',', ' ') ?> ₽</span>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <h2 class="section-title">Добро пожаловать!</h2>
    <p>Кулинарный Мир — это ресторан, где каждый найдёт блюдо по душе. Мы используем только свежие продукты и традиционные рецепты со всего мира.</p>
    <p>Наши повара готовят с любовью, чтобы вы наслаждались каждым кусочком. От классических русских щей до итальянской пасты — у нас богатый выбор на любой вкус.</p>
    <p>Приходите к нам или заказывайте онлайн!</p>
</div>
