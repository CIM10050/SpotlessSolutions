<?php
include('../includes/auth_check.php');
include('../includes/db_connect.php');

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = intval($_GET['id'] ?? 0);
$res = mysqli_query($conn, "SELECT image_url FROM products WHERE product_id = $id");
if ($row = mysqli_fetch_assoc($res)) {
    $file = "../uploads/products/" . $row['image_url'];
    if (file_exists($file)) {
        unlink($file);
    }
}
mysqli_query($conn, "DELETE FROM products WHERE product_id = $id");

header("Location: product_management.php");
exit();
