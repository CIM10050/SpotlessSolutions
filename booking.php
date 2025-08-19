<?php
// booking.php
if (session_status() === PHP_SESSION_NONE) session_start();

include('includes/auth_check.php');
include('includes/db_connect.php');

if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = $error = "";

// --- Load settings (open/close time + holidays) ---
$settings = ['open_time' => '10:00', 'close_time' => '18:00', 'holidays' => ''];
if ($res = mysqli_query($conn, "SELECT setting_key, setting_value FROM shop_settings WHERE setting_key IN ('open_time','close_time','holidays')")) {
    while ($row = mysqli_fetch_assoc($res)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}
$open_time = $settings['open_time'] ?? '10:00';
$close_time = $settings['close_time'] ?? '18:00';
$holidays  = array_filter(array_map('trim', explode(",", $settings['holidays'] ?? '')));

// --- Handle booking submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['service_type'], $_POST['scheduled_date'], $_POST['scheduled_time'])) {
    $userId      = (int)$_SESSION['user_id'];
    $serviceType = $_POST['service_type']; // 'pickup' | 'dropoff' | 'walkin'
    $date        = $_POST['scheduled_date']; // YYYY-MM-DD
    $time        = $_POST['scheduled_time']; // HH:MM
    $address     = trim($_POST['address'] ?? '');
    $instructions= trim($_POST['special_instructions'] ?? '');

    // Basic validation
    if (!in_array($serviceType, ['pickup','dropoff','walkin'], true)) {
        $error = "❌ Invalid service type.";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $error = "❌ Invalid date format.";
    } elseif (!preg_match('/^\d{2}:\d{2}$/', $time)) {
        $error = "❌ Invalid time format.";
    } elseif (in_array($date, $holidays, true)) {
        $error = "⛔ This is a shop holiday. Please choose another date.";
    } else {
        // Check conflict
        $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE scheduled_date = ? AND scheduled_time = ?");
        $stmt->bind_param("ss", $date, $time);
        $stmt->execute();
        $stmt->bind_result($cnt);
        $stmt->fetch();
        $stmt->close();

        if ($cnt > 0) {
            $error = "❌ This time slot is already booked. Please select another.";
        } else {
            // Insert booking
            $stmt = $conn->prepare("
                INSERT INTO bookings (user_id, service_type, address, scheduled_date, scheduled_time, special_instructions)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            // allow NULL address for walk-in
            $addr = ($serviceType === 'walkin') ? null : ($address !== '' ? $address : null);
            $stmt->bind_param("isssss", $userId, $serviceType, $addr, $date, $time, $instructions);
            if ($stmt->execute()) {
                $success = "✅ Booking submitted successfully!";
                // soft refresh to clear form and show it in history
                header("refresh:1;url=booking.php");
            } else {
                $error = "❌ Error while booking.";
            }
            $stmt->close();
        }
    }
}

// --- Load previous bookings for the user ---
$userId = (int)$_SESSION['user_id'];
$prevBookings = [];
$stmt = $conn->prepare("
    SELECT booking_id, service_type, address, scheduled_date, scheduled_time, status, created_at
    FROM bookings
    WHERE user_id = ?
    ORDER BY booking_id DESC
    LIMIT 20
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) $prevBookings[] = $row;
$stmt->close();

// Simple badge helper
function badge($status) {
    $map = [
        'pending'   => 'bg-yellow-100 text-yellow-800',
        'confirmed' => 'bg-blue-100 text-blue-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-800',
    ];
    $cls = $map[$status] ?? 'bg-gray-100 text-gray-700';
    $txt = htmlspecialchars($status);
    return "<span class=\"px-2 py-1 rounded text-xs font-medium $cls capitalize\">$txt</span>";
}

include('includes/header.php');
?>

<div class="max-w-3xl mx-auto mt-10 p-6 bg-white rounded-xl shadow">
    <h2 class="text-2xl font-bold text-blue-700 mb-4">📅 Book Your Laundry Service</h2>

    <?php if ($success): ?>
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= htmlspecialchars($error) ?></div>
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
            <p class="text-sm text-gray-500 mt-1">
                Open: <?= htmlspecialchars($open_time) ?> · Close: <?= htmlspecialchars($close_time) ?><br>
                Shop holidays: <?= htmlspecialchars(implode(', ', $holidays)) ?: '—' ?>
            </p>
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

        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
            ✅ Confirm Booking
        </button>
    </form>

    <!-- Previous bookings -->
    <div class="mt-10">
        <h3 class="text-xl font-semibold mb-3">🧾 My Previous Bookings</h3>

        <?php if (empty($prevBookings)): ?>
            <div class="text-gray-600">No previous bookings yet.</div>
        <?php else: ?>
            <div class="bg-white border rounded-lg overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left px-4 py-2">#</th>
                            <th class="text-left px-4 py-2">Type</th>
                            <th class="text-left px-4 py-2">Date</th>
                            <th class="text-left px-4 py-2">Time</th>
                            <th class="text-left px-4 py-2">Address</th>
                            <th class="text-left px-4 py-2">Status</th>
                            <th class="text-left px-4 py-2">Created</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <?php foreach ($prevBookings as $b): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">#<?= (int)$b['booking_id'] ?></td>
                                <td class="px-4 py-2 capitalize"><?= htmlspecialchars($b['service_type']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars(date('Y-m-d', strtotime($b['scheduled_date']))) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars($b['scheduled_time']) ?></td>
                                <td class="px-4 py-2"><?= $b['address'] ? htmlspecialchars($b['address']) : '—' ?></td>
                                <td class="px-4 py-2"><?= badge($b['status']) ?></td>
                                <td class="px-4 py-2"><?= htmlspecialchars(date('d M Y, h:i A', strtotime($b['created_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Toggle address field for pickup/dropoff
document.getElementById('service_type').addEventListener('change', function () {
    document.getElementById('addressField').classList.toggle('hidden', !(this.value === 'pickup' || this.value === 'dropoff'));
});

// Time slot generation
const openTime = "<?= $open_time ?>";   // "HH:MM"
const closeTime = "<?= $close_time ?>"; // "HH:MM"
const holidays  = <?= json_encode(array_values($holidays)) ?>;

const slotContainer = document.getElementById("slotContainer");
const hiddenInput   = document.getElementById("scheduled_time_hidden");

document.getElementById("scheduled_date").addEventListener("change", function () {
    const selectedDate = this.value;

    // Holiday check
    if (holidays.includes(selectedDate)) {
        alert("⛔ This is a shop holiday. Please choose another date.");
        this.value = '';
        slotContainer.innerHTML = '';
        hiddenInput.value = '';
        return;
    }

    // Build slots (every 2 hours, inclusive start, exclusive end)
    const [sH, sM] = openTime.split(":").map(Number);
    const [eH, eM] = closeTime.split(":").map(Number);
    const start = sH; const end = eH; // assumes :00 times as in settings

    slotContainer.innerHTML = '';
    hiddenInput.value = '';

    for (let h = start; h < end; h += 2) {
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

        // Check conflict via AJAX (expects 'available' / 'unavailable')
        fetch(`check_slot.php?date=${encodeURIComponent(selectedDate)}&time=${encodeURIComponent(slot)}`)
            .then(res => res.text())
            .then(status => {
                if (status.trim() === 'unavailable') {
                    btn.disabled = true;
                    btn.classList.add("opacity-50", "cursor-not-allowed");
                    btn.textContent += " ❌";
                }
            })
            .catch(() => {});
        slotContainer.appendChild(btn);
    }
});
</script>

<?php include('includes/footer.php'); ?>
