<?php
include('../includes/auth_check.php');
include('../includes/db_connect.php');

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = intval($_GET['id']);
$product = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM products WHERE product_id = $id"));

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock_quantity']);

    if (!empty($_FILES['image']['name'])) {
        $image = $_FILES['image'];
        $imageName = time() . "_" . basename($image['name']);
        $targetDir = "../uploads/products/";
        $targetPath = $targetDir . $imageName;

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (move_uploaded_file($image['tmp_name'], $targetPath)) {
            $sql = "UPDATE products SET product_name='$name', description='$desc', price='$price', stock_quantity='$stock', image_url='$imageName' WHERE product_id=$id";
        } else {
            $error = "❌ Failed to upload image.";
        }
    } else {
        $sql = "UPDATE products SET product_name='$name', description='$desc', price='$price', stock_quantity='$stock' WHERE product_id=$id";
    }

    if (empty($error) && mysqli_query($conn, $sql)) {
        $success = "✅ Product updated successfully!";
        header("refresh:2;url=product_management.php");
    } elseif (empty($error)) {
        $error = "❌ Error: " . mysqli_error($conn);
    }
}
?>

<?php include('../includes/header.php'); ?>

<div class="max-w-2xl mx-auto mt-10 p-6 bg-white rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Edit Product</h2>

    <?php if ($success): ?>
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">
        <input type="text" name="product_name" value="<?= htmlspecialchars($product['product_name']) ?>" required class="w-full border p-2 rounded">
        <textarea name="description" rows="3" required class="w-full border p-2 rounded"><?= htmlspecialchars($product['description']) ?></textarea>
        <input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required class="w-full border p-2 rounded">
        <input type="number" name="stock_quantity" value="<?= $product['stock_quantity'] ?>" required class="w-full border p-2 rounded">
        <div>
            <label class="block mb-1">Current Image</label>
            <img src="../uploads/products/<?= $product['image_url'] ?>" alt="" class="h-32 mb-2 rounded">
            <input type="file" name="image" class="w-full border p-2 rounded">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">💾 Update Product</button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
