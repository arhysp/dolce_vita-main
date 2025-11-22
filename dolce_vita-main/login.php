<?php

declare(strict_types=1);
include __DIR__ . '/includes/db.php';
session_start();

$currentpage = 'login';
include 'includes/header.php';

ini_set('display_errors', '1');
error_reporting(E_ALL);

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        // --- Check Admins ---
        $stmt = $conn->prepare("SELECT admin_id, password FROM admins WHERE username = ? OR email = ? LIMIT 1");
        if (! $stmt) {
            error_log('Prepare failed (admins): ' . $conn->error);
            $error = 'Server error.';
        } else {
            $stmt->bind_param('ss', $username, $username);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res && $res->num_rows === 1) {
                $admin = $res->fetch_assoc();
                if (password_verify($password, $admin['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['role'] = 'admin';
                    header('Location: admin/admin_dashboard.php');
                    exit();
                } else {
                    $error = 'Invalid username or password.';
                }
            } else {
                // --- Check Residents ---
                $stmt2 = $conn->prepare("SELECT resident_id, password, status, first_name, last_name FROM residents WHERE username = ? OR email = ? LIMIT 1");
                if (! $stmt2) {
                    error_log('Prepare failed (residents): ' . $conn->error);
                    $error = 'Server error.';
                } else {
                    $stmt2->bind_param('ss', $username, $username);
                    $stmt2->execute();
                    $res2 = $stmt2->get_result();

                    if ($res2 && $res2->num_rows === 1) {
                        $resident = $res2->fetch_assoc();
                        if (password_verify($password, $resident['password'])) {
                            if ($resident['status'] === 'approved') {
                                session_regenerate_id(true);
                                $_SESSION['resident_id'] = $resident['resident_id'];
                                $_SESSION['resident_name'] = $resident['first_name'] . ' ' . $resident['last_name'];
                                $_SESSION['role'] = 'resident';
                                header('Location: resident/resident_dashboard.php');
                                exit();
                            } elseif ($resident['status'] === 'pending') {
                                $error = 'Your account is still pending approval.';
                            } else {
                                $error = 'Your account has been rejected.';
                            }
                        } else {
                            $error = 'Invalid username or password.';
                        }
                    } else {
                        $error = 'Invalid username or password.';
                    }
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Login - Dolce Vita</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="assets/css/style.css">
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
            <h1>Welcome Back</h1>
            <p>Login to your Dolce Vita account</p>

            <?php if (!empty($error)): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="text" name="username" placeholder="Username or email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" name="login">Login</button>
                <p class="forgot"><a href="forgot_password.php">Forgot password?</a></p>
            </form>
        </div>
    </div>
</body>

</html>