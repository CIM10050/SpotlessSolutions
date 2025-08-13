<?php
// checkout.php — use the same $cart for UI + order insert
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/db_connect.php';

// Only logged-in customers
if (empty($_SESSION['user_id']) || (($_SESSION['user_role'] ?? '') !== 'customer')) {
    header('Location: login.php');
    exit;
}

// Try to load cart helpers (optional)
@require_once __DIR__ . '/includes/cart_functions.php';

$err = '';
$placed = false;

/**
 * Build a $cart shape:
 * [
 *   'services' => [ ['id'=>..,'name'=>..,'qty'=>..,'unit'=>..,'line'=>..], ... ],
 *   'products' => [ ... ],
 *   'total' => float
 * ]
 * Prefer cartGetDetails($conn) if provided by your app.
 */
function buildCart($conn) {
    if (function_exists('cartGetDetails')) {
        $c = cartGetDetails($conn);
        $c['services'] = $c['services'] ?? [];
        $c['products'] = $c['products'] ?? [];
        $c['total']    = (float)($c['total'] ?? 0);
        return $c;
    }

    // ---- Fallback if no helper exists: read session + hydrate from DB ----
    $normalize = function ($arr) {
        $out = [];
        if (!is_array($arr)) return $out;
        foreach ($arr as $item) {
            if (is_array($item) && isset($item['id'])) {
                $id  = (int)$item['id'];
                $qty = max(1, (int)($item['qty'] ?? 1));
                $out[$id] = ($out[$id] ?? 0) + $qty;
            } else {
                $id  = (int)$item;
                $out[$id] = ($out[$id] ?? 0) + 1;
            }
        }
        return $out;
    };

    $svcMap = $normalize($_SESSION['cart_services'] ?? ($_SESSION['cart']['services'] ?? ($_SESSION['services_cart'] ?? [])));
    $prdMap = $normalize($_SESSION['cart_products'] ?? ($_SESSION['cart']['products'] ?? ($_SESSION['products_cart'] ?? [])));

    $services = [];
    $products = [];
    $total = 0.0;

    if ($svcMap) {
        $ids = implode(',', array_map('intval', array_keys($svcMap)));
        $rs = $conn->query("SELECT service_id, service_name, price FROM services WHERE service_id IN ($ids)");
        while ($row = $rs->fetch_assoc()) {
            $id   = (int)$row['service_id'];
            $qty  = (int)$svcMap[$id];
            $unit = (float)$row['price'];
            $line = $qty * $unit;
            $services[] = ['id'=>$id,'name'=>$row['service_name'],'qty'=>$qty,'unit'=>$unit,'line'=>$line];
            $total += $line;
        }
    }
    if ($prdMap) {
        $ids = implode(',', array_map('intval', array_keys($prdMap)));
        $rp = $conn->query("SELECT product_id, product_name, price FROM products WHERE product_id IN ($ids)");
        while ($row = $rp->fetch_assoc()) {
            $id   = (int)$row['product_id'];
            $qty  = (int)$prdMap[$id];
            $unit = (float)$row['price'];
            $line = $qty * $unit;
            $products[] = ['id'=>$id,'name'=>$row['product_name'],'qty'=>$qty,'unit'=>$unit,'line'=>$line];
            $total += $line;
        }
    }

    return ['services'=>$services, 'products'=>$products, 'total'=>$total];
}

$cart = buildCart($conn);

/** Helper: does a table have a given column? */
function tableHasColumn(mysqli $conn, string $table, string $col): bool {
    $table = $conn->real_escape_string($table);
    $col   = $conn->real_escape_string($col);
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$col'");
    return $res && $res->num_rows > 0;
}

// Place order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $hasItems = (!empty($cart['services']) || !empty($cart['products']));
    if (!$hasItems) {
        $err = "Your cart is empty.";
    } else {
        $delivery_type = $_POST['delivery_type'] ?? 'walkin';
        $address_line1 = trim($_POST['address_line1'] ?? '');
        $suburb        = trim($_POST['suburb'] ?? '');
        $state         = trim($_POST['state'] ?? '');
        $postcode      = trim($_POST['postcode'] ?? '');
        $notes         = trim($_POST['notes'] ?? '');

        // Require address for pickup/drop-off
        if (in_array($delivery_type, ['pickup', 'dropoff'], true)) {
            if ($address_line1 === '' || $suburb === '' || $state === '' || $postcode === '') {
                $err = "Please provide full address for pickup/drop-off.";
            }
        }

        if ($err === '') {
            $conn->begin_transaction();
            try {
                // ---- Dynamic order header INSERT (only insert columns that exist) ----
                $uid = (int)$_SESSION['user_id'];
                $totalAmount = (float)$cart['total'];

                $possible = [
                    'user_id'       => ['v'=>$uid,           't'=>'i'],
                    'delivery_type' => ['v'=>$delivery_type, 't'=>'s'],
                    'address_line1' => ['v'=>$address_line1, 't'=>'s'],
                    'suburb'        => ['v'=>$suburb,        't'=>'s'],
                    'state'         => ['v'=>$state,         't'=>'s'],
                    'postcode'      => ['v'=>$postcode,      't'=>'s'],
                    'notes'         => ['v'=>$notes,         't'=>'s'],
                    'total_amount'  => ['v'=>$totalAmount,   't'=>'d'],
                    'status'        => ['v'=>'pending',      't'=>'s'],
                ];

                $cols = [];
                $placeholders = [];
                $types = '';
                $vals = [];

                foreach ($possible as $col => $meta) {
                    if (tableHasColumn($conn, 'orders', $col)) {
                        $cols[] = $col;
                        $placeholders[] = '?';
                        $types .= $meta['t'];
                        $vals[] = $meta['v'];
                    }
                }

                if (!$cols) {
                    throw new Exception("No matching columns found in 'orders' table.");
                }

                $sql = "INSERT INTO orders (" . implode(',', $cols) . ") VALUES (" . implode(',', $placeholders) . ")";
                $stmt = $conn->prepare($sql);

                // bind dynamically with references
                $bind = [];
                $bind[] = &$types;
                for ($i=0; $i<count($vals); $i++) {
                    $bind[] = &$vals[$i];
                }
                call_user_func_array([$stmt, 'bind_param'], $bind);

                $stmt->execute();
                $orderId = $stmt->insert_id;
                $stmt->close();

                // Lines: services
                if (!empty($cart['services'])) {
                    $si = $conn->prepare("
                        INSERT INTO order_items
                          (order_id, item_type, ref_id, name, qty, unit_price, line_total)
                        VALUES (?, 'service', ?, ?, ?, ?, ?)
                    ");
                    foreach ($cart['services'] as $it) {
                        $ref  = (int)$it['id'];
                        $name = $it['name'];
                        $qty  = (int)$it['qty'];
                        $unit = (float)$it['unit'];
                        $line = (float)$it['line'];
                        $si->bind_param("iisidd", $orderId, $ref, $name, $qty, $unit, $line);
                        $si->execute();
                    }
                    $si->close();
                }

                // Lines: products
                if (!empty($cart['products'])) {
                    $pi = $conn->prepare("
                        INSERT INTO order_items
                          (order_id, item_type, ref_id, name, qty, unit_price, line_total)
                        VALUES (?, 'product', ?, ?, ?, ?, ?)
                    ");
                    foreach ($cart['products'] as $it) {
                        $ref  = (int)$it['id'];
                        $name = $it['name'];
                        $qty  = (int)$it['qty'];
                        $unit = (float)$it['unit'];
                        $line = (float)$it['line'];
                        $pi->bind_param("iisidd", $orderId, $ref, $name, $qty, $unit, $line);
                        $pi->execute();
                    }
                    $pi->close();
                }

                $conn->commit();

                // Clear cart (prefer helper)
                if (function_exists('cartClear')) {
                    cartClear();
                } else {
                    unset($_SESSION['cart_services'], $_SESSION['cart_products']);
                    if (!empty($_SESSION['cart'])) {
                        unset($_SESSION['cart']['services'], $_SESSION['cart']['products']);
                    }
                    unset($_SESSION['services_cart'], $_SESSION['products_cart']);
                }

                $placed = true;

            } catch (Throwable $e) {
                $conn->rollback();
                $err = "Order failed: " . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="max-w-7xl mx-auto p-4">
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <!-- Left: Form -->
    <div class="lg:col-span-2">
      <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-3xl font-bold text-blue-700 mb-6">Checkout</h2>

        <?php if ($placed): ?>
          <div class="bg-green-100 text-green-700 p-3 rounded mb-5">
            ✅ Order placed successfully! <a class="underline" href="my_orders.php">View my orders</a>.
          </div>
        <?php elseif ($err): ?>
          <div class="bg-red-100 text-red-700 p-3 rounded mb-5"><?= htmlspecialchars($err) ?></div>
        <?php endif; ?>

        <?php if (!$placed): ?>
        <form method="post" class="space-y-5">
          <div>
            <label class="block font-semibold mb-2">Delivery Option</label>
            <select name="delivery_type" id="delivery_type"
                    class="w-full border p-3 rounded focus:outline-none focus:ring-2 focus:ring-blue-500">
              <option value="walkin">Walk-in at store</option>
              <option value="pickup">Pickup from home</option>
              <option value="dropoff">Drop-off at home</option>
            </select>
          </div>

          <div id="addressBlock" class="hidden">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm mb-1">Address Line</label>
                <input type="text" name="address_line1" class="w-full border p-3 rounded" placeholder="123 George St">
              </div>
              <div>
                <label class="block text-sm mb-1">Suburb</label>
                <input type="text" name="suburb" class="w-full border p-3 rounded" placeholder="Parramatta">
              </div>
              <div>
                <label class="block text-sm mb-1">State</label>
                <input type="text" name="state" class="w-full border p-3 rounded" placeholder="NSW">
              </div>
              <div>
                <label class="block text-sm mb-1">Postcode</label>
                <input type="text" name="postcode" class="w-full border p-3 rounded" placeholder="2000">
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm mb-1">Notes (optional)</label>
            <textarea name="notes" rows="3" class="w-full border p-3 rounded" placeholder="Any special instructions"></textarea>
          </div>

          <button type="submit" name="place_order"
                  class="bg-green-600 text-white px-6 py-3 rounded hover:bg-green-700 transition">
            Place Order
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>

    <!-- Right: Summary -->
    <aside>
      <div class="bg-white rounded-xl shadow p-6 h-fit">
        <h3 class="text-2xl font-semibold mb-4">Order Summary</h3>

        <?php if (empty($cart['services']) && empty($cart['products'])): ?>
          <p class="text-gray-500">Your cart is empty.</p>
        <?php else: ?>
          <?php if (!empty($cart['services'])): ?>
            <h4 class="font-semibold text-gray-700 mb-1">Services</h4>
            <ul class="mb-3 space-y-1">
              <?php foreach ($cart['services'] as $item): ?>
                <li class="flex justify-between text-sm">
                  <span><?= htmlspecialchars($item['name']) ?> × 
                    <input type="number" value="<?= (int)$item['qty'] ?>" class="w-16 border rounded px-2 py-1" readonly>
                  </span>
                  <span>$<?= number_format((float)$item['line'], 2) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>

          <?php if (!empty($cart['products'])): ?>
            <h4 class="font-semibold text-gray-700 mb-1">Products</h4>
            <ul class="mb-3 space-y-1">
              <?php foreach ($cart['products'] as $item): ?>
                <li class="flex justify-between text-sm">
                  <span><?= htmlspecialchars($item['name']) ?> × 
                    <input type="number" value="<?= (int)$item['qty'] ?>" class="w-16 border rounded px-2 py-1" readonly>
                  </span>
                  <span>$<?= number_format((float)$item['line'], 2) ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>

          <div class="border-t pt-3 mt-3 flex justify-between font-bold">
            <span>Total</span>
            <span>$<?= number_format((float)$cart['total'], 2) ?></span>
          </div>
        <?php endif; ?>
      </div>
    </aside>
  </div>
</div>

<script>
  // toggle address fields on delivery type change
  const typeSel = document.getElementById('delivery_type');
  const addr = document.getElementById('addressBlock');
  function syncAddr() {
    addr.classList.toggle('hidden', !(typeSel.value === 'pickup' || typeSel.value === 'dropoff'));
  }
  if (typeSel) {
    typeSel.addEventListener('change', syncAddr);
    syncAddr();
  }
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
