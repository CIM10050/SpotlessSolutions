<?php
include('../includes/auth_check.php');
include('../includes/db_connect.php');

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock_quantity']);

    $image = $_FILES['image'];
    $imageName = time() . "_" . basename($image['name']);
    $targetDir = "../uploads/products/";
    $targetPath = $targetDir . $imageName;

    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if ($image['size'] > 0 && move_uploaded_file($image['tmp_name'], $targetPath)) {
        $query = "INSERT INTO products (product_name, description, price, stock_quantity, image_url) 
                  VALUES ('$name', '$desc', '$price', '$stock', '$imageName')";
        if (mysqli_query($conn, $query)) {
            $success = "✅ Product added successfully!";
            header("refresh:2;url=product_management.php");
        } else {
            $error = "❌ Database error: " . mysqli_error($conn);
        }
    } else {
        $error = "❌ Image upload failed.";
    }
}
?>

<?php include('../includes/header.php'); ?>

<div class="max-w-2xl mx-auto mt-10 p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Add New Product</h2>

    <?php if ($success): ?>
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="text" name="product_name" placeholder="Product Name" required class="w-full border p-2 rounded">
        <textarea name="description" placeholder="Description" rows="3" required class="w-full border p-2 rounded"></textarea>
        <input type="number" name="price" step="0.01" min="0" placeholder="Price ($)" required class="w-full border p-2 rounded">
        <input type="number" name="stock_quantity" min="0" placeholder="Stock Quantity" required class="w-full border p-2 rounded">
        <input type="file" name="image" accept="image/*" required class="w-full border p-2 rounded">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">➕ Add Product</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
