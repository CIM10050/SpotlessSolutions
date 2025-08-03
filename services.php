<?php
include('includes/auth_check.php');
include('includes/db_connect.php');

$services = mysqli_query($conn, "SELECT * FROM services WHERE is_active = 1");

if (!isset($_SESSION['cart_services'])) $_SESSION['cart_services'] = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_id'])) {
    $_SESSION['cart_services'][] = $_POST['service_id'];
    header("Location: services.php?added=1");
    exit;
}

include('includes/header.php');
?>

<div class="max-w-7xl mx-auto mt-10 p-4">
    <h2 class="text-3xl font-bold text-blue-700 mb-6">🧺 Laundry Services</h2>

    <?php if (isset($_GET['added'])): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4 shadow">Service added to cart!</div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php while ($s = mysqli_fetch_assoc($services)): ?>
            <div class="bg-white rounded-xl shadow-lg hover:shadow-xl transform hover:scale-105 transition p-4">
                <h3 class="text-xl font-semibold text-blue-900"><?= $s['service_name'] ?></h3>
                <p class="text-sm text-gray-600 my-2"><?= $s['description'] ?></p>
                <p class="font-bold text-blue-600 mb-3">$<?= $s['price'] ?></p>

                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer'): ?>
                    <form method="POST">
                        <input type="hidden" name="service_id" value="<?= $s['service_id'] ?>">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">➕ Add to Cart</button>
                    </form>
                <?php else: ?>
                    <p class="text-gray-400 italic">Login as customer to book</p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include('includes/footer.php'); ?>
