USE foodapp_db;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS order_status_history;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS cart;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS favorites;
DROP TABLE IF EXISTS recently_viewed;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS food_addons;
DROP TABLE IF EXISTS addons;
DROP TABLE IF EXISTS food_variants;
DROP TABLE IF EXISTS foods;
DROP TABLE IF EXISTS coupons;
DROP TABLE IF EXISTS banners;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(25) NOT NULL,
    password VARCHAR(255) NOT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    status ENUM('active', 'blocked') NOT NULL DEFAULT 'active',
    last_login_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(120) NOT NULL UNIQUE,
    icon VARCHAR(100) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    description VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    subtitle VARCHAR(255) DEFAULT NULL,
    image VARCHAR(255) NOT NULL,
    cta_text VARCHAR(80) DEFAULT NULL,
    cta_link VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE coupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(100) NOT NULL,
    discount_type ENUM('percentage', 'fixed') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL,
    min_order_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_discount DECIMAL(10,2) DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    usage_limit INT DEFAULT NULL,
    used_count INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(160) NOT NULL UNIQUE,
    short_description VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    ingredients TEXT DEFAULT NULL,
    nutrition_info TEXT DEFAULT NULL,
    preparation_time VARCHAR(50) DEFAULT '25 mins',
    image VARCHAR(255) DEFAULT NULL,
    base_price DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2) DEFAULT NULL,
    rating DECIMAL(3,2) NOT NULL DEFAULT 4.20,
    review_count INT NOT NULL DEFAULT 0,
    availability_status ENUM('available', 'limited', 'sold_out') NOT NULL DEFAULT 'available',
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    is_recommended TINYINT(1) NOT NULL DEFAULT 0,
    is_popular TINYINT(1) NOT NULL DEFAULT 0,
    is_trending TINYINT(1) NOT NULL DEFAULT 0,
    is_new_arrival TINYINT(1) NOT NULL DEFAULT 0,
    spice_level ENUM('mild', 'medium', 'hot') NOT NULL DEFAULT 'medium',
    sold_count INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_food_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE TABLE food_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    food_id INT NOT NULL,
    variant_name VARCHAR(80) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_variant_food FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE
);

CREATE TABLE addons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE food_addons (
    food_id INT NOT NULL,
    addon_id INT NOT NULL,
    PRIMARY KEY (food_id, addon_id),
    CONSTRAINT fk_food_addon_food FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE,
    CONSTRAINT fk_food_addon_addon FOREIGN KEY (addon_id) REFERENCES addons(id) ON DELETE CASCADE
);

CREATE TABLE addresses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    label VARCHAR(50) NOT NULL DEFAULT 'Home',
    contact_name VARCHAR(120) NOT NULL,
    phone VARCHAR(25) NOT NULL,
    address_line TEXT NOT NULL,
    city VARCHAR(100) NOT NULL,
    state VARCHAR(100) DEFAULT NULL,
    postal_code VARCHAR(20) DEFAULT NULL,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_address_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE recently_viewed (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    food_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_recent_view (user_id, food_id),
    CONSTRAINT fk_recent_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_recent_food FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE
);

CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    food_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_favorite (user_id, food_id),
    CONSTRAINT fk_favorite_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorite_food FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE
);

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    food_id INT NOT NULL,
    rating TINYINT NOT NULL,
    review_text TEXT DEFAULT NULL,
    review_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_review_food FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    food_id INT NOT NULL,
    variant_id INT DEFAULT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    addons_summary VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_food FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_variant FOREIGN KEY (variant_id) REFERENCES food_variants(id) ON DELETE SET NULL
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(30) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    address_id INT DEFAULT NULL,
    customer_name VARCHAR(120) NOT NULL,
    customer_phone VARCHAR(25) NOT NULL,
    delivery_address TEXT NOT NULL,
    payment_method ENUM('Cash on Delivery', 'UPI', 'Google Pay', 'PhonePe', 'Paytm') NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    order_status ENUM('Pending', 'Accepted', 'Preparing', 'Ready', 'Out For Delivery', 'Delivered', 'Cancelled') NOT NULL DEFAULT 'Pending',
    subtotal DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    delivery_fee DECIMAL(10,2) NOT NULL DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    coupon_code VARCHAR(50) DEFAULT NULL,
    notes VARCHAR(255) DEFAULT NULL,
    placed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_address FOREIGN KEY (address_id) REFERENCES addresses(id) ON DELETE SET NULL
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    food_id INT NOT NULL,
    variant_name VARCHAR(80) DEFAULT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    addons_summary VARCHAR(255) DEFAULT NULL,
    CONSTRAINT fk_order_item_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_item_food FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE
);

CREATE TABLE order_status_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    status ENUM('Pending', 'Accepted', 'Preparing', 'Ready', 'Out For Delivery', 'Delivered', 'Cancelled') NOT NULL,
    note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_status_history_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    payment_method ENUM('Cash on Delivery', 'UPI', 'Google Pay', 'PhonePe', 'Paytm') NOT NULL,
    transaction_ref VARCHAR(120) DEFAULT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'success', 'failed', 'refunded') NOT NULL DEFAULT 'pending',
    paid_at DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
);

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message VARCHAR(255) NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (role, name, email, phone, password, status) VALUES
('customer', 'Seed User One', 'seed-user-one@internal.invalid', '9000000001', '$2y$10$yMdG1lwx9zQKk2qq9nP2weTHZ66z2k1VQjF90QnLB522Ssqn1H4xS', 'blocked'),
('customer', 'Sample Customer', 'seed-customer@internal.invalid', '9000000002', '$2y$10$yMdG1lwx9zQKk2qq9nP2weTHZ66z2k1VQjF90QnLB522Ssqn1H4xS', 'blocked');

INSERT INTO categories (name, slug, icon, image, description) VALUES
('Pizza', 'pizza', 'fa-pizza-slice', 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=900&q=80', 'Stone baked pizzas and cheesy slices'),
('Burger', 'burger', 'fa-burger', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=900&q=80', 'Smash burgers and loaded bites'),
('Biryani', 'biryani', 'fa-bowl-rice', 'https://images.unsplash.com/photo-1701579231305-d84d8af9a3fd?auto=format&fit=crop&w=900&q=80', 'Fragrant rice bowls and biryani combos'),
('Drinks', 'drinks', 'fa-glass-water', 'https://images.unsplash.com/photo-1544145945-f90425340c7e?auto=format&fit=crop&w=900&q=80', 'Coolers, shakes, and fizz'),
('Desserts', 'desserts', 'fa-ice-cream', 'https://images.unsplash.com/photo-1551024601-bec78aea704b?auto=format&fit=crop&w=900&q=80', 'Cakes, brownies, and frozen treats'),
('Wraps', 'wraps', 'fa-hotdog', 'https://images.unsplash.com/photo-1529006557810-274b9b2fc783?auto=format&fit=crop&w=900&q=80', 'Rolled snacks and quick wraps');

INSERT INTO banners (title, subtitle, image, cta_text, cta_link, sort_order) VALUES
('Free Delivery Friday', 'Get free delivery on orders above Rs. 499', 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=1400&q=80', 'Order Now', 'menu.php', 1),
('Hot Deals Combo', 'Burger, fries and drink combos from Rs. 349', 'https://images.unsplash.com/photo-1512152272829-e3139592d56f?auto=format&fit=crop&w=1400&q=80', 'See Combos', 'menu.php?category=burger', 2),
('Midnight Biryani Sale', 'Best-selling biryanis with 15 percent off tonight', 'https://images.unsplash.com/photo-1633945274309-2c16ef3c5d79?auto=format&fit=crop&w=1400&q=80', 'Explore', 'menu.php?category=biryani', 3);

INSERT INTO coupons (code, title, discount_type, discount_value, min_order_amount, max_discount, expires_at, usage_limit) VALUES
('WELCOME20', 'Welcome offer', 'percentage', 20.00, 399.00, 120.00, '2027-12-31 23:59:59', 500),
('FLAT100', 'Flat savings', 'fixed', 100.00, 599.00, NULL, '2027-12-31 23:59:59', 250),
('FLASH15', 'Flash deal', 'percentage', 15.00, 299.00, 80.00, '2027-12-31 23:59:59', 300);

INSERT INTO addons (name, price) VALUES
('Extra Cheese', 40.00),
('Paneer Cubes', 50.00),
('Chicken Tikka', 70.00),
('French Fries', 60.00),
('Cold Drink', 45.00),
('Mint Dip', 20.00),
('Brownie Bite', 55.00),
('Ice Cream Scoop', 65.00);

INSERT INTO foods (category_id, name, slug, short_description, description, ingredients, nutrition_info, preparation_time, image, base_price, discount_price, rating, review_count, availability_status, is_featured, is_recommended, is_popular, is_trending, is_new_arrival, spice_level, sold_count) VALUES
(1, 'Margherita Pizza', 'margherita-pizza', 'Classic tomato, mozzarella, basil.', 'A comforting classic with tangy tomato sauce, creamy mozzarella, and fresh basil leaves over a hand-stretched crust.', 'Tomato sauce, mozzarella, basil, olive oil, flour', '420 kcal | 18g protein | 44g carbs | 18g fat', '20 mins', 'https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=900&q=80', 249.00, 219.00, 4.6, 124, 'available', 1, 1, 1, 0, 0, 'mild', 310),
(1, 'Farmhouse Pizza', 'farmhouse-pizza', 'Loaded with veggies and cheese.', 'A loaded pizza layered with capsicum, onion, mushrooms, and sweet corn for a satisfying bite.', 'Capsicum, onion, mushrooms, sweet corn, mozzarella', '490 kcal | 19g protein | 49g carbs | 22g fat', '22 mins', 'https://images.unsplash.com/photo-1514326640560-7d063ef2aed5?auto=format&fit=crop&w=900&q=80', 329.00, 299.00, 4.7, 166, 'available', 1, 1, 1, 1, 0, 'mild', 420),
(1, 'Pepperoni Blast Pizza', 'pepperoni-blast-pizza', 'Pepperoni, mozzarella and herbs.', 'A meaty Italian-style pie finished with smoky pepperoni slices and chili oil.', 'Pepperoni, mozzarella, tomato sauce, oregano', '560 kcal | 26g protein | 47g carbs | 29g fat', '24 mins', 'https://images.unsplash.com/photo-1534308983496-4fabb1a015ee?auto=format&fit=crop&w=900&q=80', 389.00, NULL, 4.8, 205, 'available', 1, 1, 1, 1, 0, 'medium', 540),
(1, 'Paneer Tikka Pizza', 'paneer-tikka-pizza', 'Spiced paneer and onion topping.', 'Indian-inspired pizza loaded with smoky paneer tikka cubes, onions, and mint drizzle.', 'Paneer tikka, onion, capsicum, mozzarella, mint sauce', '530 kcal | 25g protein | 46g carbs | 24g fat', '24 mins', 'https://images.unsplash.com/photo-1594007654729-407eedc4be65?auto=format&fit=crop&w=900&q=80', 359.00, 329.00, 4.5, 98, 'available', 0, 1, 1, 0, 1, 'medium', 245),
(1, 'Mexican Fiesta Pizza', 'mexican-fiesta-pizza', 'Beans, jalapenos and spicy salsa.', 'A bold pizza packed with jalapenos, roasted peppers, beans, and spicy chipotle salsa.', 'Kidney beans, jalapenos, peppers, onion, chipotle sauce', '515 kcal | 20g protein | 52g carbs | 23g fat', '25 mins', 'https://images.unsplash.com/photo-1548365328-9f547fb0953b?auto=format&fit=crop&w=900&q=80', 369.00, NULL, 4.4, 84, 'available', 0, 0, 0, 1, 1, 'hot', 180),

(2, 'Cheese Burger', 'cheese-burger', 'Juicy patty with cheddar and lettuce.', 'A juicy grilled burger stacked with cheddar, crunchy lettuce, and signature burger sauce.', 'Burger bun, patty, cheddar, lettuce, onion, sauce', '540 kcal | 24g protein | 43g carbs | 31g fat', '15 mins', 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=900&q=80', 179.00, 159.00, 4.5, 145, 'available', 1, 1, 1, 0, 0, 'mild', 332),
(2, 'Crispy Chicken Burger', 'crispy-chicken-burger', 'Crispy chicken fillet with mayo.', 'Golden fried chicken fillet layered with mayo, lettuce, and pickles for crunch.', 'Chicken fillet, mayo, lettuce, pickles, bun', '590 kcal | 27g protein | 45g carbs | 34g fat', '17 mins', 'https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&w=900&q=80', 199.00, NULL, 4.7, 178, 'available', 1, 1, 1, 1, 0, 'medium', 410),
(2, 'Double Smash Burger', 'double-smash-burger', 'Two patties, more cheese, more flavour.', 'A stacked double smash burger with caramelized onions and house sauce.', 'Double patties, cheddar, onions, pickles, sauce', '690 kcal | 33g protein | 44g carbs | 42g fat', '18 mins', 'https://images.unsplash.com/photo-1571091718767-18b5b1457add?auto=format&fit=crop&w=900&q=80', 269.00, 239.00, 4.8, 222, 'available', 1, 1, 1, 1, 1, 'medium', 490),
(2, 'Veggie Supreme Burger', 'veggie-supreme-burger', 'Vegetable patty, lettuce and chipotle mayo.', 'A hearty vegetarian burger with a spiced veg patty and creamy chipotle mayo.', 'Veg patty, lettuce, tomato, chipotle mayo, bun', '470 kcal | 16g protein | 50g carbs | 22g fat', '16 mins', 'https://images.unsplash.com/photo-1520072959219-c595dc870360?auto=format&fit=crop&w=900&q=80', 189.00, NULL, 4.3, 77, 'available', 0, 1, 0, 0, 1, 'mild', 155),
(2, 'BBQ Chicken Burger', 'bbq-chicken-burger', 'Smoky grilled chicken and BBQ glaze.', 'Grilled chicken breast brushed with smoky barbecue sauce and crunchy slaw.', 'Chicken breast, BBQ sauce, slaw, bun', '520 kcal | 29g protein | 41g carbs | 25g fat', '18 mins', 'https://images.unsplash.com/photo-1550317138-10000687a72b?auto=format&fit=crop&w=900&q=80', 229.00, NULL, 4.6, 133, 'available', 0, 0, 1, 1, 0, 'medium', 284),

(3, 'Chicken Biryani', 'chicken-biryani', 'Classic dum biryani with raita.', 'Long-grain basmati rice slow cooked with masala chicken, saffron, and herbs.', 'Basmati rice, chicken, yogurt, spices, mint', '610 kcal | 31g protein | 64g carbs | 24g fat', '30 mins', 'https://images.unsplash.com/photo-1701579231305-d84d8af9a3fd?auto=format&fit=crop&w=900&q=80', 289.00, 259.00, 4.8, 260, 'available', 1, 1, 1, 1, 0, 'medium', 610),
(3, 'Veg Biryani', 'veg-biryani', 'Aromatic vegetables and basmati rice.', 'A fragrant vegetarian biryani loaded with spiced vegetables and caramelized onions.', 'Basmati rice, mixed vegetables, mint, saffron, spices', '510 kcal | 14g protein | 69g carbs | 18g fat', '28 mins', 'https://images.unsplash.com/photo-1631515243349-e0cb75fb8d3a?auto=format&fit=crop&w=900&q=80', 219.00, NULL, 4.3, 95, 'available', 0, 1, 0, 0, 0, 'mild', 180),
(3, 'Hyderabadi Mutton Biryani', 'hyderabadi-mutton-biryani', 'Rich mutton dum biryani.', 'A slow-cooked royal biryani featuring tender mutton pieces and deep aromatic spices.', 'Mutton, basmati rice, browned onions, spices, yogurt', '690 kcal | 34g protein | 62g carbs | 32g fat', '35 mins', 'https://images.unsplash.com/photo-1626500155537-93690c24099e?auto=format&fit=crop&w=900&q=80', 389.00, NULL, 4.9, 201, 'available', 1, 1, 1, 1, 0, 'hot', 470),
(3, 'Paneer Biryani', 'paneer-biryani', 'Paneer cubes and saffron rice.', 'Soft paneer cubes folded into spiced saffron rice with fried onions and herbs.', 'Paneer, rice, spices, onion, mint', '560 kcal | 22g protein | 66g carbs | 20g fat', '28 mins', 'https://images.unsplash.com/photo-1690401767645-595de0e0e5f8?auto=format&fit=crop&w=900&q=80', 259.00, 239.00, 4.4, 88, 'available', 0, 0, 1, 0, 1, 'medium', 162),
(3, 'Egg Biryani', 'egg-biryani', 'Boiled eggs tossed in spicy rice.', 'A comforting biryani with masala-coated eggs, rice, and crispy fried onions.', 'Eggs, rice, spices, onion, coriander', '540 kcal | 20g protein | 65g carbs | 21g fat', '24 mins', 'https://images.unsplash.com/photo-1563379926898-05f4575a45d8?auto=format&fit=crop&w=900&q=80', 229.00, NULL, 4.2, 64, 'available', 0, 0, 0, 0, 1, 'medium', 120),

(4, 'Cold Coffee', 'cold-coffee', 'Chilled coffee with cream.', 'A smooth iced coffee blend topped with cream and cocoa dust.', 'Coffee, milk, sugar, cream', '260 kcal | 7g protein | 29g carbs | 12g fat', '8 mins', 'https://images.unsplash.com/photo-1461023058943-07fcbe16d735?auto=format&fit=crop&w=900&q=80', 129.00, 109.00, 4.5, 141, 'available', 1, 1, 0, 0, 0, 'mild', 250),
(4, 'Fresh Lime Soda', 'fresh-lime-soda', 'Refreshing fizzy lime drink.', 'A bubbly lime soda with sweet-salt balance and mint freshness.', 'Lime, soda, mint, sugar, black salt', '120 kcal | 1g protein | 28g carbs | 0g fat', '6 mins', 'https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=900&q=80', 89.00, NULL, 4.2, 70, 'available', 0, 1, 0, 0, 0, 'mild', 130),
(4, 'Mango Smoothie', 'mango-smoothie', 'Thick mango smoothie with yogurt.', 'A creamy smoothie made with ripe mangoes, yogurt, and a hint of honey.', 'Mango, yogurt, honey, ice', '240 kcal | 6g protein | 35g carbs | 8g fat', '7 mins', 'https://images.unsplash.com/photo-1502741338009-cac2772e18bc?auto=format&fit=crop&w=900&q=80', 149.00, NULL, 4.6, 92, 'available', 0, 1, 1, 1, 0, 'mild', 190),
(4, 'Chocolate Shake', 'chocolate-shake', 'Rich and thick chocolate milkshake.', 'An indulgent chocolate shake topped with whipped cream and chips.', 'Milk, chocolate, ice cream, whipped cream', '390 kcal | 9g protein | 48g carbs | 18g fat', '9 mins', 'https://images.unsplash.com/photo-1577805947697-89e18249d767?auto=format&fit=crop&w=900&q=80', 169.00, 149.00, 4.7, 154, 'available', 1, 1, 1, 0, 0, 'mild', 280),
(4, 'Watermelon Cooler', 'watermelon-cooler', 'Summer cooler with mint and lemon.', 'A refreshing fruit cooler made for hot afternoons and flash deals.', 'Watermelon, mint, lemon, soda', '115 kcal | 1g protein | 27g carbs | 0g fat', '6 mins', 'https://images.unsplash.com/photo-1497534446932-c925b458314e?auto=format&fit=crop&w=900&q=80', 119.00, NULL, 4.3, 58, 'available', 0, 0, 0, 1, 1, 'mild', 96),

(5, 'Chocolate Brownie', 'chocolate-brownie', 'Fudgy brownie with rich cocoa.', 'A soft-centered chocolate brownie served warm and perfectly indulgent.', 'Cocoa, butter, flour, sugar, eggs', '330 kcal | 5g protein | 38g carbs | 18g fat', '12 mins', 'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=900&q=80', 149.00, NULL, 4.7, 132, 'available', 1, 1, 1, 0, 0, 'mild', 210),
(5, 'Vanilla Ice Cream', 'vanilla-ice-cream', 'Creamy vanilla scoop.', 'A chilled vanilla dessert with a smooth texture and rich dairy taste.', 'Milk, cream, vanilla, sugar', '210 kcal | 4g protein | 22g carbs | 12g fat', '5 mins', 'https://images.unsplash.com/photo-1563805042-7684c019e1cb?auto=format&fit=crop&w=900&q=80', 99.00, NULL, 4.3, 68, 'available', 0, 1, 0, 0, 0, 'mild', 118),
(5, 'Red Velvet Pastry', 'red-velvet-pastry', 'Soft pastry with cream cheese frosting.', 'An elegant red velvet pastry with smooth frosting and bakery-fresh crumb.', 'Flour, cocoa, buttermilk, cream cheese, sugar', '290 kcal | 4g protein | 36g carbs | 14g fat', '10 mins', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=900&q=80', 159.00, 139.00, 4.6, 103, 'available', 0, 1, 1, 0, 1, 'mild', 170),
(5, 'Blueberry Cheesecake', 'blueberry-cheesecake', 'Baked cheesecake with blueberry compote.', 'Creamy cheesecake finished with blueberry compote and crunchy biscuit base.', 'Cream cheese, blueberries, biscuit, butter, sugar', '360 kcal | 6g protein | 31g carbs | 23g fat', '14 mins', 'https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=900&q=80', 189.00, NULL, 4.8, 119, 'available', 1, 1, 1, 1, 0, 'mild', 230),
(5, 'Churro Bites', 'churro-bites', 'Cinnamon sugar churro bites.', 'Crispy churro bites dusted with cinnamon sugar and served with dip.', 'Flour, butter, cinnamon sugar, chocolate dip', '310 kcal | 4g protein | 42g carbs | 14g fat', '12 mins', 'https://images.unsplash.com/photo-1621303837174-89787a7d4729?auto=format&fit=crop&w=900&q=80', 139.00, NULL, 4.4, 72, 'available', 0, 0, 0, 1, 1, 'mild', 105),

(6, 'Paneer Kathi Wrap', 'paneer-kathi-wrap', 'Loaded wrap with paneer tikka.', 'A street-style wrap packed with paneer tikka, onion rings, and mint sauce.', 'Paneer, tortilla, onion, mint chutney', '450 kcal | 18g protein | 39g carbs | 21g fat', '15 mins', 'https://images.unsplash.com/photo-1645474326260-6ed4d4ad8eb3?auto=format&fit=crop&w=900&q=80', 209.00, 189.00, 4.5, 90, 'available', 0, 1, 1, 0, 0, 'medium', 176),
(6, 'Chicken Shawarma Wrap', 'chicken-shawarma-wrap', 'Middle Eastern style grilled wrap.', 'Succulent shawarma chicken rolled in toasted flatbread with garlic sauce.', 'Chicken, flatbread, garlic sauce, lettuce, pickle', '510 kcal | 28g protein | 42g carbs | 24g fat', '16 mins', 'https://images.unsplash.com/photo-1529006557810-274b9b2fc783?auto=format&fit=crop&w=900&q=80', 239.00, NULL, 4.7, 126, 'available', 1, 1, 1, 1, 0, 'medium', 295),
(6, 'Falafel Wrap', 'falafel-wrap', 'Crunchy falafel with hummus.', 'A light yet filling wrap loaded with falafel, hummus, salad, and tahini.', 'Falafel, hummus, tortilla, salad, tahini', '430 kcal | 14g protein | 48g carbs | 16g fat', '14 mins', 'https://images.unsplash.com/photo-1530469912745-a215c6b256ea?auto=format&fit=crop&w=900&q=80', 199.00, NULL, 4.3, 66, 'available', 0, 0, 0, 0, 1, 'mild', 120),
(6, 'Peri Peri Chicken Wrap', 'peri-peri-chicken-wrap', 'Spicy wrap with peri peri sauce.', 'A spicy wrap featuring grilled chicken strips, peppers, and peri peri mayo.', 'Chicken, tortilla, peppers, peri peri mayo', '500 kcal | 27g protein | 43g carbs | 23g fat', '16 mins', 'https://images.unsplash.com/photo-1532635241-17e820acc59f?auto=format&fit=crop&w=900&q=80', 249.00, 229.00, 4.6, 88, 'available', 0, 1, 1, 1, 1, 'hot', 168),
(6, 'Loaded Veg Burrito', 'loaded-veg-burrito', 'Rice, beans and salsa wrap.', 'A burrito-style wrap with beans, rice, salsa, corn, and sour cream.', 'Rice, beans, salsa, corn, tortilla, sour cream', '520 kcal | 15g protein | 61g carbs | 19g fat', '17 mins', 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?auto=format&fit=crop&w=900&q=80', 229.00, NULL, 4.4, 59, 'available', 0, 0, 0, 0, 1, 'medium', 111);

INSERT INTO food_variants (food_id, variant_name, price, sort_order) VALUES
(1, 'Small', 219.00, 1), (1, 'Medium', 249.00, 2), (1, 'Large', 319.00, 3),
(2, 'Medium', 299.00, 1), (2, 'Large', 369.00, 2),
(6, 'Regular', 159.00, 1), (6, 'Combo', 229.00, 2),
(11, 'Single', 259.00, 1), (11, 'Family Pack', 459.00, 2),
(27, 'Regular', 189.00, 1), (27, 'Meal Combo', 269.00, 2);

INSERT INTO food_addons (food_id, addon_id) VALUES
(1, 1), (1, 2), (2, 1), (2, 2), (3, 1), (3, 3),
(6, 4), (6, 5), (7, 4), (7, 5),
(11, 6), (11, 5), (12, 6),
(16, 5), (17, 8),
(21, 8), (22, 7),
(27, 6), (28, 4), (29, 6), (30, 5);

INSERT INTO addresses (user_id, label, contact_name, phone, address_line, city, state, postal_code, is_default) VALUES
(2, 'Home', 'Sample Customer', '9000000002', 'Flat 204, Green Residency, Baner Road', 'Pune', 'Maharashtra', '411045', 1);

INSERT INTO favorites (user_id, food_id) VALUES
(2, 2), (2, 7), (2, 11), (2, 23);

INSERT INTO recently_viewed (user_id, food_id, viewed_at) VALUES
(2, 2, NOW() - INTERVAL 2 HOUR),
(2, 11, NOW() - INTERVAL 90 MINUTE),
(2, 23, NOW() - INTERVAL 30 MINUTE),
(2, 28, NOW() - INTERVAL 10 MINUTE);

INSERT INTO reviews (user_id, food_id, rating, review_text) VALUES
(2, 2, 5, 'Loaded with veggies and cheese. Perfect for group orders.'),
(2, 7, 4, 'Crispy and juicy burger with a good crunch.'),
(2, 11, 5, 'Best biryani in the menu. Fragrant and filling.'),
(2, 23, 5, 'Blueberry cheesecake tastes premium and fresh.');

INSERT INTO orders (order_number, user_id, address_id, customer_name, customer_phone, delivery_address, payment_method, payment_status, order_status, subtotal, discount_amount, delivery_fee, total_amount, coupon_code, notes, placed_at) VALUES
('ORD-20260621-001', 2, 1, 'Sample Customer', '9000000002', 'Flat 204, Green Residency, Baner Road, Pune', 'Cash on Delivery', 'pending', 'Pending', 698.00, 100.00, 40.00, 638.00, 'FLAT100', 'Ring the bell once', NOW() - INTERVAL 1 DAY),
('ORD-20260621-002', 2, 1, 'Sample Customer', '9000000002', 'Flat 204, Green Residency, Baner Road, Pune', 'UPI', 'paid', 'Delivered', 548.00, 82.20, 30.00, 495.80, 'FLASH15', 'Leave at reception', NOW() - INTERVAL 3 HOUR),
('ORD-20260621-003', 2, 1, 'Sample Customer', '9000000002', 'Flat 204, Green Residency, Baner Road, Pune', 'Google Pay', 'paid', 'Preparing', 398.00, 0.00, 30.00, 428.00, NULL, NULL, NOW() - INTERVAL 1 HOUR);

INSERT INTO order_items (order_id, food_id, variant_name, quantity, unit_price, total_price, addons_summary) VALUES
(1, 2, 'Large', 1, 369.00, 369.00, 'Extra Cheese'),
(1, 7, NULL, 1, 199.00, 199.00, 'French Fries'),
(1, 16, NULL, 1, 129.00, 129.00, NULL),
(2, 11, 'Single', 1, 259.00, 259.00, 'Mint Dip'),
(2, 21, NULL, 1, 159.00, 159.00, NULL),
(2, 23, NULL, 1, 189.00, 189.00, NULL),
(3, 28, NULL, 1, 239.00, 239.00, 'French Fries'),
(3, 18, NULL, 1, 169.00, 169.00, NULL);

INSERT INTO order_status_history (order_id, status, note, created_at) VALUES
(1, 'Pending', 'Order placed by customer', NOW() - INTERVAL 1 DAY),
(2, 'Pending', 'Order placed by customer', NOW() - INTERVAL 3 HOUR),
(2, 'Accepted', 'Restaurant accepted the order', NOW() - INTERVAL 170 MINUTE),
(2, 'Preparing', 'Chef started preparing the order', NOW() - INTERVAL 150 MINUTE),
(2, 'Ready', 'Packed and ready for dispatch', NOW() - INTERVAL 100 MINUTE),
(2, 'Out For Delivery', 'Delivery partner picked up the order', NOW() - INTERVAL 70 MINUTE),
(2, 'Delivered', 'Delivered successfully', NOW() - INTERVAL 30 MINUTE),
(3, 'Pending', 'Order placed by customer', NOW() - INTERVAL 60 MINUTE),
(3, 'Accepted', 'Accepted by restaurant', NOW() - INTERVAL 45 MINUTE),
(3, 'Preparing', 'Currently being prepared', NOW() - INTERVAL 15 MINUTE);

INSERT INTO payments (order_id, payment_method, transaction_ref, amount, payment_status, paid_at) VALUES
(1, 'Cash on Delivery', NULL, 638.00, 'pending', NULL),
(2, 'UPI', 'UPI-TXN-458392', 495.80, 'success', NOW() - INTERVAL 3 HOUR),
(3, 'Google Pay', 'GPay-299183', 428.00, 'success', NOW() - INTERVAL 1 HOUR);

INSERT INTO notifications (user_id, title, message) VALUES
(2, 'Order accepted', 'Your order ORD-20260621-003 has been accepted by the restaurant.'),
(2, 'Hot deal unlocked', 'Use WELCOME20 on your next order above Rs. 399.');

UPDATE foods f
LEFT JOIN (
    SELECT food_id, AVG(rating) AS avg_rating, COUNT(*) AS review_total
    FROM reviews
    GROUP BY food_id
) r ON r.food_id = f.id
SET f.rating = COALESCE(r.avg_rating, f.rating),
    f.review_count = COALESCE(r.review_total, f.review_count);

UPDATE foods f
LEFT JOIN (
    SELECT food_id, SUM(quantity) AS total_sold
    FROM order_items
    GROUP BY food_id
) oi ON oi.food_id = f.id
SET f.sold_count = COALESCE(oi.total_sold, f.sold_count);
