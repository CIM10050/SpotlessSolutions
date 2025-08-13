<?php
// my_orders.php
include('includes/auth_check.php');
include('includes/config.php');
include('includes/db_connect.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'customer') {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

$userId = (int)$_SESSION['user_id'];

// Fetch orders for this user
$sql = "SELECT
  order_id,
  delivery_type AS delivery_option,  
  address_line1, suburb, state, postcode,
  total_amount, status, created_at
FROM orders
WHERE user_id = ?
ORDER BY order_id DESC;";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$ordersRes = mysqli_stmt_get_result($stmt);

function statusBadge($status) {
    $map = [
        'pending'   => 'bg-yellow-100 text-yellow-800',
        'confirmed' => 'bg-blue-100 text-blue-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
    $cls = $map[$status] ?? 'bg-gray-100 text-gray-700';
    return "<span class=\"px-2 py-1 rounded text-xs font-medium $cls capitalize\">$status</span>";
}

include('includes/header.php');
?>
<div class="max-w-7xl mx-auto p-4 mt-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
        <a href="<?= BASE_URL ?>/cart.php" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Go to Cart</a>
    </div>

    <?php if (mysqli_num_rows($ordersRes) === 0): ?>
        <div class="bg-white rounded-xl shadow p-6 text-center">
            <p class="text-gray-600">You have no orders yet.</p>
            <div class="mt-4 space-x-3">
                <a href="<?= BASE_URL ?>/services.php" class="px-4 py-2 rounded border hover:bg-gray-50">Book Services</a>
                <a href="<?= BASE_URL ?>/products.php" class="px-4 py-2 rounded border hover:bg-gray-50">Shop Products</a>
            </div>
        </div>
    <?php else: ?>
        <div class="space-y-4">
            <?php while ($o = mysqli_fetch_assoc($ordersRes)): ?>
                <?php
                // Fetch items
                $sqlItems = "SELECT id, item_type, ref_id, name, unit_price, qty, line_total
                             FROM order_items WHERE order_id = ?
                             ORDER BY id ASC";
                $stmtI = mysqli_prepare($conn, $sqlItems);
                mysqli_stmt_bind_param($stmtI, "i", $o['order_id']);
                mysqli_stmt_execute($stmtI);
                $itemsRes = mysqli_stmt_get_result($stmtI);
                ?>
                <div class="bg-white rounded-xl shadow overflow-hidden">
                    <div class="p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-xl font-semibold">Order #<?= (int)$o['order_id'] ?></h2>
                                <?= statusBadge($o['status']) ?>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                Placed: <?= date('d M Y, h:i A', strtotime($o['created_at'])) ?>
                                · Delivery: <span class="capitalize"><?= htmlspecialchars($o['delivery_option']) ?></span>
                            </p>
                        </div>
                        <div class="mt-3 sm:mt-0 space-x-2">
                            <button onclick="toggleDetails('items-<?= $o['order_id'] ?>')" class="px-3 py-2 rounded border hover:bg-gray-50">View Items</button>
                            <button onclick="printOrder('print-<?= $o['order_id'] ?>')" class="px-3 py-2 rounded border hover:bg-gray-50">Print</button>
                        </div>
                    </div>

                    <div id="items-<?= $o['order_id'] ?>" class="hidden border-t">
                        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="font-semibold mb-3">Items</h3>
                                <div class="divide-y">
                                    <?php $sum = 0; while ($it = mysqli_fetch_assoc($itemsRes)): $sum += (float)$it['line_total']; ?>
                                        <div class="py-3 flex items-center justify-between">
                                            <div>
                                                <div class="font-medium"><?= htmlspecialchars($it['name']) ?></div>
                                                <div class="text-xs text-gray-500 capitalize"><?= htmlspecialchars($it['item_type']) ?></div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm text-gray-600">Qty: <?= (int)$it['qty'] ?></div>
                                                <div class="font-semibold">$<?= number_format((float)$it['line_total'], 2) ?></div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                                <div class="mt-3 border-t pt-3 flex justify-between">
                                    <span class="font-medium">Total</span>
                                    <span class="font-semibold">$<?= number_format((float)$o['total_amount'], 2) ?></span>
                                </div>
                            </div>

                            <div>
                                <h3 class="font-semibold mb-3">Delivery</h3>
                                <?php if (in_array($o['delivery_option'], ['pickup','dropoff'])): ?>
                                    <div class="text-gray-700">
                                        <div><?= htmlspecialchars($o['address_line1']) ?></div>
                                        <div><?= htmlspecialchars($o['suburb']) ?>, <?= htmlspecialchars($o['state']) ?> <?= htmlspecialchars($o['postcode']) ?></div>
                                    </div>
                                <?php else: ?>
                                    <p class="text-gray-700">Walk-in at store.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Printable section -->
                    <div id="print-<?= $o['order_id'] ?>" class="hidden print:block">
                        <div class="p-6">
                            <h2 class="text-2xl font-bold mb-1">Spotless Solutions</h2>
                            <p class="text-sm text-gray-600 mb-4">Order Receipt</p>
                            <p><strong>Order:</strong> #<?= (int)$o['order_id'] ?></p>
                            <p><strong>Date:</strong> <?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></p>
                            <p><strong>Status:</strong> <?= htmlspecialchars($o['status']) ?></p>
                            <p><strong>Delivery:</strong> <?= htmlspecialchars($o['delivery_option']) ?></p>
                            <?php if (in_array($o['delivery_option'], ['pickup','dropoff'])): ?>
                                <p><?= htmlspecialchars($o['address_line1']) ?>, <?= htmlspecialchars($o['suburb']) ?>, <?= htmlspecialchars($o['state']) ?> <?= htmlspecialchars($o['postcode']) ?></p>
                            <?php endif; ?>
                            <hr class="my-4">
                            <?php
                            // get items again for print
                            $stmtI2 = mysqli_prepare($conn, "SELECT name, unit_price, qty, line_total FROM order_items WHERE order_id=?");
                            mysqli_stmt_bind_param($stmtI2, "i", $o['order_id']);
                            mysqli_stmt_execute($stmtI2);
                            $printItems = mysqli_stmt_get_result($stmtI2);
                            ?>
                            <table class="w-full text-sm">
                                <thead><tr class="text-left border-b">
                                    <th class="py-2">Item</th>
                                    <th class="py-2">Qty</th>
                                    <th class="py-2">Unit</th>
                                    <th class="py-2 text-right">Total</th>
                                </tr></thead>
                                <tbody>
                                <?php while ($pi = mysqli_fetch_assoc($printItems)): ?>
                                    <tr class="border-b">
                                        <td class="py-2"><?= htmlspecialchars($pi['name']) ?></td>
                                        <td class="py-2"><?= (int)$pi['qty'] ?></td>
                                        <td class="py-2">$<?= number_format((float)$pi['unit_price'], 2) ?></td>
                                        <td class="py-2 text-right">$<?= number_format((float)$pi['line_total'], 2) ?></td>
                                    </tr>
                                <?php endwhile; ?>
                                </tbody>
                            </table>
                            <div class="mt-3 text-right font-semibold">
                                Grand Total: $<?= number_format((float)$o['total_amount'], 2) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleDetails(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.toggle('hidden');
}
function printOrder(id) {
    // Show only the selected print block, trigger print, then hide again
    const blocks = document.querySelectorAll('[id^="print-"]');
    blocks.forEach(b => b.classList.add('hidden'));
    const target = document.getElementById(id);
    if (target) {
        target.classList.remove('hidden');
        window.print();
        target.classList.add('hidden');
    }
}
</script>
<?php include('includes/footer.php'); ?>
