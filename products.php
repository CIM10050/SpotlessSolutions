<?php
include('includes/auth_check.php');
include('includes/config.php');
include('includes/db_connect.php');

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/**
 * Add an item to the unified cart
 */
function add_to_cart(array $item) {
    $key = $item['type'] . '-' . $item['id'];
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] += $item['qty'];
    } else {
        $_SESSION['cart'][$key] = [
            'key'   => $key,
            'type'  => $item['type'],    // 'product'
            'id'    => $item['id'],
            'name'  => $item['name'],
            'price' => (float)$item['price'],
            'qty'   => (int)$item['qty'],
            'image' => $item['image'] ?? ''
        ];
    }
}

$added = false;

// Handle add-to-cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'customer') {
        header("Location: " . BASE_URL . "/login.php");
        exit;
    }

    $pid = (int)$_POST['product_id'];
    $q   = isset($_POST['qty']) ? max(1, min(99, (int)$_POST['qty'])) : 1;

    $sql = "SELECT product_id, product_name, price, image_url FROM products WHERE is_available = 1 AND product_id = $pid";
    $res = mysqli_query($conn, $sql);
    if ($res && $row = mysqli_fetch_assoc($res)) {
        $image = !empty($row['image_url']) ? (strpos($row['image_url'], 'http') === 0 ? $row['image_url'] : BASE_URL . '/uploads/products/' . $row['image_url']) : BASE_URL . '/assets/product-placeholder.jpg';
        add_to_cart([
            'type'  => 'product',
            'id'    => $row['product_id'],
            'name'  => $row['product_name'],
            'price' => $row['price'],
            'qty'   => $q,
            'image' => $image
        ]);
        $added = true;
    }
}

$products = mysqli_query($conn, "SELECT product_id, product_name, description, price, image_url FROM products WHERE is_available = 1 ORDER BY product_name ASC");

include('includes/header.php');
?>

<div class="max-w-7xl mx-auto mt-10 p-4">
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-3xl font-bold text-green-700">🛒 Laundry Products</h2>
        <a href="<?= BASE_URL ?>/cart.php" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 transition">View Cart</a>
    </div>

    <?php if ($added): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 p-3 rounded mb-4 shadow">
            Product added to cart successfully. <a class="underline" href="<?= BASE_URL ?>/cart.php">Go to cart →</a>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php while ($p = mysqli_fetch_assoc($products)): 
            $img = !empty($p['image_url']) ? (strpos($p['image_url'], 'http') === 0 ? $p['image_url'] : 'uploads/products/' . $p['image_url']) : 'assets/product-placeholder.jpg';
        ?>
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition p-4">
                <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlspecialchars($p['product_name']) ?>" class="w-full h-40 object-cover rounded mb-3">
                <h3 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($p['product_name']) ?></h3>
                <p class="text-sm text-gray-600 my-2"><?= htmlspecialchars($p['description']) ?></p>
                <p class="font-bold text-green-600 mb-2">$<?= number_format((float)$p['price'], 2) ?></p>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer'): ?>
                   <form method="POST" action="cart.php">
  <input type="hidden" name="action" value="add">
  <input type="hidden" name="type" value="products">
  <input type="hidden" name="id" value="<?= (int)$p['product_id'] ?>">
  <input type="number" name="qty" value="1" min="1" class="w-20 border rounded px-2 py-1 mr-2">
  <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">➕ Add to Cart</button>
</form>
                <?php else: ?>
                    <p class="text-gray-400 italic mt-2">Login as customer to add to cart</p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>
