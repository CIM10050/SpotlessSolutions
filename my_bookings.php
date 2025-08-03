<?php
include('includes/auth_check.php');
include('includes/db_connect.php');

$userId = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM bookings WHERE user_id = '$userId' ORDER BY scheduled_date DESC");

include('includes/header.php');
?>

<div class="max-w-4xl mx-auto mt-8 p-6 bg-white shadow rounded">
    <h2 class="text-xl font-bold mb-4">🧾 My Bookings</h2>
    <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <div class="border p-4 mb-4 rounded shadow-sm">
            <p><strong>Service:</strong> <?= ucfirst($row['service_type']) ?></p>
            <p><strong>Date:</strong> <?= $row['scheduled_date'] ?> | <strong>Time:</strong> <?= $row['scheduled_time'] ?></p>
            <p><strong>Status:</strong> <?= ucfirst($row['status']) ?></p>
            <p><strong>Notes:</strong> <?= $row['special_instructions'] ?></p>
            <?php if ($row['address']): ?>
                <p><strong>Address:</strong> <?= $row['address'] ?></p>
            <?php endif; ?>
            <a href="print_booking.php?id=<?= $row['booking_id'] ?>" target="_blank" class="text-blue-600 underline mt-2 inline-block">🖨️ Print Summary</a>
        </div>
    <?php endwhile; ?>
</div>

<?php include('includes/footer.php'); ?>
