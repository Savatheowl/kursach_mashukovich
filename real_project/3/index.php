<?php
session_start();
require_once 'config.php';
require_once 'database.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'about';
$allowed_pages = ['about', 'menu', 'dish', 'contacts', 'login', 'register', 'logout', 'cart', 'checkout', 'my-orders', 'admin'];

$page_title = SITE_NAME;

if ($page === 'admin') {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: index.php?page=login');
        exit;
    }
    $admin_page = isset($_GET['admin_page']) ? $_GET['admin_page'] : 'index';
    $allowed_admin_pages = ['index', 'dishes', 'categories', 'orders'];
    if (!in_array($admin_page, $allowed_admin_pages)) {
        $admin_page = 'index';
    }
    $page_title = 'Админ-панель — ' . SITE_NAME;
    ob_start();
    require "pages/admin/{$admin_page}.php";
    $content = ob_get_clean();
    require "templates/admin_header.php";
    echo $content;
    require "templates/footer.php";
    exit;
}

if (in_array($page, $allowed_pages)) {
    $page_title = ucfirst($page) . ' — ' . SITE_NAME;
    require "templates/header.php";
    require "pages/{$page}.php";
    require "templates/footer.php";
} else {
    require "templates/header.php";
    require "pages/about.php";
    require "templates/footer.php";
}
