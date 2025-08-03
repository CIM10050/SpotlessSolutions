<?php
include('../includes/auth_check.php');
include('../includes/db_connect.php');

// Restrict access
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$success = $error = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $open = $_POST['open_time'];
    $close = $_POST['close_time'];
    $holidays = $_POST['holidays']; // comma-separated

    $queries = [
        "UPDATE shop_settings SET setting_value='$open' WHERE setting_key='open_time'",
        "UPDATE shop_settings SET setting_value='$close' WHERE setting_key='close_time'",
        "UPDATE shop_settings SET setting_value='$holidays' WHERE setting_key='holidays'"
    ];

    $allOK = true;
    foreach ($queries as $q) {
        if (!mysqli_query($conn, $q)) {
            $allOK = false;
            $error = "Error saving settings.";
            break;
        }
    }
    if ($allOK) $success = "✅ Settings updated successfully!";
}

// Fetch existing values
$settings = [];
$result = mysqli_query($conn, "SELECT * FROM shop_settings");
while ($row = mysqli_fetch_assoc($result)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<?php include('../includes/header.php'); ?>

<div class="max-w-3xl mx-auto mt-10 p-6 bg-white rounded-xl shadow-md">
    <h2 class="text-2xl font-bold mb-4 text-blue-700">🛠 Admin Settings – Shop Configuration</h2>

    <?php if ($success): ?>
        <div class="bg-green-100 text-green-800 p-3 mb-4 rounded shadow"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded shadow"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div>
            <label class="block text-sm font-medium mb-1">Opening Time</label>
            <input type="time" name="open_time" required value="<?= $settings['open_time'] ?? '10:00' ?>" class="w-full border p-2 rounded">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Closing Time</label>
            <input type="time" name="close_time" required value="<?= $settings['close_time'] ?? '18:00' ?>" class="w-full border p-2 rounded">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Holiday Dates (comma separated)</label>
            <input type="text" name="holidays" placeholder="e.g., 2025-08-15,2025-08-20" value="<?= $settings['holidays'] ?? '' ?>" class="w-full border p-2 rounded">
            <p class="text-sm text-gray-500 mt-1">Use YYYY-MM-DD format. Separate multiple with commas.</p>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition w-full mt-4">
            💾 Save Settings
        </button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>
