<?php
include('../includes/auth_check.php');
include('../includes/db_connect.php');

// Access control
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$service_id = intval($_GET['id'] ?? 0);

if ($service_id > 0) {
    // Optional: Delete image file if needed
    $result = mysqli_query($conn, "SELECT image FROM services WHERE service_id = $service_id");
    if ($row = mysqli_fetch_assoc($result)) {
        $imgPath = '../uploads/services/' . $row['image'];
        if (file_exists($imgPath)) {
            unlink($imgPath); // delete image
        }
    }

    // Delete record
    mysqli_query($conn, "DELETE FROM services WHERE service_id = $service_id");
}

header("Location: service_management.php");
exit();
