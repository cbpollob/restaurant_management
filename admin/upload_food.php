<?php
// ============================================================
//  admin/upload_food.php – Handle image upload to Cloudinary
// ============================================================
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/cloudinary.php';

header('Content-Type: application/json; charset=utf-8');

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed.');
}
if (empty($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errMsg = [
        UPLOAD_ERR_INI_SIZE   => 'File exceeds server upload limit.',
        UPLOAD_ERR_FORM_SIZE  => 'File exceeds form size limit.',
        UPLOAD_ERR_PARTIAL    => 'File only partially uploaded.',
        UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
    ];
    $code = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
    jsonResponse(false, $errMsg[$code] ?? 'Upload error.');
}

$file     = $_FILES['image'];
$allowed  = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
$mimeType = mime_content_type($file['tmp_name']);

if (!in_array($mimeType, $allowed)) {
    jsonResponse(false, 'Only JPEG, PNG, WebP and GIF images are allowed.');
}
if ($file['size'] > 5 * 1024 * 1024) {
    jsonResponse(false, 'File size must be under 5 MB.');
}

$secureUrl = uploadToCloudinary($file['tmp_name'], $file['name']);

if ($secureUrl === false) {
    jsonResponse(false, 'Failed to upload image to Cloudinary. Check your API credentials.');
}

jsonResponse(true, 'Image uploaded successfully.', ['url' => $secureUrl]);
