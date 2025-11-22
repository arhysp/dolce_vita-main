<?php
include __DIR__ . '/includes/db.php';
session_start();

$currentpage = 'register';
include 'includes/header.php';


if (isset($_POST['register'])) {
    $errors = [];

    // Sanitize Inputs
    $first_name   = htmlspecialchars(trim($_POST['first_name']));
    $middle_name  = htmlspecialchars(trim($_POST['middle_name']));
    $last_name    = htmlspecialchars(trim($_POST['last_name']));
    $username     = htmlspecialchars(trim($_POST['username']));
    $email        = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $contact_no   = htmlspecialchars(trim($_POST['contact_no']));
    $birthdate    = $_POST['birthdate'];
    $address      = htmlspecialchars(trim($_POST['address']));
    $lot_number   = htmlspecialchars(trim($_POST['lot_number']));
    $blk_number   = htmlspecialchars(trim($_POST['blk_number']));
    $password     = $_POST['password'];
    $confirm_pass = $_POST['confirm_password'];

    // Required field check
    $required_fields = [
        'first_name',
        'last_name',
        'middle_name',
        'username',
        'email',
        'contact_no',
        'birthdate',
        'address',
        'lot_number',
        'blk_number',
        'proof',
        'password',
        'confirm_password'
    ];

    foreach ($required_fields as $field) {
        if ($field === 'proof') {
            if (empty($_FILES['proof']['name'])) {
                $errors[] = "Proof of Residency is required.";
            }
        } elseif (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
        }
    }

    // Password match check
    if ($password !== $confirm_pass) {
        $errors[] = "Passwords do not match.";
    }

    // Password strength check
    if (
        strlen($password) < 8 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[a-z]/', $password) ||
        !preg_match('/[0-9]/', $password)
    ) {
        $errors[] = "Password must be at least 8 characters long and include uppercase, lowercase, and numbers.";
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    // Check for duplicate username or email
    $check = $conn->prepare("SELECT resident_id FROM residents WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $errors[] = "Username or email already exists.";
    }
    $check->close();

    // If there are validation errors, show them
    if (!empty($errors)) {
        $_SESSION['error'] = $errors;
        $_SESSION['invalid_fields'] = array_keys($_POST, '', true);
        if (empty($_FILES['proof']['name'])) {
            $_SESSION['invalid_fields'][] = 'proof';
        }
        header("Location: register.php");
        exit();
    }

    // Handle proof upload
    if (!empty($_FILES['proof']['name'])) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
        $proof_tmp = $_FILES['proof']['tmp_name'];
        $file_type = mime_content_type($proof_tmp);

        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error'] = ["Invalid file type. Only JPG, PNG, and PDF are allowed."];
            header("Location: register.php");
            exit();
        }

        $ext = pathinfo($_FILES['proof']['name'], PATHINFO_EXTENSION);
        $new_filename = uniqid('proof_', true) . '.' . $ext;
        $upload_dir = __DIR__ . '/uploads/proofs';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $proof_path = 'uploads/proofs/' . $new_filename; // store relative path
        $full_path = $upload_dir . '/' . $new_filename;

        if (!move_uploaded_file($proof_tmp, $full_path)) {
            $_SESSION['error'] = ["Error uploading proof file."];
            header("Location: register.php");
            exit();
        }
    } else {
        $proof_path = null;
    }

    // Hash Password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert new resident
    $stmt = $conn->prepare("
        INSERT INTO residents 
        (first_name, middle_name, last_name, username, email, contact_no, birthdate, address, lot_number, blk_number, proof, password, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->bind_param(
        "ssssssssssss",
        $first_name,
        $middle_name,
        $last_name,
        $username,
        $email,
        $contact_no,
        $birthdate,
        $address,
        $lot_number,
        $blk_number,
        $proof_path,
        $hashed_password
    );

    if ($stmt->execute()) {
        $_SESSION['success'] = "Registration successful! Please wait for admin approval.";
        header("Location: register.php");
        exit();
    } else {
        error_log('Database Error (register): ' . $stmt->error);
        $_SESSION['error'] = ["An error occurred. Please try again later."];
        header("Location: register.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Resident Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/register.css">
</head>


<body>
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


    <!-- Notification Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="notification success">
            <?= htmlspecialchars($_SESSION['success']); ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>


    <?php if (isset($_SESSION['error']) && count($_SESSION['error']) > 0): ?>
        <div class="notification error">
            <strong>Please fix the following:</strong>
            <ul>
                <?php foreach ($_SESSION['error'] as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>


    <!-- Registration Form -->
    <div class="register-bg">
        <div class="register-container">
            <div class="form-box">
                <h2>Resident Registration</h2>
                <form action="register.php" method="POST" enctype="multipart/form-data" novalidate>

                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" required>
                    </div>

                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" required>
                    </div>

                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" required>
                    </div>

                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" required>
                    </div>

                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_no" required>
                    </div>

                    <div class="form-group">
                        <label>Birthdate</label>
                        <input type="date" name="birthdate" required>
                    </div>

                    <div class="form-group">
                        <label>Address</label>
                        <input type="text" name="address" required>
                    </div>

                    <div class="form-group">
                        <label>Lot Number</label>
                        <input type="text" name="lot_number" required>
                    </div>

                    <div class="form-group">
                        <label>Block Number</label>
                        <input type="text" name="blk_number" required>
                    </div>

                    <div class="form-group file-wide">
                        <label>Proof of Residency (JPG, PNG, PDF) — max 5MB</label>
                        <input type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" required>
                    </div>

                    <button type="submit" name="register" class="btn">Register</button>

                    <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
                </form>
            </div>
        </div>
    </div>
</body>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const notif = document.querySelector('.notification');
        if (notif) {
            setTimeout(() => {
                notif.classList.add('hide');
                setTimeout(() => notif.remove(), 600);
            }, 5000);
        }
    });
</script>

</html>