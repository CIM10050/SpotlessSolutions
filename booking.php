<?php
include('includes/auth_check.php');
include('includes/db_connect.php');

$success = $error = "";

// Load settings (open/close time + holidays)
$settings = [];
$settings_q = mysqli_query($conn, "SELECT * FROM shop_settings");
while ($row = mysqli_fetch_assoc($settings_q)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$open_time = $settings['open_time'] ?? '10:00';
$close_time = $settings['close_time'] ?? '18:00';
$holidays = explode(",", $settings['holidays'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    $serviceType = $_POST['service_type'];
    $date = $_POST['scheduled_date'];
    $time = $_POST['scheduled_time'];
    $address = !empty($_POST['address']) ? mysqli_real_escape_string($conn, $_POST['address']) : null;
    $instructions = mysqli_real_escape_string($conn, $_POST['special_instructions']);

    if ($serviceType && $date && $time) {
        $conflict = mysqli_query($conn, "SELECT * FROM bookings WHERE scheduled_date = '$date' AND scheduled_time = '$time'");
        if (mysqli_num_rows($conflict) > 0) {
            $error = "❌ This time slot is already booked. Please select another.";
        } else {
            $query = "INSERT INTO bookings (user_id, service_type, address, scheduled_date, scheduled_time, special_instructions)
                      VALUES ('$userId', '$serviceType', " . ($address ? "'$address'" : "NULL") . ", '$date', '$time', '$instructions')";
            if (mysqli_query($conn, $query)) {
                $success = "✅ Booking submitted successfully!";
                header("refresh:2;url=booking.php");
            } else {
                $error = "❌ Error while booking: " . mysqli_error($conn);
            }
        }
    } else {
        $error = "❌ Please fill all required fields.";
    }
}
?>

<?php include('includes/header.php'); ?>

<div class="max-w-3xl mx-auto mt-10 p-6 bg-white rounded-xl shadow">
    <h2 class="text-2xl font-bold text-blue-700 mb-4">📅 Book Your Laundry Service</h2>

    <?php if ($success): ?>
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" id="bookingForm" class="space-y-6">
        <div>
            <label class="block font-medium mb-1">Service Type</label>
            <select name="service_type" id="service_type" required class="w-full border p-2 rounded">
                <option value="">-- Select --</option>
                <option value="pickup">Pickup from home</option>
                <option value="dropoff">Drop-off at home</option>
                <option value="walkin">Walk-in at store</option>
            </select>
        </div>

        <div id="addressField" class="hidden">
            <label class="block font-medium mb-1">Address</label>
            <textarea name="address" placeholder="Enter your address" class="w-full border p-2 rounded"></textarea>
        </div>

        <div>
            <label class="block font-medium mb-1">Scheduled Date</label>
            <input type="date" name="scheduled_date" id="scheduled_date" required class="w-full border p-2 rounded">
            <p class="text-sm text-gray-500">Shop holidays: <?= implode(', ', $holidays) ?></p>
        </div>

        <div>
            <label class="block font-medium mb-1">Available Time Slots</label>
            <div id="slotContainer" class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-2"></div>
            <input type="hidden" name="scheduled_time" id="scheduled_time_hidden" required>
        </div>

        <div>
            <label class="block font-medium mb-1">Special Instructions</label>
            <textarea name="special_instructions" rows="3" placeholder="Optional notes..." class="w-full border p-2 rounded"></textarea>
        </div>

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">✅ Confirm Booking</button>
    </form>
</div>

<script>
// Toggle address field
document.getElementById('service_type').addEventListener('change', function () {
    document.getElementById('addressField').classList.toggle('hidden', !(this.value === 'pickup' || this.value === 'dropoff'));
});

// Generate slot cards based on time config
const openTime = "<?= $open_time ?>";
const closeTime = "<?= $close_time ?>";
const holidays = <?= json_encode($holidays) ?>;

document.getElementById("scheduled_date").addEventListener("change", function () {
    const selectedDate = this.value;
    const today = new Date().toISOString().split("T")[0];

    // Check holiday
    if (holidays.includes(selectedDate)) {
        alert("⛔ This is a shop holiday. Please choose another date.");
        this.value = '';
        document.getElementById("slotContainer").innerHTML = '';
        return;
    }

    // Generate slots
    const [startHour] = openTime.split(":").map(Number);
    const [endHour] = closeTime.split(":").map(Number);

    const slotContainer = document.getElementById("slotContainer");
    const hiddenInput = document.getElementById("scheduled_time_hidden");
    slotContainer.innerHTML = '';
    hiddenInput.value = '';

    for (let h = startHour; h < endHour; h += 2) {
        const slot = `${String(h).padStart(2, '0')}:00`;

        const btn = document.createElement("button");
        btn.type = "button";
        btn.textContent = slot;
        btn.className = "px-4 py-2 border rounded text-blue-800 bg-blue-100 hover:bg-blue-200 transition";
        btn.onclick = () => {
            hiddenInput.value = slot;
            document.querySelectorAll("#slotContainer button").forEach(b => b.classList.remove("bg-blue-300"));
            btn.classList.add("bg-blue-300");
        };

        // Check conflict via AJAX
        fetch(`check_slot.php?date=${selectedDate}&time=${slot}`)
            .then(res => res.text())
            .then(status => {
                if (status.trim() === 'unavailable') {
                    btn.disabled = true;
                    btn.classList.add("opacity-50", "cursor-not-allowed");
                    btn.textContent += " ❌";
                }
            });

        slotContainer.appendChild(btn);
    }
});
</script>

<?php include('includes/footer.php'); ?>
