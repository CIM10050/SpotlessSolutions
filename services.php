<?php
include('includes/auth_check.php');
include('includes/config.php');
include('includes/db_connect.php');

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}



/**
 * Add an item to the session cart (service/product unified)
 */
function add_to_cart(array $item) {
    // Key format keeps items unique by type+id
    $key = $item['type'] . '-' . $item['id'];
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] += $item['qty'];
    } else {
        $_SESSION['cart'][$key] = [
            'key'   => $key,
            'type'  => $item['type'],    // 'service'
            'id'    => $item['id'],
            'name'  => $item['name'],
            'price' => (float)$item['price'],
            'qty'   => (int)$item['qty'],
            'image' => $item['image'] ?? '' // optional
        ];
    }
}

$added = false;

// Handle add-to-cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'])) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'customer') {
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }

    $sid = (int)$_POST['service_id'];
    $q   = isset($_POST['qty']) ? max(1, min(99, (int)$_POST['qty'])) : 1;

    $sql  = "SELECT service_id, service_name, price FROM services WHERE is_active = 1 AND service_id = $sid";
    $res  = mysqli_query($conn, $sql);
    if ($res && $row = mysqli_fetch_assoc($res)) {
        add_to_cart([
            'type'  => 'service',
            'id'    => $row['service_id'],
            'name'  => $row['service_name'],
            'price' => $row['price'],
            'qty'   => $q,
            // if you store an image column later, pass it here
            'image' => BASE_URL . '/assets/service-placeholder.jpg'
        ]);
        $added = true;
    }
}

$services = mysqli_query($conn, "SELECT service_id, service_name, description, price FROM services WHERE is_active = 1 ORDER BY service_name ASC");

include('includes/header.php');
?>

<div class="max-w-7xl mx-auto mt-10 p-4">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-blue-700">🧺 Laundry Services</h2>
        <a href="<?= BASE_URL ?>/cart.php" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 transition">View Cart</a>
    </div>

    <?php if ($added): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-4 shadow">
            Service added to cart successfully. <a class="underline" href="<?= BASE_URL ?>/cart.php">Go to cart →</a>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php while ($s = mysqli_fetch_assoc($services)): ?>
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition p-4">
                <div class="h-40 w-full rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center mb-3">
                    <span class="text-5xl">🫧</span>
                </div>
                <h3 class="text-xl font-semibold text-blue-900"><?= htmlspecialchars($s['service_name']) ?></h3>
                <p class="text-sm text-gray-600 my-2"><?= htmlspecialchars($s['description']) ?></p>
                <p class="font-bold text-blue-600 mb-3">$<?= number_format((float)$s['price'], 2) ?></p>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer'): ?>
                    <form method="POST" action="cart.php">
  <input type="hidden" name="action" value="add">
  <input type="hidden" name="type" value="services">
  <input type="hidden" name="id" value="<?= (int)$s['service_id'] ?>">
  <input type="hidden" name="qty" value="1">
  <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">➕ Add to Cart</button>
</form>

                <?php else: ?>
                    <p class="text-gray-400 italic mt-2">Login as customer to add to cart</p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>
