
<?php
session_start();
include 'includes/config.php';
include 'includes/db_connect.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, full_name, password_hash, user_role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($user_id, $full_name, $password_hash, $role);
        $stmt->fetch();

        if (password_verify($password, $password_hash)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['full_name'] = $full_name;
            $_SESSION['user_role'] = $role;

            if ($role === 'admin') {
                header("Location: " . BASE_URL . "/admin/dashboard.php");
            } else {
                header("Location: " . BASE_URL . "/index.php");
            }
            exit();
        } else {
            $error = "❌ Incorrect password.";
        }
    } else {
        $error = "❌ No account found with this email.";
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="max-w-md mx-auto mt-12 bg-white shadow-md rounded px-8 pt-6 pb-8">
    <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>

    <?php if ($error): ?>
        <div class="mb-4 bg-red-100 text-red-800 p-3 rounded"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <input type="email" name="email" placeholder="Email" required class="w-full border p-2 rounded">
        <input type="password" name="password" placeholder="Password" required class="w-full border p-2 rounded">
        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">Login</button>
    </form>
    <p class="mt-4 text-center text-sm">New user? <a href="<?= BASE_URL ?>/register.php" class="text-blue-600 hover:underline">Register now</a></p>
</div>

<?php include 'includes/footer.php'; ?>
