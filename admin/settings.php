<?php
// admin/settings.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once('../includes/auth_check.php');
require_once('../includes/db_connect.php');

// Restrict access
if (empty($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// --- Ensure keys exist so the page always has values ---
mysqli_query($conn, "INSERT IGNORE INTO shop_settings (setting_key, setting_value) VALUES
  ('open_time','10:00'),
  ('close_time','18:00'),
  ('holidays','')
");

// Fetch existing values (safe default if query fails)
$settings = ['open_time' => '10:00', 'close_time' => '18:00', 'holidays' => ''];
if ($res = mysqli_query($conn, "SELECT setting_key, setting_value FROM shop_settings WHERE setting_key IN ('open_time','close_time','holidays')")) {
    while ($row = mysqli_fetch_assoc($res)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

$success = $error = "";

// --- Handle form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $open  = $_POST['open_time']  ?? '10:00';
    $close = $_POST['close_time'] ?? '18:00';

    // Normalize holidays CSV to unique, sorted YYYY-MM-DD list
    $raw = $_POST['holidays'] ?? '';
    $arr = array_filter(array_map('trim', explode(',', $raw)));
    $arr = array_values(array_unique(array_filter($arr, function($d){
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d); // YYYY-MM-DD
    })));
    sort($arr);
    $holidaysCsv = implode(',', $arr);

    // Upsert with prepared statement
    $stmt = $conn->prepare("INSERT INTO shop_settings (setting_key, setting_value)
                            VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    if (!$stmt) {
        $error = "Error saving settings.";
    } else {
        $ok = true;
        foreach ([ ['open_time', $open], ['close_time', $close], ['holidays', $holidaysCsv] ] as [$k, $v]) {
            $stmt->bind_param('ss', $k, $v);
            if (!$stmt->execute()) { $ok = false; break; }
        }
        $stmt->close();

        if ($ok) {
            $success = "✅ Settings updated successfully!";
            // reflect normalized values without reload
            $settings['open_time']  = $open;
            $settings['close_time'] = $close;
            $settings['holidays']   = $holidaysCsv;
        } else {
            $error = "Error saving settings.";
        }
    }
}

include('../includes/header.php');
?>
<div class="max-w-3xl mx-auto mt-10 p-6 bg-white rounded-xl shadow-md">
    <h2 class="text-2xl font-bold mb-4 text-blue-700">🛠 Admin Settings – Shop Configuration</h2>

    <?php if ($success): ?>
        <div class="bg-green-100 text-green-800 p-3 mb-4 rounded shadow"><?= htmlspecialchars($success) ?></div>
    <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded shadow"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-6">
        <div>
            <label class="block text-sm font-medium mb-1">Opening Time</label>
            <input type="time" name="open_time" required
                   value="<?= htmlspecialchars($settings['open_time'] ?? '10:00') ?>"
                   class="w-full border p-2 rounded">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Closing Time</label>
            <input type="time" name="close_time" required
                   value="<?= htmlspecialchars($settings['close_time'] ?? '18:00') ?>"
                   class="w-full border p-2 rounded">
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Holiday Dates</label>

            <!-- server-side initial chips (shows even if JS blocked) -->
            <div id="holidayChips" class="flex flex-wrap gap-2 mb-2">
                <?php
                  $csv = $settings['holidays'] ?? '';
                  $initDates = array_filter(array_map('trim', explode(',', $csv)));
                  foreach ($initDates as $d):
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) continue;
                ?>
                    <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-sm border">
                      <?= htmlspecialchars($d) ?>
                      <button type="button" data-date="<?= htmlspecialchars($d) ?>"
                              class="text-blue-700 hover:text-blue-900" aria-label="Remove <?= htmlspecialchars($d) ?>">✕</button>
                    </span>
                <?php endforeach; ?>
            </div>

            <!-- date picker + add -->
            <div class="flex gap-2">
                <input type="date" id="holidayPicker" class="border p-2 rounded">
                <button type="button" id="addHolidayBtn"
                        class="px-3 rounded bg-gray-100 hover:bg-gray-200 border">Add</button>
            </div>

            <!-- hidden CSV actually submitted -->
            <input type="hidden" name="holidays" id="holidaysField"
                   value="<?= htmlspecialchars($settings['holidays'] ?? '') ?>">

            <p class="text-sm text-gray-500 mt-1">
                Click “Add” to include each date. Click a chip’s ✕ to remove.
            </p>
        </div>

        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition w-full mt-4">
            💾 Save Settings
        </button>
    </form>
</div>

<?php include('../includes/footer.php'); ?>

<script>
(function() {
  const chipsEl   = document.getElementById('holidayChips');
  const pickerEl  = document.getElementById('holidayPicker');
  const addBtn    = document.getElementById('addHolidayBtn');
  const hiddenEl  = document.getElementById('holidaysField');

  // parse initial CSV -> array of YYYY-MM-DD
  let dates = (hiddenEl.value || '')
    .split(',')
    .map(s => s.trim())
    .filter(Boolean);

  function render() {
    // keep unique + sorted
    dates = Array.from(new Set(dates)).sort();
    // render chips
    chipsEl.innerHTML = dates.map(d => `
      <span class="inline-flex items-center gap-2 px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-sm border">
        ${d}
        <button type="button" data-date="${d}" class="text-blue-700 hover:text-blue-900" aria-label="Remove ${d}">✕</button>
      </span>
    `).join('');
    // sync hidden CSV field
    hiddenEl.value = dates.join(',');
  }

  function isValidISODate(s) {
    return /^\d{4}-\d{2}-\d{2}$/.test(s);
  }

  addBtn.addEventListener('click', () => {
    const val = pickerEl.value;
    if (!val || !isValidISODate(val)) return;
    dates.push(val);
    render();
    pickerEl.value = '';
  });

  chipsEl.addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-date]');
    if (!btn) return;
    const d = btn.dataset.date;
    dates = dates.filter(x => x !== d);
    render();
  });

  // initial paint (overrides server-side markup to ensure state is synced)
  render();
})();
</script>
