<?php
// admin/orders.php
$requireAdmin = true; // works with your includes/auth_check gate
include('../includes/auth_check.php');
include('../includes/config.php');
include('../includes/db_connect.php');

if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

// CSRF token (simple)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

$filters = [
    'from'   => $_GET['from']   ?? '',
    'to'     => $_GET['to']     ?? '',
    'email'  => $_GET['email']  ?? '',
    'status' => $_GET['status'] ?? ''
];

// Handle status update
$notice = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        $orderId = (int)($_POST['order_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';
        $allowed = ['pending','confirmed','completed','cancelled'];
        if ($orderId > 0 && in_array($newStatus, $allowed, true)) {
            $stmt = mysqli_prepare($conn, "UPDATE orders SET status=? WHERE order_id=?");
            mysqli_stmt_bind_param($stmt, "si", $newStatus, $orderId);
            if (mysqli_stmt_execute($stmt)) {
                $notice = "Status updated for Order #$orderId.";
            } else {
                $error = "DB error updating status: " . mysqli_error($conn);
            }
        } else {
            $error = "Invalid input for status update.";
        }
    }
}

// Build query with filters
$params = [];
$wheres = ["1=1"];
$types  = "";

if ($filters['from'] !== '') {
    $wheres[] = "DATE(o.created_at) >= ?";
    $params[] = $filters['from'];
    $types   .= "s";
}
if ($filters['to'] !== '') {
    $wheres[] = "DATE(o.created_at) <= ?";
    $params[] = $filters['to'];
    $types   .= "s";
}
if ($filters['email'] !== '') {
    $wheres[] = "u.email LIKE ?";
    $params[] = "%".$filters['email']."%";
    $types   .= "s";
}
if ($filters['status'] !== '') {
    $wheres[] = "o.status = ?";
    $params[] = $filters['status'];
    $types   .= "s";
}

// IMPORTANT: alias real delivery column to one name the UI uses
$sql = "SELECT 
            o.order_id, 
            o.user_id, 
            u.full_name, 
            u.email,
            COALESCE(o.delivery_type, o.delivery_option) AS delivery_option,
            o.total_amount, 
            o.status, 
            o.created_at
        FROM orders o
        JOIN users u ON u.user_id = o.user_id
        WHERE " . implode(" AND ", $wheres) . "
        ORDER BY o.order_id DESC";

$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$orders = mysqli_stmt_get_result($stmt);

function statusBadgeAdmin($s) {
    $map = [
        'pending'   => 'bg-yellow-100 text-yellow-800',
        'confirmed' => 'bg-blue-100 text-blue-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
    $cls = $map[$s] ?? 'bg-gray-100 text-gray-700';
    return "<span class=\"px-2 py-1 rounded text-xs font-medium $cls capitalize\">$s</span>";
}

include('../includes/header.php');
?>
<div class="max-w-7xl mx-auto p-4 mt-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold">Orders (Admin)</h1>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="px-4 py-2 rounded border hover:bg-gray-50">Back to Dashboard</a>
    </div>

    <?php if ($notice): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 p-3 rounded mb-4"><?= htmlspecialchars($notice) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <form class="bg-white rounded-xl shadow p-4 mb-6 grid grid-cols-1 md:grid-cols-5 gap-4" method="GET">
        <div>
            <label class="block text-sm font-medium mb-1">From</label>
            <input type="date" name="from" value="<?= htmlspecialchars($filters['from']) ?>" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">To</label>
            <input type="date" name="to" value="<?= htmlspecialchars($filters['to']) ?>" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">User Email</label>
            <input type="text" name="email" placeholder="contains…" value="<?= htmlspecialchars($filters['email']) ?>" class="w-full border rounded p-2">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <select name="status" class="w-full border rounded p-2">
                <option value="">Any</option>
                <?php foreach (['pending','confirmed','completed','cancelled'] as $st): ?>
                    <option value="<?= $st ?>" <?= $filters['status']===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex items-end">
            <button class="w-full px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Filter</button>
        </div>
    </form>

    <!-- Orders table -->
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left px-4 py-3">Order #</th>
                    <th class="text-left px-4 py-3">Customer</th>
                    <th class="text-left px-4 py-3">Email</th>
                    <th class="text-left px-4 py-3">Delivery</th>
                    <th class="text-left px-4 py-3">Total</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Date</th>
                    <th class="text-right px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                <?php if (mysqli_num_rows($orders) === 0): ?>
                    <tr><td colspan="8" class="px-4 py-6 text-center text-gray-500">No orders found.</td></tr>
                <?php else: ?>
                    <?php while ($o = mysqli_fetch_assoc($orders)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium">#<?= (int)$o['order_id'] ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($o['full_name']) ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($o['email']) ?></td>
                            <td class="px-4 py-3 capitalize"><?= htmlspecialchars($o['delivery_option']) ?></td>
                            <td class="px-4 py-3 font-semibold">$<?= number_format((float)$o['total_amount'], 2) ?></td>
                            <td class="px-4 py-3"><?= statusBadgeAdmin($o['status']) ?></td>
                            <td class="px-4 py-3">
                                <?php
                                  $ts = $o['created_at'] ?? null;
                                  echo $ts ? date('d M Y, h:i A', strtotime($ts)) : '-';
                                ?>
                            </td>
                            <td class="px-4 py-3 text-right space-x-2">
                                <button class="px-3 py-1 rounded border hover:bg-gray-100" onclick="openItemsModal(<?= (int)$o['order_id'] ?>)">Items</button>
                                <button class="px-3 py-1 rounded border hover:bg-gray-100" onclick="openStatusModal(<?= (int)$o['order_id'] ?>, '<?= htmlspecialchars($o['status']) ?>')">Change Status</button>
                                <button class="px-3 py-1 rounded border hover:bg-gray-100" onclick="printAdmin(<?= (int)$o['order_id'] ?>)">Print</button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Items Modal -->
<div id="itemsModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4">
  <div class="bg-white w-full max-w-2xl rounded-xl shadow-lg p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold">Order Items</h3>
      <button onclick="closeItemsModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
    </div>
    <div id="itemsBody" class="space-y-3"></div>
  </div>
</div>

<!-- Status Modal -->
<div id="statusModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4">
  <div class="bg-white w-full max-w-md rounded-xl shadow-lg p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-xl font-semibold">Update Status</h3>
      <button onclick="closeStatusModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
    </div>
    <form method="POST" class="space-y-4">
      <input type="hidden" name="csrf" value="<?= $_SESSION['csrf_token'] ?>">
      <input type="hidden" name="order_id" id="status_order_id">
      <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <select name="status" id="status_select" class="w-full border rounded p-2">
          <?php foreach (['pending','confirmed','completed','cancelled'] as $st): ?>
            <option value="<?= $st ?>"><?= ucfirst($st) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="flex justify-end gap-2">
        <button type="button" onclick="closeStatusModal()" class="px-4 py-2 rounded border hover:bg-gray-50">Cancel</button>
        <button type="submit" name="update_status" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700">Update</button>
      </div>
    </form>
  </div>
</div>

<!-- Admin print container -->
<div id="adminPrint" class="hidden print:block p-6"></div>

<script>
// Items modal (AJAX fetch)
function openItemsModal(orderId) {
    fetch('order_items_api.php?order_id=' + orderId)
      .then(r => r.json())
      .then(data => {
          const body = document.getElementById('itemsBody');
          if (!body) return;
          if (!data || !Array.isArray(data.items) || data.items.length === 0) {
              body.innerHTML = '<p class="text-gray-600">No items found.</p>';
          } else {
              let html = '<div class="divide-y">';
              data.items.forEach(it => {
                  html += `
                    <div class="py-3 flex items-center justify-between">
                        <div>
                            <div class="font-medium">${escapeHtml(it.name)}</div>
                            <div class="text-xs text-gray-500 capitalize">${escapeHtml(it.item_type)}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-600">Qty: ${it.qty}</div>
                            <div class="font-semibold">$${Number(it.line_total).toFixed(2)}</div>
                        </div>
                    </div>`;
              });
              html += '</div>';
              body.innerHTML = html;
          }
          document.getElementById('itemsModal').classList.remove('hidden');
          document.getElementById('itemsModal').classList.add('flex');
      })
      .catch(() => {
          const body = document.getElementById('itemsBody');
          body.innerHTML = '<p class="text-red-600">Failed to load items.</p>';
          document.getElementById('itemsModal').classList.remove('hidden');
          document.getElementById('itemsModal').classList.add('flex');
      });
}
function closeItemsModal() {
    const m = document.getElementById('itemsModal');
    m.classList.add('hidden'); m.classList.remove('flex');
    document.getElementById('itemsBody').innerHTML = '';
}

// Status modal
function openStatusModal(orderId, current) {
    document.getElementById('status_order_id').value = orderId;
    const sel = document.getElementById('status_select');
    [...sel.options].forEach(o => o.selected = (o.value === current));
    const m = document.getElementById('statusModal');
    m.classList.remove('hidden'); m.classList.add('flex');
}
function closeStatusModal() {
    const m = document.getElementById('statusModal');
    m.classList.add('hidden'); m.classList.remove('flex');
}

// Print admin (fetch details then render)
function printAdmin(orderId) {
    Promise.all([
        fetch('order_items_api.php?order_id=' + orderId).then(r => r.json()),
        fetch('order_header_api.php?order_id=' + orderId).then(r => r.json()),
    ]).then(([items, header]) => {
        const box = document.getElementById('adminPrint');
        const h = header || {};
        const list = (items && items.items) ? items.items : [];
        let rows = '';
        list.forEach(it => {
            rows += `<tr class="border-b">
                <td class="py-2">${escapeHtml(it.name)}</td>
                <td class="py-2">${it.qty}</td>
                <td class="py-2">$${Number(it.unit_price).toFixed(2)}</td>
                <td class="py-2 text-right">$${Number(it.line_total).toFixed(2)}</td>
            </tr>`;
        });
        box.innerHTML = `
            <h2 class="text-2xl font-bold mb-1">Spotless Solutions</h2>
            <p class="text-sm text-gray-600 mb-4">Order Receipt (Admin)</p>
            <p><strong>Order:</strong> #${h.order_id ?? orderId}</p>
            <p><strong>Customer:</strong> ${escapeHtml(h.full_name ?? '')} (${escapeHtml(h.email ?? '')})</p>
            <p><strong>Date:</strong> ${escapeHtml(h.created_at ?? '')}</p>
            <p><strong>Status:</strong> ${escapeHtml(h.status ?? '')}</p>
            <p><strong>Delivery:</strong> ${escapeHtml((h.delivery_option ?? h.delivery_type) ?? '')}</p>
            ${h.address_line1 ? `<p>${escapeHtml(h.address_line1)}, ${escapeHtml(h.suburb)}, ${escapeHtml(h.state)} ${escapeHtml(h.postcode)}</p>` : ''}
            <hr class="my-4">
            <table class="w-full text-sm">
                <thead><tr class="text-left border-b">
                    <th class="py-2">Item</th>
                    <th class="py-2">Qty</th>
                    <th class="py-2">Unit</th>
                    <th class="py-2 text-right">Total</th>
                </tr></thead>
                <tbody>${rows}</tbody>
            </table>
            <div class="mt-3 text-right font-semibold">Grand Total: $${Number(h.total_amount ?? 0).toFixed(2)}</div>
        `;
        window.print();
    }).catch(() => alert('Failed to load order for printing.'));
}

// tiny helper
function escapeHtml(str) {
    if (typeof str !== 'string') return str;
    return str.replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
}
</script>
<?php include('../includes/footer.php'); ?>
