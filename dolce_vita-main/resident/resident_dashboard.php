<?php
session_start();
if (!isset($_SESSION['resident_id'])) {
    header("Location: ../login.php");
    exit();
}
$currentPage = 'home';
include 'resident_header.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Resident Dashboard- Dolce Vita</title>
    <link rel="stylesheet" href="../assets/css/residentdb.css">
</head>

<body>
    <section class="resident-hero">
        <div class="resident-hero-content">
            <h1>Welcome,
                <span><?= htmlspecialchars($_SESSION['resident_name']); ?></span>!
            </h1>
            <p>View announcements, post your complaints, check your dues status and manage your profile here!</p>

            <div class="resident-buttons">
                <a href="resident_announcements.php" class="resident-btn">View Announcements</a>
                <a href="resident_complaints.php" class="resident-btn">Post Complaints</a>
                <a href="resident_dues.php" class="resident-btn">View Dues</a>
            </div>
        </div>
    </section>
</body>

</html>