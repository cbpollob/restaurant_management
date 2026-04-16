<?php
// ============================================================
//  General helper functions – includes/functions.php
// ============================================================

/**
 * Sanitize user input for output.
 */
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Output a JSON response and exit.
 *
 * @param bool   $success
 * @param string $message
 * @param array  $data
 */
function jsonResponse(bool $success, string $message, array $data = []): void {
    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(array_merge(['success' => $success, 'message' => $message], $data));
    exit;
}

/**
 * Generate a unique order code.
 */
function generateOrderCode(): string {
    return 'ORD-' . strtoupper(substr(uniqid('', true), -8));
}

// ============================================================
//  Cart helpers  (session-based)
// ============================================================

/**
 * Return the full cart array from session.
 */
function getCartFromSession(): array {
    return $_SESSION['cart'] ?? [];
}

/**
 * Add or increment an item in the session cart.
 */
function addToCart(int $food_id, int $qty, float $price, string $name, string $image): void {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $key = (string) $food_id;
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] += $qty;
    } else {
        $_SESSION['cart'][$key] = [
            'food_id' => $food_id,
            'name'    => $name,
            'price'   => $price,
            'image'   => $image,
            'qty'     => $qty,
        ];
    }
}

/**
 * Remove an item from the session cart.
 */
function removeFromCart(int $food_id): void {
    $key = (string) $food_id;
    unset($_SESSION['cart'][$key]);
}

/**
 * Update the quantity of a cart item.
 * Removes the item if qty <= 0.
 */
function updateCartQty(int $food_id, int $qty): void {
    $key = (string) $food_id;
    if ($qty <= 0) {
        unset($_SESSION['cart'][$key]);
    } elseif (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] = $qty;
    }
}

/**
 * Return the cart subtotal.
 */
function getCartTotal(): float {
    $total = 0.0;
    foreach (getCartFromSession() as $item) {
        $total += $item['price'] * $item['qty'];
    }
    return $total;
}

/**
 * Return the total number of items (sum of quantities) in the cart.
 */
function getCartCount(): int {
    $count = 0;
    foreach (getCartFromSession() as $item) {
        $count += $item['qty'];
    }
    return $count;
}
