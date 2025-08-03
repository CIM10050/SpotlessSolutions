<?php
require_once('../includes/auth_check.php');
$requireAdmin = true; // Restrict access to admin only
require_once('../includes/db_connect.php');
include('../includes/header.php');

// Fetch live counts
$serviceCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM services"))['total'];
$productCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM products"))['total'];
$bookingCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM bookings"))['total'];
$userCount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users"))['total'];
$total = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings"))[0];
$completed = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE status='completed'"))[0];
$pending = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE status='pending'"))[0];

?>

<div class="max-w-6xl mx-auto mt-10 p-6 bg-white shadow rounded-lg">
    <h1 class="text-3xl font-bold text-blue-700 mb-6">Admin Dashboard</h1>

    <!-- Quick Links -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
        <a href="service_management.php" class="p-5 bg-blue-100 hover:bg-blue-200 rounded shadow hover:shadow-md transition-all transform hover:scale-105 hover:rotate-1 duration-300">
            <h2 class="text-xl font-semibold text-blue-800">🔧 Manage Services</h2>
            <p class="text-sm text-gray-700">Add, edit, or delete laundry services.</p>
        </a>

        <a href="product_management.php" class="p-5 bg-green-100 hover:bg-green-200 rounded shadow hover:shadow-md transition-all transform hover:scale-105 hover:rotate-1 duration-300">
            <h2 class="text-xl font-semibold text-green-800">🧴 Manage Products</h2>
            <p class="text-sm text-gray-700">Maintain laundry care product listings.</p>
        </a>

        <a href="bookings.php" class="p-5 bg-yellow-100 hover:bg-yellow-200 rounded shadow hover:shadow-md transition-all transform hover:scale-105 hover:rotate-1 duration-300">
            <h2 class="text-xl font-semibold text-yellow-800">📅 Manage Bookings</h2>
            <p class="text-sm text-gray-700">View and handle all bookings.</p>
        </a>
    </div>

    <!-- Live Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-white">
        <div class="bg-blue-600 p-6 rounded-xl shadow-md transform hover:scale-105 transition-all duration-300">
            <div class="text-4xl mb-2">🧼</div>
            <h3 class="text-lg">Total Services</h3>
            <p class="text-2xl font-bold"><?= $serviceCount ?></p>
        </div>

        <div class="bg-green-600 p-6 rounded-xl shadow-md transform hover:scale-105 transition-all duration-300">
            <div class="text-4xl mb-2">📦</div>
            <h3 class="text-lg">Total Products</h3>
            <p class="text-2xl font-bold"><?= $productCount ?></p>
        </div>

        <div class="bg-yellow-500 p-6 rounded-xl shadow-md transform hover:scale-105 transition-all duration-300">
            <div class="text-4xl mb-2">📅</div>
            <h3 class="text-lg">Total Bookings</h3>
            <p class="text-2xl font-bold"><?= $bookingCount ?></p>
        </div>

        <div class="bg-purple-600 p-6 rounded-xl shadow-md transform hover:scale-105 transition-all duration-300">
            <div class="text-4xl mb-2">👥</div>
            <h3 class="text-lg">Total Users</h3>
            <p class="text-2xl font-bold"><?= $userCount ?></p>
        </div>
    </div>

    <div class="mt-8 p-4 border-t text-gray-600">
        Logged in as: <strong><?= $_SESSION['user_name'] ?? 'Admin' ?></strong>
    </div>
</div>

<div class="max-w-5xl mx-auto mt-10 grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white shadow-lg rounded-xl p-6 text-center">
        <h3 class="text-lg text-gray-500">Total Bookings</h3>
        <p class="text-3xl font-bold text-blue-700"><?= $total ?></p>
    </div>
    <div class="bg-white shadow-lg rounded-xl p-6 text-center">
        <h3 class="text-lg text-gray-500">Completed</h3>
        <p class="text-3xl font-bold text-green-600"><?= $completed ?></p>
    </div>
    <div class="bg-white shadow-lg rounded-xl p-6 text-center">
        <h3 class="text-lg text-gray-500">Pending</h3>
        <p class="text-3xl font-bold text-yellow-600"><?= $pending ?></p>
    </div>
</div>


<?php include('../includes/footer.php'); ?>
