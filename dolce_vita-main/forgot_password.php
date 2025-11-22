<?php
include __DIR__ . '/includes/db.php';
session_start();

include 'includes/header.php';

$currentPage = 'forgot_password';
$message = '';

if (isset($_POST['reset_request'])) {
    $email = trim($_POST['email']);
    $token = bin2hex(random_bytes(50));
    $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

    // Check if the email exists in either admins or residents
    $stmt = $conn->prepare("
        SELECT 'admin' AS type FROM admins WHERE email=?
        UNION
        SELECT 'resident' AS type FROM residents WHERE email=?
    ");
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $type = $user['type'];

        // Update reset token & expiry in the correct table
        if ($type === 'admin') {
            $stmt2 = $conn->prepare("UPDATE admins SET reset_token=?, reset_expiry=? WHERE email=?");
        } else {
            $stmt2 = $conn->prepare("UPDATE residents SET reset_token=?, reset_expiry=? WHERE email=?");
        }
        $stmt2->bind_param("sss", $token, $expiry, $email);
        $stmt2->execute();

        // ✅ Simplified reset link (no type)
        $reset_link = "http://localhost/dolcevita/reset_password.php?token=" . urlencode($token);

        $message = "
            Your password reset link:<br>
            <a href='$reset_link' target='_blank'>$reset_link</a><br><br>
            <small>This link will expire in 1 hour.</small>
        ";
    } else {
        $message = "Email not found in our records.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .notification {
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
            background-color: #222;
            color: #fff;
            text-align: center;
            word-wrap: break-word;
            line-height: 1.6;
        }

        .notification a {
            color: #4fc3f7;
            text-decoration: underline;
        }

        .notification small {
            color: #ccc;
        }
    </style>
</head>

<body class="login-bg">
    <header class="navbar">
        <div class="logo">
            <img src="assets/background/logo.jpg" alt="Dolce Vita Logo"><span class="logo-text">Dolce Vita Community Portal</span>
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="community.php">Community</a></li>
            </ul>
        </nav>
    </header>

    <div class="login-hero">
        <div class="login-box">
            <h1>Forgot Password</h1>
            <p>Enter your registered email to receive a password reset link.</p>

            <form method="POST">
                <input type="email" name="email" placeholder="Email" required>
                <button type="submit" name="reset_request">Send Reset Link</button>
            </form>

            <?php if (!empty($message)): ?>
                <div class="notification">
                    <?= $message ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>