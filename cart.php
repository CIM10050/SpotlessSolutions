<?php
// cart.php
// 1) Do NOT output anything before we process POST actions.
if (session_status() === PHP_SESSION_NONE) session_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('includes/db_connect.php');


require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/db_connect.php';
require_once __DIR__ . '/includes/cart_functions.php';

// Only customers can use the cart
if (($_SESSION['user_role'] ?? '') !== 'customer') {
    // no output yet -> safe redirect
    header("Location: login.php");
    exit;
}

// tiny helper: safe redirect (header or JS as fallback)
function safe_redirect($url) {
    if (!headers_sent()) {
        header("Location: $url");
        exit;
    }
    echo "<script>location.href = ".json_encode($url).";</script>";
    exit;
}

// 2) Handle actions BEFORE including header.php (no output yet)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $type   = $_POST['type']   ?? '';   // 'services' or 'products'
    $id     = (int)($_POST['id'] ?? 0);

    if ($action === 'add' && $id > 0) {
        $qty = max(1, (int)($_POST['qty'] ?? 1));
        // normalize type
        $type = ($type === 'services') ? 'services' : 'products';
        cartAdd($type, $id, $qty);
        safe_redirect('cart.php?added=1');
    }

    if ($action === 'set' && $id > 0) {
        $qty = max(0, (int)($_POST['qty'] ?? 1));
        $type = ($type === 'services') ? 'services' : 'products';
        cartSetQty($type, $id, $qty);
        safe_redirect('cart.php?updated=1');
    }

    if ($action === 'remove' && $id > 0) {
        $type = ($type === 'services') ? 'services' : 'products';
        cartRemove($type, $id);
        safe_redirect('cart.php?removed=1');
    }

    if ($action === 'clear') {
        cartClear();
        safe_redirect('cart.php?cleared=1');
    }
}

// 3) Now it’s safe to render
require_once __DIR__ . '/includes/header.php';

// 4) Fetch cart details for display
$cart = cartGetDetails($conn);
?>

<div class="max-w-7xl mx-auto mt-10 p-4">
  <h2 class="text-3xl font-bold text-blue-700 mb-6">🛒 Your Cart</h2>

  <?php if (isset($_GET['added'])): ?>
    <div class="bg-green-100 text-green-700 p-3 rounded mb-4 shadow">Item added!</div>
  <?php elseif (isset($_GET['updated'])): ?>
    <div class="bg-blue-100 text-blue-700 p-3 rounded mb-4 shadow">Quantity updated.</div>
  <?php elseif (isset($_GET['removed'])): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded mb-4 shadow">Item removed.</div>
  <?php elseif (isset($_GET['cleared'])): ?>
    <div class="bg-yellow-100 text-yellow-800 p-3 rounded mb-4 shadow">Cart cleared.</div>
  <?php endif; ?>

  <?php if (empty($cart['services']) && empty($cart['products'])): ?>
    <div class="bg-white p-6 rounded shadow text-center">
      <p class="text-gray-600">Your cart is empty.</p>
      <div class="mt-4 space-x-2">
        <a href="services.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Browse Services</a>
        <a href="products.php" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Browse Products</a>
      </div>
    </div>
  <?php else: ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">

        <!-- Services -->
        <?php if (!empty($cart['services'])): ?>
          <div class="bg-white rounded-xl shadow p-4">
            <h3 class="text-xl font-semibold mb-3">Services</h3>
            <div class="divide-y">
              <?php foreach ($cart['services'] as $item): ?>
                <div class="py-3 flex items-center justify-between">
                  <div>
                    <div class="font-medium"><?= htmlspecialchars($item['name']) ?></div>
                    <div class="text-sm text-gray-500">$<?= number_format($item['unit'], 2) ?> each</div>
                  </div>
                  <div class="flex items-center space-x-3">
                    <form method="POST" class="flex items-center space-x-2">
                      <input type="hidden" name="action" value="set">
                      <input type="hidden" name="type" value="services">
                      <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                      <input type="number" name="qty" value="<?= (int)$item['qty'] ?>" min="1"
                             class="w-20 border rounded px-2 py-1">
                      <button class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Update</button>
                    </form>
                    <form method="POST">
                      <input type="hidden" name="action" value="remove">
                      <input type="hidden" name="type" value="services">
                      <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                      <button class="px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700">Remove</button>
                    </form>
                  </div>
                  <div class="w-24 text-right font-semibold">
                    $<?= number_format($item['line'], 2) ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- Products -->
        <?php if (!empty($cart['products'])): ?>
          <div class="bg-white rounded-xl shadow p-4">
            <h3 class="text-xl font-semibold mb-3">Products</h3>
            <div class="divide-y">
              <?php foreach ($cart['products'] as $item): ?>
                <div class="py-3 flex items-center justify-between">
                  <div>
                    <div class="font-medium"><?= htmlspecialchars($item['name']) ?></div>
                    <div class="text-sm text-gray-500">$<?= number_format($item['unit'], 2) ?> each</div>
                  </div>
                  <div class="flex items-center space-x-3">
                    <form method="POST" class="flex items-center space-x-2">
                      <input type="hidden" name="action" value="set">
                      <input type="hidden" name="type" value="products">
                      <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                      <input type="number" name="qty" value="<?= (int)$item['qty'] ?>" min="1"
                             class="w-20 border rounded px-2 py-1">
                      <button class="px-3 py-1 rounded bg-blue-600 text-white hover:bg-blue-700">Update</button>
                    </form>
                    <form method="POST">
                      <input type="hidden" name="action" value="remove">
                      <input type="hidden" name="type" value="products">
                      <input type="hidden" name="id" value="<?= (int)$item['id'] ?>">
                      <button class="px-3 py-1 rounded bg-red-600 text-white hover:bg-red-700">Remove</button>
                    </form>
                  </div>
                  <div class="w-24 text-right font-semibold">
                    $<?= number_format($item['line'], 2) ?>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>

      </div>

      <!-- Summary -->
      <div class="bg-white rounded-xl shadow p-4 h-fit">
        <h3 class="text-xl font-semibold mb-4">Order Summary</h3>
        <div class="flex justify-between mb-2">
          <span>Subtotal</span>
          <span class="font-semibold">$<?= number_format($cart['total'], 2) ?></span>
        </div>
        <div class="text-sm text-gray-500 mb-4">* Delivery/handling calculated at checkout if applicable.</div>

        <div class="space-y-2">
          <a href="checkout.php"
             class="block text-center w-full bg-green-600 text-white py-2 rounded hover:bg-green-700 transition">
            Proceed to Checkout
          </a>
          <form method="POST">
            <input type="hidden" name="action" value="clear">
            <button class="w-full bg-gray-100 text-gray-700 py-2 rounded hover:bg-gray-200 transition">
              Clear Cart
            </button>
          </form>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
