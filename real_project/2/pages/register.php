<?php
$page_title = 'Регистрация — ' . SITE_NAME;
$pdo = getDB();
$errors = [];
$old = $_POST;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $surname = trim($_POST['surname'] ?? '');
    $patronymic = trim($_POST['patronymic'] ?? '');
    $login = trim($_POST['login'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_repeat = $_POST['password_repeat'] ?? '';

    if (!preg_match('/^[a-zA-Zа-яёА-ЯЁ\s-]+$/u', $name)) {
        $errors[] = 'Имя должно содержать только буквы';
    }
    if (!preg_match('/^[a-zA-Zа-яёА-ЯЁ\s-]+$/u', $surname)) {
        $errors[] = 'Фамилия должна содержать только буквы';
    }
    if ($patronymic !== '' && !preg_match('/^[a-zA-Zа-яёА-ЯЁ\s-]+$/u', $patronymic)) {
        $errors[] = 'Отчество должно содержать только буквы';
    }
    if (empty($login)) {
        $errors[] = 'Логин обязателен';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Некорректный email';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Пароль должен быть не менее 6 символов';
    }
    if ($password !== $password_repeat) {
        $errors[] = 'Пароли не совпадают';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->execute([$login]);
        if ($stmt->fetch()) {
            $errors[] = 'Логин уже занят';
        }
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (name, surname, patronymic, login, email, password, role) VALUES (?, ?, ?, ?, ?, ?, 'visitor')");
        $stmt->execute([$name, $surname, $patronymic ?: null, $login, $email, $hashed]);
        header('Location: index.php?page=login');
        exit;
    }
}
?>

<div class="auth-form">
    <div class="card">
        <h2>Регистрация</h2>
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $err): ?>
                    <p><?= htmlspecialchars($err) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="index.php?page=register">
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Имя *</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($old['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="surname">Фамилия *</label>
                    <input type="text" id="surname" name="surname" value="<?= htmlspecialchars($old['surname'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label for="patronymic">Отчество</label>
                <input type="text" id="patronymic" name="patronymic" value="<?= htmlspecialchars($old['patronymic'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label for="login">Логин *</label>
                <input type="text" id="login" name="login" value="<?= htmlspecialchars($old['login'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Пароль *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="password_repeat">Повтор пароля *</label>
                    <input type="password" id="password_repeat" name="password_repeat" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Зарегистрироваться</button>
        </form>
        <div class="auth-link">
            <p>Уже есть аккаунт? <a href="index.php?page=login">Войти</a></p>
        </div>
    </div>
</div>
