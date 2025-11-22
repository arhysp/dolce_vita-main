<?php
include __DIR__ . '/../includes/db.php';
session_start();

// Check if user is resident
if (!isset($_SESSION['resident_id'])) {
    header('Location: ../login.php');
    exit;
}

$currentPage = 'announcements';

$stmt = $conn->prepare("SELECT * FROM announcements ORDER BY created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Resident - Announcements</title>
    <link rel="stylesheet" href="../assets/css/resident_announcements.css">
</head>

<body>
    <?php include 'resident_header.php'; ?>

    <aside class="sidebar">
        <ul>
            <li><a href="resident_announcements.php" class="active">View Announcements</a></li>
            <li><a href="resident_complaints.php">Post Complaints</a></li>
            <li><a href="resident_dues.php">View Dues</a></li>
        </ul>
    </aside>

    <div class="announcements-container">
        <h1>Community Announcements</h1>

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="announcement-card">
                    <h2><?= htmlspecialchars($row['title']) ?></h2>
                    <div class="announcement-meta">
                        Posted by <?= htmlspecialchars($row['author']) ?>
                        on <?= date("F j, Y, g:i a", strtotime($row['created_at'])) ?>
                    </div>
                    <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>

                    <?php if (!empty($row['image'])): ?>
                        <img src="../admin/uploads/<?= htmlspecialchars($row['image']) ?>" alt="Announcement Image">
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="no-announcements">No announcements have been posted yet.</p>
        <?php endif; ?>
    </div>

</body>