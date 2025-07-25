<!-- includes/header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Spotless Solutions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- ✅ Correct Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">


<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include_once 'config.php';
?>

<!-- includes/header.php -->
<nav class="bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
        <a href="<?= BASE_URL ?>/index.php" class="text-xl font-bold text-blue-600">Spotless Solutions</a>
        <div class="space-x-4">
            <a href="<?= BASE_URL ?>/index.php" class="text-gray-700 hover:text-blue-500">Home</a>
            <a href="<?= BASE_URL ?>/about.php" class="text-gray-700 hover:text-blue-500">Services</a>
            <a href="<?= BASE_URL ?>/products.php" class="text-gray-700 hover:text-blue-500">Products</a>
            <a href="<?= BASE_URL ?>/booking.php" class="text-gray-700 hover:text-blue-500">Book Service</a>
            <?php if (isset($_SESSION['user_role'])): ?>
                <?php if ($_SESSION['user_role'] == 'admin'): ?>
                    <a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-red-500 font-semibold">Admin Panel</a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/logout.php" class="text-gray-700 hover:text-red-500">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="text-gray-700 hover:text-blue-500">Login</a>
                <a href="<?= BASE_URL ?>/register.php" class="text-gray-700 hover:text-blue-500">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

