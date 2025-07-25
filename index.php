<?php
session_start();
include 'includes/header.php';
?>
<?php include_once 'includes/config.php'; ?>

<section class="bg-gradient-to-r from-blue-50 to-blue-100 py-20">
    <div class="max-w-6xl mx-auto px-4 text-center">
        <h1 class="text-4xl md:text-5xl font-bold text-blue-700 mb-4">
            Welcome to Spotless Solutions
        </h1>
        <p class="text-lg md:text-xl text-gray-700 mb-8">
            Professional laundry & dry cleaning service at your doorstep – Book online, stay fresh!
        </p>
        <div class="space-x-4">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Login</a>
                <a href="register.php" class="bg-gray-100 border border-blue-600 text-blue-600 px-6 py-2 rounded hover:bg-gray-200">Register</a>
            <?php else: ?>
                <a href="booking.php" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Book Now</a>
                <a href="logout.php" class="bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="py-16 bg-white">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-10">Our Services</h2>
        <div class="grid md:grid-cols-3 gap-6">
            <div class="bg-blue-50 p-6 rounded shadow hover:shadow-lg transition">
                <h3 class="text-lg font-semibold mb-2 text-blue-700">Dry Cleaning</h3>
                <p class="text-sm text-gray-600">Perfect for suits, dresses, and delicate fabrics.</p>
            </div>
            <div class="bg-blue-50 p-6 rounded shadow hover:shadow-lg transition">
                <h3 class="text-lg font-semibold mb-2 text-blue-700">Wash & Fold</h3>
                <p class="text-sm text-gray-600">Convenient pickup and delivery of everyday laundry.</p>
            </div>
            <div class="bg-blue-50 p-6 rounded shadow hover:shadow-lg transition">
                <h3 class="text-lg font-semibold mb-2 text-blue-700">Ironing Services</h3>
                <p class="text-sm text-gray-600">Get wrinkle-free clothes, ready to wear.</p>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
