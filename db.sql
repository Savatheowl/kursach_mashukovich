CREATE DATABASE IF NOT EXISTS kursach CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kursach;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    surname VARCHAR(255) NOT NULL,
    patronymic VARCHAR(255) NULL,
    login VARCHAR(255) UNIQUE NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'client', 'visitor') NOT NULL DEFAULT 'visitor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE dishes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    photo VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    country VARCHAR(255) NOT NULL,
    category_id INT NOT NULL,
    composition TEXT NOT NULL,
    in_stock INT NOT NULL DEFAULT 0,
    is_published BOOLEAN NOT NULL DEFAULT FALSE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    dish_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cart (user_id, dish_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    status ENUM('new', 'confirmed', 'cancelled') NOT NULL DEFAULT 'new',
    cancel_reason TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    dish_id INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample data

-- Password hashes: admin123 and user123
INSERT INTO users (name, surname, patronymic, login, email, password, role) VALUES
('Admin', 'Adminov', NULL, 'admin', 'admin@example.com', '$2y$10$VcT/NHaIkhNC8cs6N29/ae3ROtr6b61AiMtNAozXX44uKPlt8wFYa', 'admin'),
('User', 'Userov', 'Userovich', 'user', 'user@example.com', '$2y$10$PKS33QnJyWdtFUsM7R79lO61Shwnne.f2Qy2OTppJK87y9gOIhag.', 'client');

INSERT INTO categories (name) VALUES
('Первые блюда'),
('Вторые блюда'),
('Салаты'),
('Десерты'),
('Напитки'),
('Закуски');

INSERT INTO dishes (name, photo, price, country, category_id, composition, in_stock, is_published, created_at) VALUES
('Борщ', 'images/placeholder.svg', 350.00, 'Украина', 1, 'Свекла, капуста, картофель, морковь, лук, мясо, сметана', 20, TRUE, NOW()),
('Щи', 'images/placeholder.svg', 280.00, 'Россия', 1, 'Капуста, картофель, морковь, лук, мясо', 15, TRUE, NOW()),
('Стейк Рибай', 'images/placeholder.svg', 1200.00, 'США', 2, 'Говядина рибай, соль, перец, розмарин', 10, TRUE, NOW()),
('Паста Карбонара', 'images/placeholder.svg', 450.00, 'Италия', 2, 'Спагетти, бекон, яйца, пармезан, сливки', 25, TRUE, NOW()),
('Цезарь', 'images/placeholder.svg', 380.00, 'Мексика', 3, 'Салат айсберг, курица, пармезан, сухарики, соус цезарь', 30, TRUE, NOW()),
('Греческий салат', 'images/placeholder.svg', 320.00, 'Греция', 3, 'Помидоры, огурцы, перец, фета, маслины, оливковое масло', 20, TRUE, NOW()),
('Тирамису', 'images/placeholder.svg', 390.00, 'Италия', 4, 'Маскарпоне, печенье савоярди, кофе, какао', 15, TRUE, NOW()),
('Чизкейк', 'images/placeholder.svg', 350.00, 'США', 4, 'Сливочный сыр, печенье, сливки, яйца', 12, TRUE, NOW()),
('Компот', 'images/placeholder.svg', 100.00, 'Россия', 5, 'Ягоды, сахар, вода', 50, TRUE, NOW()),
('Лимонад', 'images/placeholder.svg', 150.00, 'Франция', 5, 'Лимон, сахар, вода, мята, лед', 40, TRUE, NOW()),
('Брускетта', 'images/placeholder.svg', 250.00, 'Италия', 6, 'Хлеб, помидоры, базилик, оливковое масло, чеснок', 20, TRUE, NOW()),
('Спринг-роллы', 'images/placeholder.svg', 280.00, 'Вьетнам', 6, 'Рисовая бумага, овощи, креветки, соус', 15, TRUE, NOW());
