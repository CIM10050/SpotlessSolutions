<?php
include('../includes/auth_check.php');
include('../includes/db_connect.php');

// Restrict access to admin only
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['service_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);

    // Handle image upload
    $image = $_FILES['image'];
    $imageName = time() . "_" . basename($image['name']);
    $targetDir = "../uploads/services/";
    $targetPath = $targetDir . $imageName;

    // ✅ Create upload folder if not exists
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    // ✅ Upload and insert into DB
    if ($image['size'] > 0 && move_uploaded_file($image['tmp_name'], $targetPath)) {
        $query = "INSERT INTO services (service_name, description, price, image) 
                  VALUES ('$name', '$desc', '$price', '$imageName')";
        if (mysqli_query($conn, $query)) {
            $success = "✅ Service added successfully!";
            header("refresh:2;url=service_management.php");
        } else {
            $error = "❌ Database error: " . mysqli_error($conn);
        }
    } else {
        $error = "❌ Failed to upload image.";
    }
}
?>

<?php include('../includes/header.php'); ?>

<div class="max-w-2xl mx-auto mt-10 p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-4">➕ Add New Service</h2>

    <?php if ($success): ?>
        <div class="bg-green-100 text-green-800 p-3 mb-4 rounded shadow"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-800 p-3 mb-4 rounded shadow"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Service Name</label>
            <input type="text" name="service_name" required class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label class="block text-sm font-medium">Description</label>
            <textarea name="description" required rows="3" class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400"></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium">Price ($)</label>
            <input type="number" name="price" step="0.01" min="0" required class="w-full border p-2 rounded focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>

        <div>
            <label class="block text-sm font-medium">Upload Image</label>
            <input type="file" name="image" accept="image/*" required class="w-full border p-2 rounded bg-white">
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition w-full">Add Service</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
