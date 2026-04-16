-- Restaurant Management System - Seed Data
USE restaurant_management;

-- --------------------------------------------------------
-- Categories
-- --------------------------------------------------------
INSERT INTO categories (name, slug, status) VALUES
('Starters',     'starters',     'active'),
('Main Course',  'main-course',  'active'),
('Desserts',     'desserts',     'active'),
('Drinks',       'drinks',       'active'),
('Fast Food',    'fast-food',    'active'),
('Seafood',      'seafood',      'active');

-- --------------------------------------------------------
-- Food Items  (category_id refs above, 1-6)
-- --------------------------------------------------------
INSERT INTO food_items (category_id, name, description, price, image_url, stock, featured, status) VALUES
-- Starters (1)
(1, 'Garlic Bread',
 'Crispy baguette slices topped with roasted garlic butter and fresh herbs, served warm.',
 120.00, 'https://res.cloudinary.com/demo/image/upload/v1/restaurant_food/garlic_bread.jpg',
 50, 1, 'active'),
(1, 'Chicken Wings',
 'Juicy bone-in wings tossed in our signature spicy buffalo sauce, served with ranch dip.',
 280.00, 'https://res.cloudinary.com/demo/image/upload/v1/restaurant_food/chicken_wings.jpg',
 40, 0, 'active'),

-- Main Course (2)
(2, 'Grilled Chicken',
 'Tender marinated chicken breast grilled to perfection, served with seasonal vegetables and mashed potato.',
 450.00, 'https://res.cloudinary.com/demo/image/upload/v1/restaurant_food/grilled_chicken.jpg',
 30, 1, 'active'),
(2, 'Beef Steak',
 'Premium 200g sirloin steak cooked to your preference, served with fries and mushroom sauce.',
 750.00, 'https://res.cloudinary.com/demo/image/upload/v1/restaurant_food/beef_steak.jpg',
 20, 1, 'active'),
(2, 'Paneer Butter Masala',
 'Rich and creamy tomato-based curry with soft paneer cubes, best paired with naan or rice.',
 320.00, 'https://res.cloudinary.com/demo/image/upload/v1/restaurant_food/paneer_masala.jpg',
 35, 0, 'active'),

-- Desserts (3)
(3, 'Chocolate Lava Cake',
 'Warm dark chocolate cake with a molten center, served with a scoop of vanilla ice cream.',
 220.00, 'https://res.cloudinary.com/demo/image/upload/v1/restaurant_food/lava_cake.jpg',
 25, 1, 'active'),
(3, 'Gulab Jamun',
 'Soft milk-solid dumplings soaked in rose-flavoured sugar syrup, served warm (4 pieces).',
 150.00, 'https://res.cloudinary.com/demo/image/upload/v1/restaurant_food/gulab_jamun.jpg',
 60, 0, 'active'),

-- Drinks (4)
(4, 'Mango Lassi',
 'Chilled blend of fresh Alphonso mango pulp, yogurt and a hint of cardamom. (400 ml)',
 130.00, 'https://res.cloudinary.com/demo/image/upload/v1/restaurant_food/mango_lassi.jpg',
 80, 0, 'active'),
(4, 'Fresh Lemonade',
 'Hand-squeezed lemon juice with mint leaves, black salt and sparkling water. (400 ml)',
 90.00,  'https://res.cloudinary.com/demo/image/upload/v1/restaurant_food/lemonade.jpg',
 100, 0, 'active'),

-- Fast Food (5)
(5, 'Classic Burger',
 'Juicy beef patty with lettuce, tomato, pickles and our secret sauce in a brioche bun.',
 250.00, 'https://res.cloudinary.com/demo/image/upload/v1/restaurant_food/burger.jpg',
 45, 0, 'active'),
(5, 'Loaded Fries',
 'Crispy fries loaded with cheddar cheese sauce, jalapeños, bacon bits and sour cream.',
 180.00, 'https://res.cloudinary.com/demo/image/upload/v1/restaurant_food/loaded_fries.jpg',
 55, 0, 'active'),

-- Seafood (6)
(6, 'Grilled Prawns',
 'Tiger prawns marinated in garlic-herb butter and grilled, served with lemon and tartar sauce.',
 580.00, 'https://res.cloudinary.com/demo/image/upload/v1/restaurant_food/grilled_prawns.jpg',
 20, 0, 'active');

-- --------------------------------------------------------
-- Users
-- password hash below corresponds to 'Admin@123' / 'User@123'
-- via PHP password_hash($pass, PASSWORD_BCRYPT)
-- --------------------------------------------------------
INSERT INTO users (name, email, phone, password_hash, role) VALUES
('Admin User',   'admin@restaurant.com', '01700000000',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHe/tO/4.', 'admin'),
('Alice Rahman',  'alice@example.com',   '01711111111',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHe/tO/4.', 'customer'),
('Bob Hossain',   'bob@example.com',     '01722222222',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHe/tO/4.', 'customer'),
('Carol Islam',   'carol@example.com',   '01733333333',
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uHe/tO/4.', 'customer');

-- --------------------------------------------------------
-- Promo Codes
-- --------------------------------------------------------
INSERT INTO promo_codes (code, discount_percent, min_order, max_uses, used_count, expires_at, status) VALUES
('SAVE10',    10, 200.00, 500, 12, '2025-12-31', 'active'),
('WELCOME20', 20, 300.00, 200,  3, '2025-12-31', 'active'),
('FLAT5',      5, 100.00, 999,  8, '2025-12-31', 'active');

-- --------------------------------------------------------
-- Sample Orders
-- --------------------------------------------------------
INSERT INTO orders (user_id, order_code, type, subtotal, discount, total, status, address, phone, promo_code) VALUES
(2, 'ORD-ALPHA001', 'delivery', 730.00, 73.00, 657.00, 'delivered',
 '12 Mirpur Road, Dhaka', '01711111111', 'SAVE10'),
(3, 'ORD-BRAVO002', 'dine-in',  450.00,  0.00, 450.00, 'confirmed',
 NULL, '01722222222', NULL),
(4, 'ORD-CHARLIE03', 'delivery', 580.00, 116.00, 464.00, 'preparing',
 '7 Gulshan Ave, Dhaka', '01733333333', 'WELCOME20');

-- --------------------------------------------------------
-- Order Items
-- --------------------------------------------------------
INSERT INTO order_items (order_id, food_id, quantity, unit_price, line_total) VALUES
(1, 3,  1, 450.00, 450.00),
(1, 8,  2, 130.00, 260.00),
(1, 7,  1, 150.00, 150.00),  -- adjusted for demo subtotal ~730

(2, 3,  1, 450.00, 450.00),

(3, 12, 1, 580.00, 580.00);

-- --------------------------------------------------------
-- Payments
-- --------------------------------------------------------
INSERT INTO payments (order_id, method, amount, status, txn_ref, paid_at) VALUES
(1, 'bkash',  657.00, 'success', 'TXN-BK-000001', '2024-03-10 14:22:00'),
(2, 'card',   450.00, 'success', 'TXN-CD-000002', '2024-03-11 19:05:00'),
(3, 'nagad',  464.00, 'success', 'TXN-NG-000003', '2024-03-12 11:30:00');

-- --------------------------------------------------------
-- Bookings
-- --------------------------------------------------------
INSERT INTO bookings (user_id, booking_date, time_slot, guests, notes, status) VALUES
(2, '2025-08-15', '07:00 PM', 4, 'Window seat preferred', 'confirmed'),
(3, '2025-08-16', '08:00 PM', 2, 'Anniversary dinner',    'pending'),
(4, '2025-08-17', '01:00 PM', 6, 'Birthday party',        'pending');

-- --------------------------------------------------------
-- Reviews
-- --------------------------------------------------------
INSERT INTO reviews (user_id, food_id, rating, comment) VALUES
(2, 3, 5, 'Absolutely delicious! The grilled chicken was perfectly seasoned.'),
(3, 4, 4, 'Great steak, cooked exactly medium-rare as requested.'),
(4, 6, 5, 'The lava cake was heavenly – warm, gooey and just perfect!'),
(2, 8, 4, 'Refreshing mango lassi, very authentic taste.'),
(3, 1, 5, 'Love the garlic bread – crispy outside and soft inside.');

-- --------------------------------------------------------
-- Wishlist
-- --------------------------------------------------------
INSERT INTO wishlist (user_id, food_id) VALUES
(2,  4),
(2,  6),
(3,  3),
(3, 12),
(4,  1);
