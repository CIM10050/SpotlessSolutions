<?php
require_once('../includes/auth_check.php');
$requireAdmin = true; // Restrict access to admin only
require_once('../includes/db_connect.php');
include('../includes/header.php');
?>

<div class="max-w-6xl mx-auto mt-10 p-6 bg-white shadow rounded-lg">
    <h1 class="text-3xl font-bold text-blue-700 mb-6">Admin Dashboard</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <a href="service_management.php" class="p-5 bg-blue-100 hover:bg-blue-200 rounded shadow hover:shadow-md transition">
            <h2 class="text-xl font-semibold text-blue-800">🔧 Manage Services</h2>
            <p class="text-sm text-gray-700">Add, edit, or delete laundry services.</p>
        </a>

        <a href="product_management.php" class="p-5 bg-green-100 hover:bg-green-200 rounded shadow hover:shadow-md transition">
            <h2 class="text-xl font-semibold text-green-800">🧴 Manage Products</h2>
            <p class="text-sm text-gray-700">Maintain laundry care product listings.</p>
        </a>

        <a href="booking_management.php" class="p-5 bg-yellow-100 hover:bg-yellow-200 rounded shadow hover:shadow-md transition">
            <h2 class="text-xl font-semibold text-yellow-800">📅 Manage Bookings</h2>
            <p class="text-sm text-gray-700">View and handle all bookings.</p>
        </a>
    </div>

    <div class="mt-8 p-4 border-t text-gray-600">
        Logged in as: <strong><?= $_SESSION['user_name'] ?? 'Admin' ?></strong>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
