<?php
include('../includes/auth_check.php');
include('../includes/db_connect.php');

// Access control
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$service_id = intval($_GET['id'] ?? 0);
$success = $error = "";

// Fetch existing data
$result = mysqli_query($conn, "SELECT * FROM services WHERE service_id = $service_id");
if (!$result || mysqli_num_rows($result) === 0) {
    die("Service not found.");
}
$service = mysqli_fetch_assoc($result);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['service_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);

    // If new image uploaded
    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image'];
        $imageName = time() . "_" . basename($image['name']);
        $targetDir = "../uploads/services/";
        $targetPath = $targetDir . $imageName;

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (move_uploaded_file($image['tmp_name'], $targetPath)) {
            $update = "UPDATE services SET service_name='$name', description='$desc', price='$price', image='$imageName' WHERE service_id=$service_id";
        } else {
            $error = "❌ Failed to upload image.";
        }
    } else {
        $update = "UPDATE services SET service_name='$name', description='$desc', price='$price' WHERE service_id=$service_id";
    }

    if (empty($error) && mysqli_query($conn, $update)) {
        $success = "✅ Service updated successfully!";
        header("refresh:2;url=service_management.php");
    } else if (empty($error)) {
        $error = "❌ Database error: " . mysqli_error($conn);
    }
}
?>

<?php include('../includes/header.php'); ?>

<div class="max-w-2xl mx-auto mt-10 p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Edit Service</h2>

    <?php if ($success): ?>
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Service Name</label>
            <input type="text" name="service_name" value="<?= htmlspecialchars($service['service_name']) ?>" required class="w-full border p-2 rounded">
        </div>

        <div>
            <label class="block text-sm font-medium">Description</label>
            <textarea name="description" rows="3" required class="w-full border p-2 rounded"><?= htmlspecialchars($service['description']) ?></textarea>
        </div>

        <div>
            <label class="block text-sm font-medium">Price ($)</label>
            <input type="number" name="price" value="<?= htmlspecialchars($service['price']) ?>" step="0.01" min="0" required class="w-full border p-2 rounded">
        </div>

        <div>
            <label class="block text-sm font-medium">Current Image</label><br>
            <img src="../uploads/services/<?= htmlspecialchars($service['image']) ?>" alt="Service Image" class="h-32 mb-2 rounded">
            <input type="file" name="image" accept="image/*" class="w-full border p-2 rounded bg-white">
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">💾 Update Service</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
