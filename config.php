<?php
define('DB_TYPE', 'sqlite'); // 'mysql' or 'sqlite'

// MySQL settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'kursach');
define('DB_USER', 'root');
define('DB_PASS', '');

// SQLite settings
define('DB_SQLITE_PATH', __DIR__ . '/data/kursach.db');

define('UPLOAD_DIR', 'uploads/dishes/');
define('SITE_NAME', 'Кулинарный Мир');
define('ITEMS_PER_PAGE', 12);
