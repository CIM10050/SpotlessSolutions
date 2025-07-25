<?php
session_start();
include 'includes/config.php';
include 'includes/db_connect.php';

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $error = "❌ Passwords do not match.";
    } else {
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "❌ Email already registered.";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $full_name, $email, $phone, $hashed);

            if ($stmt->execute()) {
                $success = "✅ Registration successful! Please log in.";
            } else {
                $error = "❌ Something went wrong. Try again.";
            }
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="max-w-md mx-auto mt-12 bg-white shadow-md rounded px-8 pt-6 pb-8">
    <h2 class="text-2xl font-bold mb-6 text-center">Register</h2>

    <?php if ($error): ?>
        <div class="mb-4 bg-red-100 text-red-800 p-3 rounded"><?= $error ?></div>
    <?php elseif ($success): ?>
        <div class="mb-4 bg-green-100 text-green-800 p-3 rounded"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <input type="text" name="full_name" placeholder="Full Name" required class="w-full border p-2 rounded">
        <input type="email" name="email" placeholder="Email" required class="w-full border p-2 rounded">
        <input type="text" name="phone" placeholder="Phone (optional)" class="w-full border p-2 rounded">
        <input type="password" name="password" placeholder="Password" required class="w-full border p-2 rounded">
        <input type="password" name="confirm_password" placeholder="Confirm Password" required class="w-full border p-2 rounded">
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Register</button>
    </form>
    <p class="mt-4 text-center text-sm">Already have an account? <a href="<?= BASE_URL ?>/login.php" class="text-blue-600 hover:underline">Login here</a></p>
</div>

<?php include 'includes/footer.php'; ?>
