<?php
include('../includes/auth_check.php');
include('../includes/db_connect.php');

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$filterUser = $_GET['user'] ?? '';
$filterFrom = $_GET['from'] ?? '';
$filterTo = $_GET['to'] ?? '';
$success = "";

// 🔁 Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = intval($_POST['booking_id']);
    $newStatus = $_POST['status'];

    mysqli_query($conn, "UPDATE bookings SET status='$newStatus' WHERE booking_id=$id");
    $success = "✅ Status updated for Booking #$id!";
}

// 📦 Load user list
$userList = mysqli_query($conn, "SELECT DISTINCT u.user_id, u.full_name FROM bookings b JOIN users u ON b.user_id = u.user_id");

// 📊 Load bookings with optional filters
$where = "WHERE 1";
if ($filterUser) $where .= " AND b.user_id = '$filterUser'";
if ($filterFrom) $where .= " AND b.scheduled_date >= '$filterFrom'";
if ($filterTo) $where .= " AND b.scheduled_date <= '$filterTo'";

$query = "SELECT b.*, u.full_name FROM bookings b JOIN users u ON b.user_id = u.user_id $where ORDER BY scheduled_date DESC";
$bookings = mysqli_query($conn, $query);
?>

<?php include('../includes/header.php'); ?>

<div class="max-w-7xl mx-auto mt-8 p-6 bg-white rounded-xl shadow">
    <h2 class="text-2xl font-bold mb-4 text-blue-700">📦 Manage Bookings</h2>

    <?php if ($success): ?>
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= $success ?></div>
    <?php endif; ?>

    <!-- 🔍 Filters -->
    <form method="GET" class="flex flex-wrap gap-4 mb-6">
        <select name="user" class="border p-2 rounded">
            <option value="">All Users</option>
            <?php while ($u = mysqli_fetch_assoc($userList)): ?>
                <option value="<?= $u['user_id'] ?>" <?= ($u['user_id'] == $filterUser ? 'selected' : '') ?>>
                    <?= $u['full_name'] ?>
                </option>
            <?php endwhile; ?>
        </select>
        <input type="date" name="from" value="<?= $filterFrom ?>" class="border p-2 rounded" placeholder="From date">
        <input type="date" name="to" value="<?= $filterTo ?>" class="border p-2 rounded" placeholder="To date">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
        <a href="bookings.php" class="text-red-600 px-3 py-2">Reset</a>
    </form>

    <!-- 📋 Booking Table -->
    <div class="overflow-auto">
        <table class="w-full table-auto border-collapse text-sm">
            <thead>
                <tr class="bg-blue-100 text-blue-800">
                    <th class="p-2 border">#</th>
                    <th class="p-2 border">User</th>
                    <th class="p-2 border">Service</th>
                    <th class="p-2 border">Date</th>
                    <th class="p-2 border">Time</th>
                    <th class="p-2 border">Status</th>
                    <th class="p-2 border">Update</th>
                    <th class="p-2 border">Print</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($bookings)): ?>
                    <tr class="text-center border-t hover:bg-gray-50">
                        <td class="p-2"><?= $row['booking_id'] ?></td>
                        <td class="p-2"><?= htmlspecialchars($row['full_name']) ?></td>
                        <td class="p-2"><?= ucfirst($row['service_type']) ?></td>
                        <td class="p-2"><?= $row['scheduled_date'] ?></td>
                        <td class="p-2"><?= $row['scheduled_time'] ?></td>
                        <td class="p-2">
                            <span class="text-<?= 
                                $row['status'] === 'completed' ? 'green' :
                                ($row['status'] === 'pending' ? 'yellow' : 'gray') 
                            ?>-600 font-medium"><?= ucfirst($row['status']) ?></span>
                        </td>
                        <td class="p-2">
                            <form method="POST" class="flex items-center gap-1">
                                <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
                                <select name="status" class="border p-1 rounded text-sm">
                                    <option value="pending" <?= $row['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="confirmed" <?= $row['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                    <option value="completed" <?= $row['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                    <option value="cancelled" <?= $row['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                                <button name="update_status" class="text-blue-500 hover:underline text-sm">✔</button>
                            </form>
                        </td>
                        <td class="p-2">
                            <a href="../print_booking.php?id=<?= $row['booking_id'] ?>" target="_blank" class="text-blue-500 underline">🖨️</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include('../includes/footer.php'); ?>
