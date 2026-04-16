<?php
// ============================================================
//  Application Configuration
// ============================================================

// -- Database --
define('DB_HOST',    'localhost');
define('DB_NAME',    'restaurant_management');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// -- Cloudinary --
define('CLOUDINARY_CLOUD_NAME',    'your_cloud_name');
define('CLOUDINARY_API_KEY',       'your_api_key');
define('CLOUDINARY_API_SECRET',    'your_api_secret');
define('CLOUDINARY_UPLOAD_PRESET', 'restaurant_food');
define('CLOUDINARY_FOLDER',        'restaurant_food');

// -- App --
define('BASE_URL',         'http://localhost/restaurant');
define('APP_NAME',         'RestauRant');
define('SESSION_LIFETIME', 3600);
