<?php
include('../includes/auth_check.php');
include('../includes/db_connect.php');

// Only admin access
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit();
}

// Optional: Page-level access control
if (isset($requireAdmin) && $requireAdmin === true) {
    if ($_SESSION['user_role'] !== 'admin') {
        header("Location: /index.php");
        exit();
    }
}

// Update 'id' if your primary key is named differently
$services = mysqli_query($conn, "SELECT * FROM services ORDER BY service_id DESC");

?>

<?php include('../includes/header.php'); ?>

<div class="max-w-7xl mx-auto mt-6 p-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Service Management</h2>
        <a href="add_service.php" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">+ Add New Service</a>
    </div>

    <?php if (mysqli_num_rows($services) > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <?php while ($row = mysqli_fetch_assoc($services)): ?>
                <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition transform hover:scale-105 duration-300">
                    <img src="../uploads/services/<?= $row['image'] ?>" alt="<?= $row['service_name'] ?>" class="w-full h-40 object-cover">
                    <div class="p-4">
                        <h3 class="text-lg font-semibold"><?= $row['service_name'] ?></h3>
                        <p class="text-sm text-gray-600 mb-2"><?= $row['description'] ?></p>
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-blue-600">$<?= $row['price'] ?></span>
                            <div class="space-x-2">
                              <a href="edit_service.php?id=<?= $row['service_id'] ?>" class="text-yellow-500 hover:text-yellow-700">✏️</a>
<a href="delete_service.php?id=<?= $row['service_id'] ?>" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure to delete this service?')">🗑️</a>

                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-500">No services available.</p>
    <?php endif; ?>
</div>

<?php include('../includes/footer.php'); ?>
