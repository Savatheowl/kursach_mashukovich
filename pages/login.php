<?php
$page_title = 'Вход — ' . SITE_NAME;
$pdo = getDB();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = 'Заполните все поля';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE login = ?");
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_surname'] = $user['surname'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: index.php?page=about');
            exit;
        } else {
            $error = 'Неверный логин или пароль';
        }
    }
}
?>

<div class="auth-form">
    <div class="card">
        <h2>Вход</h2>
        <?php if ($error): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="index.php?page=login">
            <div class="form-group">
                <label for="login">Логин</label>
                <input type="text" id="login" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%">Войти</button>
        </form>
        <div class="auth-link">
            <p>Нет аккаунта? <a href="index.php?page=register">Зарегистрироваться</a></p>
        </div>
    </div>
</div>
