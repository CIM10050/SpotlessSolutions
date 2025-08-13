<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/config.php';

$userRole = $_SESSION['user_role'] ?? null;
$currentPath = strtok($_SERVER['REQUEST_URI'], '?');

// Simple function to highlight active page
function navLink($label, $url, $currentPath) {
    $isActive = (strpos($currentPath, parse_url($url, PHP_URL_PATH)) === 0);
    $classes = "px-3 py-2 rounded-md text-sm font-medium transition";
    $classes .= $isActive ? " text-blue-700 bg-blue-50" : " text-gray-700 hover:text-blue-600 hover:bg-gray-50";
    return "<a href=\"$url\" class=\"$classes\">$label</a>";
}

// Cart count for customers
$cartServicesCount = isset($_SESSION['cart_services']) ? count($_SESSION['cart_services']) : 0;
$cartProductsCount = isset($_SESSION['cart_products']) ? count($_SESSION['cart_products']) : 0;
$cartTotalCount = $cartServicesCount + $cartProductsCount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Spotless Solutions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Optional: Animate on Scroll -->
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.4/dist/aos.css">
    <script defer src="https://unpkg.com/aos@2.3.4/dist/aos.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => { if (window.AOS) AOS.init(); });
    </script>

    <script>
        function toggleMobileNav() {
            document.getElementById('mobileNav').classList.toggle('hidden');
        }
    </script>
</head>
<body class="bg-gray-50 text-gray-800">

<!-- Navbar -->
<nav class="bg-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="<?= BASE_URL ?>/index.php" class="text-2xl font-bold tracking-wide text-blue-700">
                    Spotless <span class="text-gray-900">Solutions</span>
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center space-x-2">
                <?php if ($userRole !== 'admin'): ?>
                    <?= navLink('Home', BASE_URL.'/index.php', $currentPath) ?>
                    <?= navLink('Services', BASE_URL.'/services.php', $currentPath) ?>
                    <?= navLink('Products', BASE_URL.'/products.php', $currentPath) ?>
                    <?= navLink('Book Service', BASE_URL.'/booking.php', $currentPath) ?>
                <?php endif; ?>

                <?php if ($userRole === 'customer'): ?>
                    <a href="<?= BASE_URL ?>/cart.php"
                       class="relative px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50 transition">
                        🛒 Cart
                        <?php if ($cartTotalCount > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-blue-600 text-white text-xs rounded-full px-1.5 py-0.5">
                                <?= $cartTotalCount ?>
                            </span>
                        <?php endif; ?>
                    </a>
                    <?= navLink('My Orders', BASE_URL.'/my_orders.php', $currentPath) ?>
                <?php endif; ?>

                <?php if ($userRole === 'admin'): ?>
                    <?= navLink('Dashboard', BASE_URL.'/admin/dashboard.php', $currentPath) ?>
                    <?= navLink('Orders', BASE_URL.'/admin/orders.php', $currentPath) ?>
                    <?= navLink('Services', BASE_URL.'/admin/service_management.php', $currentPath) ?>
                    <?= navLink('Products', BASE_URL.'/admin/product_management.php', $currentPath) ?>
                    <?= navLink('Settings', BASE_URL.'/admin/settings.php', $currentPath) ?>
                <?php endif; ?>

                <?= navLink('About', BASE_URL.'/about.php', $currentPath) ?>

                <?php if ($userRole): ?>
                    <a href="<?= BASE_URL ?>/logout.php" class="ml-2 px-3 py-2 rounded-md text-sm font-medium text-red-600 hover:bg-red-50 transition">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/login.php" class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50 transition">
                        Login
                    </a>
                    <a href="<?= BASE_URL ?>/register.php" class="px-3 py-2 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition">
                        Register
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden flex items-center">
                <button onclick="toggleMobileNav()" class="p-2 rounded-md text-gray-600 hover:text-blue-700 hover:bg-gray-100">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobileNav" class="hidden md:hidden border-t border-gray-100 bg-white">
        <div class="px-4 py-3 space-y-1">
            <?php if ($userRole !== 'admin'): ?>
                <?= navLink('Home', BASE_URL.'/index.php', $currentPath) ?>
                <?= navLink('Services', BASE_URL.'/services.php', $currentPath) ?>
                <?= navLink('Products', BASE_URL.'/products.php', $currentPath) ?>
                <?= navLink('Book Service', BASE_URL.'/booking.php', $currentPath) ?>
            <?php endif; ?>

            <?php if ($userRole === 'customer'): ?>
                <a href="<?= BASE_URL ?>/cart.php" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50">
                    🛒 Cart <?= $cartTotalCount ? "($cartTotalCount)" : "" ?>
                </a>
                <?= navLink('My Orders', BASE_URL.'/my_orders.php', $currentPath) ?>
            <?php endif; ?>

            <?php if ($userRole === 'admin'): ?>
                <?= navLink('Dashboard', BASE_URL.'/admin/dashboard.php', $currentPath) ?>
                <?= navLink('Orders', BASE_URL.'/admin/orders.php', $currentPath) ?>
                <?= navLink('Services', BASE_URL.'/admin/service_management.php', $currentPath) ?>
                <?= navLink('Products', BASE_URL.'/admin/product_management.php', $currentPath) ?>
                <?= navLink('Settings', BASE_URL.'/admin/settings.php', $currentPath) ?>
            <?php endif; ?>

            <?= navLink('About', BASE_URL.'/about.php', $currentPath) ?>

            <?php if ($userRole): ?>
                <a href="<?= BASE_URL ?>/logout.php" class="block px-3 py-2 rounded-md text-sm font-medium text-red-600 hover:bg-red-50">Logout</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php" class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-blue-600 hover:bg-gray-50">Login</a>
                <a href="<?= BASE_URL ?>/register.php" class="block mt-1 px-3 py-2 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
