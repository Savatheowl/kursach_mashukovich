<?php
session_start();
require_once 'config.php';
require_once 'database.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'about';
$allowed_pages = ['about', 'login', 'register', 'logout'];

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
