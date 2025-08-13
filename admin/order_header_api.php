<?php
// Admin: return order headers with safe column names
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/db_connect.php';

header('Content-Type: application/json');

if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'forbidden']);
    exit;
}

$page  = max(1, (int)($_GET['page']  ?? 1));
$limit = min(100, max(1, (int)($_GET['limit'] ?? 50)));
$offset = ($page - 1) * $limit;

// total count
$cntRes = $conn->query("SELECT COUNT(*) AS c FROM orders");
$total  = (int)$cntRes->fetch_assoc()['c'];

// IMPORTANT: alias delivery_type/delivery_option to a single field name
$sql = "
  SELECT
    o.order_id,
    o.user_id,
    u.full_name,
    o.total_amount,
    o.status,
    o.created_at,
    COALESCE(o.delivery_type, o.delivery_option) AS delivery_type,
    o.address_line1, o.suburb, o.state, o.postcode, o.notes
  FROM orders o
  LEFT JOIN users u ON u.user_id = o.user_id
  ORDER BY o.order_id DESC
  LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$res = $stmt->get_result();

$data = [];
while ($row = $res->fetch_assoc()) {
    // If your frontend expects "delivery_option", also provide it:
    $row['delivery_option'] = $row['delivery_type']; // same value
    $data[] = $row;
}

echo json_encode([
    'page'  => $page,
    'limit' => $limit,
    'total' => $total,
    'data'  => $data
]);
