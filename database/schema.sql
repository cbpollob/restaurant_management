-- Restaurant Management System - Database Schema
-- Character Set: utf8mb4 for full Unicode support

CREATE DATABASE IF NOT EXISTS restaurant_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE restaurant_management;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(100)  NOT NULL,
  email         VARCHAR(150)  UNIQUE NOT NULL,
  phone         VARCHAR(20)   DEFAULT NULL,
  password_hash VARCHAR(255)  NOT NULL,
  role          ENUM('customer','admin') DEFAULT 'customer',
  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: categories
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(80)  NOT NULL,
  slug       VARCHAR(80)  UNIQUE NOT NULL,
  status     ENUM('active','inactive') DEFAULT 'active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: food_items
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS food_items (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  category_id INT           NOT NULL,
  name        VARCHAR(150)  NOT NULL,
  description TEXT,
  price       DECIMAL(10,2) NOT NULL,
  image_url   VARCHAR(500)  DEFAULT NULL,
  stock       INT           DEFAULT 0,
  featured    TINYINT(1)    DEFAULT 0,
  status      ENUM('active','inactive') DEFAULT 'active',
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_food_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_food_status   (status),
  INDEX idx_food_featured (featured),
  INDEX idx_food_name     (name),
  INDEX idx_food_category (category_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: orders
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS orders (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT           NOT NULL,
  order_code VARCHAR(20)   UNIQUE NOT NULL,
  type       ENUM('dine-in','delivery') DEFAULT 'delivery',
  subtotal   DECIMAL(10,2) NOT NULL,
  discount   DECIMAL(10,2) DEFAULT 0.00,
  total      DECIMAL(10,2) NOT NULL,
  status     ENUM('placed','confirmed','preparing','out_for_delivery','delivered','cancelled') DEFAULT 'placed',
  address    TEXT          DEFAULT NULL,
  phone      VARCHAR(20)   DEFAULT NULL,
  promo_code VARCHAR(30)   DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_order_user_id   (user_id),
  INDEX idx_order_status    (status),
  INDEX idx_order_created   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: order_items
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS order_items (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  order_id   INT           NOT NULL,
  food_id    INT           NOT NULL,
  quantity   INT           NOT NULL DEFAULT 1,
  unit_price DECIMAL(10,2) NOT NULL,
  line_total DECIMAL(10,2) NOT NULL,
  CONSTRAINT fk_oi_order FOREIGN KEY (order_id) REFERENCES orders(id)     ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_oi_food  FOREIGN KEY (food_id)  REFERENCES food_items(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_oi_order (order_id),
  INDEX idx_oi_food  (food_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: bookings
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS bookings (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  user_id      INT         NOT NULL,
  booking_date DATE        NOT NULL,
  time_slot    VARCHAR(20) NOT NULL,
  guests       INT         DEFAULT 2,
  notes        TEXT        DEFAULT NULL,
  status       ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
  created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_booking (booking_date, time_slot),
  CONSTRAINT fk_booking_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  INDEX idx_booking_date   (booking_date),
  INDEX idx_booking_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: payments
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS payments (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT           NOT NULL,
  method   ENUM('bkash','nagad','card') NOT NULL,
  amount   DECIMAL(10,2) NOT NULL,
  status   ENUM('pending','success','failed') DEFAULT 'pending',
  txn_ref  VARCHAR(60)   DEFAULT NULL,
  paid_at  TIMESTAMP     NULL DEFAULT NULL,
  CONSTRAINT fk_payment_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
  INDEX idx_payment_order  (order_id),
  INDEX idx_payment_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: reviews
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS reviews (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT        NOT NULL,
  food_id    INT        NOT NULL,
  rating     TINYINT(1) NOT NULL,
  comment    TEXT       DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(id)     ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_review_food FOREIGN KEY (food_id) REFERENCES food_items(id) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5),
  INDEX idx_review_food (food_id),
  INDEX idx_review_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: wishlist
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS wishlist (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  user_id    INT NOT NULL,
  food_id    INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_wishlist (user_id, food_id),
  CONSTRAINT fk_wl_user FOREIGN KEY (user_id) REFERENCES users(id)     ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_wl_food FOREIGN KEY (food_id) REFERENCES food_items(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: promo_codes
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS promo_codes (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  code             VARCHAR(30)   UNIQUE NOT NULL,
  discount_percent INT           NOT NULL DEFAULT 0,
  min_order        DECIMAL(10,2) DEFAULT 0.00,
  max_uses         INT           DEFAULT 100,
  used_count       INT           DEFAULT 0,
  expires_at       DATE          DEFAULT NULL,
  status           ENUM('active','inactive') DEFAULT 'active',
  INDEX idx_promo_code   (code),
  INDEX idx_promo_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
