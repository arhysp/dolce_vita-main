<?php
include __DIR__ . '/includes/db.php';
session_start();

$message = "";
$show_form = false;

if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $now = date('Y-m-d H:i:s');

    // Step 1: Check in residents table
    $stmt = $conn->prepare("SELECT * FROM residents WHERE reset_token = ? AND reset_expiry > ?");
    $stmt->bind_param("ss", $token, $now);
    $stmt->execute();
    $resident_result = $stmt->get_result();

    if ($resident_result->num_rows === 1) {
        $user = $resident_result->fetch_assoc();
        $table = 'residents';
        $id_field = 'resident_id';
        $show_form = true;
    } else {


        // Step 2: Check in admins table
        $stmt = $conn->prepare("SELECT * FROM admins WHERE reset_token = ? AND reset_expiry > ?");
        $stmt->bind_param("ss", $token, $now);
        $stmt->execute();
        $admin_result = $stmt->get_result();

        if ($admin_result->num_rows === 1) {
            $user = $admin_result->fetch_assoc();
            $table = 'admins';
            $id_field = 'admin_id';
            $show_form = true;
        } else {
            $message = "<p style='color:red;'>Invalid or expired token.</p>";
        }
    }

    // Step 3: Handle password reset
    if ($show_form && isset($_POST['reset_password'])) {
        $new_password = trim($_POST['password']);

        if (strlen($new_password) < 6) {
            $message = "<p style='color:red;'>Password must be at least 6 characters long.</p>";
        } else {
            $hashed = password_hash($new_password, PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE $table SET password = ?, reset_token = NULL, reset_expiry = NULL WHERE $id_field = ?");
            $update->bind_param("si", $hashed, $user[$id_field]);

            if ($update->execute()) {
                $message = "<p style='color:green;'>✅ Password successfully updated! You can now <a href='login.php'>login</a>.</p>";
                $show_form = false;
            } else {
                $message = "<p style='color:red;'>❌ Error updating password. Please try again.</p>";
            }
        }
    }
} else {
    $message = "<p style='color:red;'>Invalid password reset link.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="login-bg">
    <div class="login-hero">
        <div class="login-box">
            <h2>Reset Password</h2>
            <?= $message ?>

            <?php if ($show_form): ?>
                <form method="POST">
                    <label>New Password:</label>
                    <input type="password" name="password" required placeholder="Enter new password">
                    <button type="submit" name="reset_password">Reset Password</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>