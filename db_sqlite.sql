-- SQLite schema (или просто запусти php seed.php — таблицы создадутся автоматически)
-- Run: sqlite3 data/kursach.db < db_sqlite.sql

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    surname TEXT NOT NULL,
    patronymic TEXT,
    login TEXT UNIQUE NOT NULL,
    email TEXT NOT NULL,
    password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'visitor'
);

CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS dishes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    photo TEXT NOT NULL DEFAULT 'images/placeholder.svg',
    price REAL NOT NULL,
    country TEXT NOT NULL,
    category_id INTEGER NOT NULL,
    composition TEXT NOT NULL,
    in_stock INTEGER NOT NULL DEFAULT 0,
    is_published INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

CREATE TABLE IF NOT EXISTS cart (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    dish_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE,
    UNIQUE(user_id, dish_id)
);

CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    status TEXT NOT NULL DEFAULT 'new',
    cancel_reason TEXT,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    dish_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id)
);

-- Sample data

INSERT OR IGNORE INTO users (name, surname, patronymic, login, email, password, role) VALUES
('Admin', 'Adminov', NULL, 'admin', 'admin@example.com', '$2y$10$VcT/NHaIkhNC8cs6N29/ae3ROtr6b61AiMtNAozXX44uKPlt8wFYa', 'admin'),
('User', 'Userov', 'Userovich', 'user', 'user@example.com', '$2y$10$PKS33QnJyWdtFUsM7R79lO61Shwnne.f2Qy2OTppJK87y9gOIhag.', 'client');

INSERT OR IGNORE INTO categories (name) VALUES
('Первые блюда'),
('Вторые блюда'),
('Салаты'),
('Десерты'),
('Напитки'),
('Закуски');

INSERT OR IGNORE INTO dishes (name, photo, price, country, category_id, composition, in_stock, is_published, created_at) VALUES
('Борщ', 'images/placeholder.svg', 350.00, 'Украина', 1, 'Свекла, капуста, картофель, морковь, лук, мясо, сметана', 20, 1, datetime('now')),
('Щи', 'images/placeholder.svg', 280.00, 'Россия', 1, 'Капуста, картофель, морковь, лук, мясо', 15, 1, datetime('now')),
('Стейк Рибай', 'images/placeholder.svg', 1200.00, 'США', 2, 'Говядина рибай, соль, перец, розмарин', 10, 1, datetime('now')),
('Паста Карбонара', 'images/placeholder.svg', 450.00, 'Италия', 2, 'Спагетти, бекон, яйца, пармезан, сливки', 25, 1, datetime('now')),
('Цезарь', 'images/placeholder.svg', 380.00, 'Мексика', 3, 'Салат айсберг, курица, пармезан, сухарики, соус цезарь', 30, 1, datetime('now')),
('Греческий салат', 'images/placeholder.svg', 320.00, 'Греция', 3, 'Помидоры, огурцы, перец, фета, маслины, оливковое масло', 20, 1, datetime('now')),
('Тирамису', 'images/placeholder.svg', 390.00, 'Италия', 4, 'Маскарпоне, печенье савоярди, кофе, какао', 15, 1, datetime('now')),
('Чизкейк', 'images/placeholder.svg', 350.00, 'США', 4, 'Сливочный сыр, печенье, сливки, яйца', 12, 1, datetime('now')),
('Компот', 'images/placeholder.svg', 100.00, 'Россия', 5, 'Ягоды, сахар, вода', 50, 1, datetime('now')),
('Лимонад', 'images/placeholder.svg', 150.00, 'Франция', 5, 'Лимон, сахар, вода, мята, лед', 40, 1, datetime('now')),
('Брускетта', 'images/placeholder.svg', 250.00, 'Италия', 6, 'Хлеб, помидоры, базилик, оливковое масло, чеснок', 20, 1, datetime('now')),
('Спринг-роллы', 'images/placeholder.svg', 280.00, 'Вьетнам', 6, 'Рисовая бумага, овощи, креветки, соус', 15, 1, datetime('now'));
