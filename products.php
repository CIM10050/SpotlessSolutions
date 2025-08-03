<?php
include('includes/auth_check.php');
include('includes/db_connect.php');

$products = mysqli_query($conn, "SELECT * FROM products WHERE is_available = 1");

if (!isset($_SESSION['cart_products'])) $_SESSION['cart_products'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $_SESSION['cart_products'][] = $_POST['product_id'];
    header("Location: products.php?added=1");
    exit;
}

include('includes/header.php');
?>

<div class="max-w-7xl mx-auto mt-10 p-4">
    <h2 class="text-3xl font-bold text-green-700 mb-6">🛒 Laundry Products</h2>

    <?php if (isset($_GET['added'])): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4 shadow">Product added to cart!</div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php while ($p = mysqli_fetch_assoc($products)): ?>
            <div class="bg-white rounded-xl shadow-md hover:shadow-xl transform hover:scale-105 transition p-4">
                <img src="uploads/products/<?= $p['image_url'] ?>" alt="<?= $p['product_name'] ?>" class="w-full h-40 object-cover rounded mb-3">
                <h3 class="text-lg font-semibold text-gray-900"><?= $p['product_name'] ?></h3>
                <p class="text-sm text-gray-600 my-2"><?= $p['description'] ?></p>
                <p class="font-bold text-green-600 mb-2">$<?= $p['price'] ?></p>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer'): ?>
                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 transition">➕ Add to Cart</button>
                    </form>
                <?php else: ?>
                    <p class="text-gray-400 italic">Login as customer to buy</p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>
