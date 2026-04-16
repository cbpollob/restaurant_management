<?php
// ============================================================
//  Cloudinary upload helper – includes/cloudinary.php
// ============================================================

require_once __DIR__ . '/config.php';

/**
 * Upload a local file to Cloudinary via the REST API.
 *
 * @param string $filePath  Absolute path to the temporary upload file.
 * @param string $fileName  Original file name (used as public_id base).
 * @return string|false     The secure_url on success, false on failure.
 */
function uploadToCloudinary(string $filePath, string $fileName): string|false {
    $cloudName  = CLOUDINARY_CLOUD_NAME;
    $apiKey     = CLOUDINARY_API_KEY;
    $apiSecret  = CLOUDINARY_API_SECRET;
    $folder     = CLOUDINARY_FOLDER;

    $timestamp  = time();
    $publicId   = $folder . '/' . pathinfo($fileName, PATHINFO_FILENAME) . '_' . $timestamp;

    // Build signature string
    $sigParams  = [
        'folder'    => $folder,
        'public_id' => $publicId,
        'timestamp' => $timestamp,
    ];
    ksort($sigParams);

    $sigString = '';
    foreach ($sigParams as $k => $v) {
        $sigString .= ($sigString === '' ? '' : '&') . $k . '=' . $v;
    }
    $sigString .= $apiSecret;
    $signature  = sha1($sigString);

    // Build POST fields
    $postFields = [
        'file'      => new CURLFile($filePath),
        'api_key'   => $apiKey,
        'timestamp' => $timestamp,
        'folder'    => $folder,
        'public_id' => $publicId,
        'signature' => $signature,
    ];

    $url = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $postFields,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
    ]);

    $response = curl_exec($ch);
    $error    = curl_error($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($error || $httpCode !== 200) {
        error_log("Cloudinary upload error: " . ($error ?: "HTTP $httpCode – $response"));
        return false;
    }

    $data = json_decode($response, true);
    return $data['secure_url'] ?? false;
}
