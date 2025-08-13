<?php
// includes/cart_functions.php
if (session_status() === PHP_SESSION_NONE) session_start();

/**
 * Cart shape (in $_SESSION['cart']):
 * [
 *   'services' => [ service_id => qty, ... ],
 *   'products' => [ product_id => qty, ... ],
 * ]
 */

function ensureCart() {
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = ['services' => [], 'products' => []];
    } else {
        $_SESSION['cart'] += ['services' => [], 'products' => []]; // ensure keys
    }
}

function cartAdd($type, $id, $qty = 1) {
    ensureCart();
    $type = ($type === 'services') ? 'services' : 'products';
    $id = (int)$id; $qty = max(1, (int)$qty);
    if (!isset($_SESSION['cart'][$type][$id])) {
        $_SESSION['cart'][$type][$id] = 0;
    }
    $_SESSION['cart'][$type][$id] += $qty;
}

function cartSetQty($type, $id, $qty) {
    ensureCart();
    $type = ($type === 'services') ? 'services' : 'products';
    $id = (int)$id; $qty = (int)$qty;
    if ($qty <= 0) {
        unset($_SESSION['cart'][$type][$id]);
    } else {
        $_SESSION['cart'][$type][$id] = $qty;
    }
}

function cartRemove($type, $id) {
    ensureCart();
    $type = ($type === 'services') ? 'services' : 'products';
    $id = (int)$id;
    unset($_SESSION['cart'][$type][$id]);
}

function cartClear() {
    $_SESSION['cart'] = ['services' => [], 'products' => []];
}

function cartGetDetails(mysqli $conn) {
    ensureCart();
    $details = [
        'services' => [],
        'products' => [],
        'total'    => 0.00
    ];

    // Services
    if (!empty($_SESSION['cart']['services'])) {
        $ids = array_map('intval', array_keys($_SESSION['cart']['services']));
        $in  = implode(',', $ids);
        $sql = "SELECT service_id, service_name, price FROM services WHERE service_id IN ($in)";
        $res = $conn->query($sql);
        while ($row = $res->fetch_assoc()) {
            $sid = (int)$row['service_id'];
            $qty = (int)($_SESSION['cart']['services'][$sid] ?? 0);
            if ($qty < 1) continue;
            $unit = (float)$row['price'];
            $line = $unit * $qty;
            $details['services'][] = [
                'id'   => $sid,
                'name' => $row['service_name'],
                'unit' => $unit,
                'qty'  => $qty,
                'line' => $line,
            ];
            $details['total'] += $line;
        }
    }

    // Products
    if (!empty($_SESSION['cart']['products'])) {
        $ids = array_map('intval', array_keys($_SESSION['cart']['products']));
        $in  = implode(',', $ids);
        $sql = "SELECT product_id, product_name, price FROM products WHERE product_id IN ($in)";
        $res = $conn->query($sql);
        while ($row = $res->fetch_assoc()) {
            $pid = (int)$row['product_id'];
            $qty = (int)($_SESSION['cart']['products'][$pid] ?? 0);
            if ($qty < 1) continue;
            $unit = (float)$row['price'];
            $line = $unit * $qty;
            $details['products'][] = [
                'id'   => $pid,
                'name' => $row['product_name'],
                'unit' => $unit,
                'qty'  => $qty,
                'line' => $line,
            ];
            $details['total'] += $line;
        }
    }

    $details['total'] = round($details['total'], 2);
    return $details;
}
