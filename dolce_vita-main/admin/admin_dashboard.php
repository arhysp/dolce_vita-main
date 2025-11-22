<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}
$currentPage = 'home';
include 'admin_header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Dolce Vita</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body class="admin-body">

    <section class="admin-hero">
        <div class="admin-hero-content">
            <h1>Welcome, <span>Admin!</span></h1>
            <p>Manage residents, announcements, complaints, and dues here.</p>

            <div class="admin-buttons">
                <a href="admin_residents.php" class="admin-btn">Manage Residents Accounts</a>
                <a href="admin_announcements.php" class="admin-btn">Post Announcements</a>
                <a href="admin_complaints.php" class="admin-btn">View Complaints</a>
                <a href="admin_dues.php" class="admin-btn">Manage Dues</a>
            </div>
        </div>
    </section>

</body>

</html>