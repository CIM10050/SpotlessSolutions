<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Spotless Solutions</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Animate on Scroll -->
  
  <script>AOS.init();</script>
</head>
<body class="bg-gray-50 text-gray-800">

<?php
if (session_status() == PHP_SESSION_NONE) session_start();
include_once 'config.php';

$userRole = $_SESSION['user_role'] ?? null;
?>

<!-- ✅ Header Navigation -->
<nav class="bg-white shadow-md sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
    <a href="<?= BASE_URL ?>/index.php" class="text-2xl font-bold text-blue-700 tracking-wide">Spotless Solutions</a>

    <div class="hidden md:flex space-x-6 items-center">
      <?php if ($userRole !== 'admin'): ?>
        <a href="<?= BASE_URL ?>/index.php" class="hover:text-blue-600">Home</a>
        <a href="<?= BASE_URL ?>/services.php" class="hover:text-blue-600">Services</a>
     
        <a href="<?= BASE_URL ?>/products.php" class="hover:text-blue-600">Products</a>
        <a href="<?= BASE_URL ?>/booking.php" class="hover:text-blue-600">Book Service</a>
      <?php endif; ?>

      <?php if ($userRole == 'customer'): ?>
        <a href="<?= BASE_URL ?>/cart.php" class="hover:text-blue-600">🛒 Cart</a>
      <?php endif; ?>

      <?php if ($userRole == 'admin'): ?>
        <a href="<?= BASE_URL ?>/admin/dashboard.php" class="text-red-600 font-semibold">Admin Dashboard</a>
        <a href="<?= BASE_URL ?>/admin/bookings.php" class="hover:text-blue-600">Bookings</a>
        <a href="<?= BASE_URL ?>/admin/settings.php" class="hover:text-blue-600">Settings</a>
      <?php endif; ?>
     <a href="<?= BASE_URL ?>/about.php" class="hover:text-blue-600">About us</a>
      <?php if ($userRole): ?>
        <a href="<?= BASE_URL ?>/logout.php" class="hover:text-red-600 font-medium">Logout</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php" class="hover:text-blue-600">Login</a>
        <a href="<?= BASE_URL ?>/register.php" class="hover:text-blue-600">Register</a>
      <?php endif; ?>
         
    </div>

    <!-- Mobile Menu Placeholder (optional dropdown) -->
    <div class="md:hidden">
      <!-- You can add hamburger menu here if needed -->
    </div>
  </div>
</nav>
