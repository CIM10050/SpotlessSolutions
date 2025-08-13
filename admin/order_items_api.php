<?php
// admin/order_items_api.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once dirname(__DIR__) . '/includes/auth_check.php';
require_once dirname(__DIR__) . '/includes/db_connect.php';

header('Content-Type: application/json');

// Admin only
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

// Inputs
$orderId  = (int)($_GET['order_id'] ?? $_POST['order_id'] ?? 0);
$refId    = isset($_GET['ref_id']) ? (int)$_GET['ref_id'] : (isset($_POST['ref_id']) ? (int)$_POST['ref_id'] : null);
$itemType = $_GET['item_type'] ?? $_POST['item_type'] ?? null; // 'service' or 'product'

if ($orderId <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'order_id required']);
    exit;
}

// Build SQL: read from order_items and (optionally) match ref_id / item_type
$sql = "
  SELECT
    oi.id                         AS item_id,          -- alias for UI
    oi.item_type,                                     -- 'service' | 'product'
    oi.ref_id                     AS item_ref_id,      -- alias for UI
    COALESCE(s.service_name, p.product_name, oi.name) AS name,  -- prefer master name
    oi.qty,
    oi.unit_price,
    oi.line_total
  FROM order_items oi
  LEFT JOIN services s ON (oi.item_type = 'service' AND s.service_id = oi.ref_id)
  LEFT JOIN products p ON (oi.item_type = 'product' AND p.product_id = oi.ref_id)
  WHERE oi.order_id = ?
";

$types = "i";
$args  = [$orderId];

if ($refId !== null) {
    $sql   .= " AND oi.ref_id = ?";
    $types .= "i";
    $args[] = $refId;
}
if ($itemType !== null && ($itemType === 'service' || $itemType === 'product')) {
    $sql   .= " AND oi.item_type = ?";
    $types .= "s";
    $args[] = $itemType;
}

$sql .= " ORDER BY oi.id ASC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'prepare failed', 'detail' => $conn->error]);
    exit;
}

// bind_param with dynamic args
$stmt->bind_param($types, ...$args);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
$subtotal = 0.0;
while ($row = $res->fetch_assoc()) {
    $row['qty']        = (int)$row['qty'];
    $row['unit_price'] = (float)$row['unit_price'];
    $row['line_total'] = (float)$row['line_total'];
    $subtotal += $row['line_total'];
    $items[] = $row;
}

echo json_encode([
    'order_id' => $orderId,
    'items'    => $items,
    'subtotal' => number_format($subtotal, 2, '.', '')
]);
