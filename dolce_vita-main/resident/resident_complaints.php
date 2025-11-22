<?php
include __DIR__ . '/../includes/db.php';
session_start();

// Check if resident is logged in
if (!isset($_SESSION['resident_id'])) {
    header("Location: ../login.php");
    exit;
}

$resident_id = $_SESSION['resident_id'];
$message = "";

// Handle complaint submission
if (isset($_POST['submit_complaint'])) {
    $subject = trim($_POST['subject']);
    $details = trim($_POST['details']);
    $photoPath = null;

    // Handle photo upload (optional)
    if (!empty($_FILES['photo']['name'])) {
        $targetDir = "../uploads/complaints/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $fileName = time() . "_" . basename($_FILES["photo"]["name"]);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["photo"]["tmp_name"], $targetFilePath)) {
            $photoPath = "uploads/complaints/" . $fileName;
        }
    }

    $stmt = $conn->prepare("INSERT INTO complaints (resident_id, subject, details, photo) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $resident_id, $subject, $details, $photoPath);

    if ($stmt->execute()) {
        $message = "Complaint submitted successfully!";
    } else {
        $message = "Error submitting complaint.";
    }
    $stmt->close();
}

// Fetch resident’s complaints
$query = "
    SELECT c.*, a.username AS admin_username
    FROM complaints c
    LEFT JOIN admins a ON c.updated_by_admin_id = a.admin_id
    WHERE c.resident_id = ?
    ORDER BY c.date_submitted DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $resident_id);
$stmt->execute();
$result = $stmt->get_result();


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resident Complaints</title>
    <link rel="stylesheet" href="../assets/css/resident_complaints.css">
</head>

<body>
    <?php include 'resident_header.php'; ?>

    <aside class="sidebar">
        <ul>
            <li><a href="resident_announcements.php">View Announcements</a></li>
            <li><a href="resident_complaints.php" class="active">Post Complaints</a></li>
            <li><a href="resident_dues.php">View Dues</a></li>
        </ul>
    </aside>

    <div class="complaints-container">
        <h2 class="page-title">My Complaints</h2>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- Complaint Form -->
        <form class="complaint-form" action="" method="POST" enctype="multipart/form-data">
            <label>Subject</label>
            <input type="text" name="subject" required>

            <label>Details</label>
            <textarea name="details" rows="4" required></textarea>

            <label>Attach Photo (optional)</label>
            <input type="file" name="photo" accept="image/*">

            <button type="submit" name="submit_complaint" class="btn-submit">Submit Complaint</button>
        </form>

        <!-- Complaints List -->
        <div class="complaints-list">
            <h3>My Submitted Complaints</h3>

            <?php if ($result->num_rows > 0): ?>
                <table>
                    <tr>
                        <th>Subject</th>
                        <th>Details</th>
                        <th>Photo</th>
                        <th>Status</th>
                        <th>Last Updated</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['subject']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['details'])) ?></td>
                            <td>
                                <?php if ($row['photo']): ?>
                                    <img src="../<?= htmlspecialchars($row['photo']) ?>" class="complaint-photo">
                                <?php else: ?>
                                    <span class="no-photo">No Photo</span>
                                <?php endif; ?>
                            </td>
                            <td class="status <?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
                                <?= htmlspecialchars($row['status']) ?>
                            </td>
                            <td>
                                <?= date('M d, Y h:i A', strtotime($row['date_updated'])) ?><br>
                                <?php if ($row['admin_username']): ?>
                                    <small>Updated by: <?= htmlspecialchars($row['admin_username']) ?></small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p class="no-complaints">No complaints submitted yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>