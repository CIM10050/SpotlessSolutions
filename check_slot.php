<?php
include('includes/db_connect.php');
$date = $_GET['date'];
$time = $_GET['time'];

$query = "SELECT * FROM bookings WHERE scheduled_date = '$date' AND scheduled_time = '$time'";
$result = mysqli_query($conn, $query);
echo (mysqli_num_rows($result) > 0) ? 'unavailable' : 'available';
