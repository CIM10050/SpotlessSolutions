<?php
include('includes/db_connect.php');
$id = $_GET['id'];
$booking = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM bookings WHERE booking_id = '$id'"));

if (!$booking) {
    die("Invalid booking ID.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Booking Summary</title>
    <style>
        body { font-family: sans-serif; padding: 40px; }
        .card { border: 1px solid #ccc; padding: 20px; border-radius: 10px; max-width: 600px; margin: auto; }
        h2 { color: #1e3a8a; }
        .label { font-weight: bold; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Spotless Solutions – Booking Summary</h2>
    <p><span class="label">Booking ID:</span> <?= $booking['booking_id'] ?></p>
    <p><span class="label">Service Type:</span> <?= ucfirst($booking['service_type']) ?></p>
    <p><span class="label">Date & Time:</span> <?= $booking['scheduled_date'] ?> at <?= $booking['scheduled_time'] ?></p>
    <p><span class="label">Status:</span> <?= ucfirst($booking['status']) ?></p>
    <?php if ($booking['address']): ?>
        <p><span class="label">Address:</span> <?= $booking['address'] ?></p>
    <?php endif; ?>
    <p><span class="label">Instructions:</span> <?= $booking['special_instructions'] ?></p>

    <div class="no-print mt-4">
        <button onclick="window.print()">🖨️ Print</button>
    </div>
</div>

</body>
</html>
