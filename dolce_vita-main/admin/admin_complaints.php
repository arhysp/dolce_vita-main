<?php
include '../includes/db.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];
$message = "";

// Update complaint status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE complaints SET status = ?, updated_by_admin_id = ?, date_updated = NOW() WHERE complaint_id = ?");
    $stmt->bind_param("sii", $status, $admin_id, $complaint_id);
    if ($stmt->execute()) {
        $message = "Complaint status updated successfully.";
    } else {
        $message = "Error updating status.";
    }
    $stmt->close();
}

// Fetch all complaints
$sql = "
SELECT c.*, 
       CONCAT(r.first_name, ' ', r.middle_name, ' ', r.last_name) AS resident_name,
       a.username AS admin_username
FROM complaints c
JOIN residents r ON c.resident_id = r.resident_id
LEFT JOIN admins a ON c.updated_by_admin_id = a.admin_id
ORDER BY c.date_submitted DESC;
";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Complaints</title>
    <link rel="stylesheet" href="../assets/css/admin_complaints.css">
</head>

<body>
    <?php include 'admin_header.php'; ?>

    <div class="admin-container">

        <aside class="sidebar">
            <ul>
                <li><a href="admin_residents.php">Manage Residents Accounts</a></li>
                <li><a href="admin_announcements.php">Post Announcements</a></li>
                <li><a href="admin_complaints.php" class="active">View Complaints</a></li>
                <li><a href="admin_dues.php">Manage Dues</a></li>
            </ul>
        </aside>

        <h2 class="page-title">Manage Resident Complaints</h2>
        <?php if (!empty($message)) echo "<p class='message'>$message</p>"; ?>

        <div class="complaints-table">
            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Complaint ID</th>
                            <th>Resident</th>
                            <th>Subject</th>
                            <th>Details</th>
                            <th>Photo</th>
                            <th>Status</th>
                            <th>Updated By</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['complaint_id']) ?></td>
                                <td><?= htmlspecialchars($row['resident_name']) ?></td>
                                <td><?= htmlspecialchars($row['subject']) ?></td>
                                <td class="details"><?= nl2br(htmlspecialchars($row['details'])) ?></td>
                                <td>
                                    <?php if (!empty($row['photo'])): ?>
                                        <img src="<?= htmlspecialchars($row['photo']) ?>" alt="Photo" class="complaint-photo">
                                    <?php else: ?>
                                        <span class="no-photo">No Photo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" class="status-form">
                                        <input type="hidden" name="complaint_id" value="<?= $row['complaint_id'] ?>">
                                        <select name="status" class="status-select <?= strtolower(str_replace(' ', '-', $row['status'])) ?>">
                                            <option value="New" <?= $row['status'] == 'New' ? 'selected' : '' ?>>New</option>
                                            <option value="In Progress" <?= $row['status'] == 'In Progress' ? 'selected' : '' ?>>In Progress</option>
                                            <option value="Resolved" <?= $row['status'] == 'Resolved' ? 'selected' : '' ?>>Resolved</option>
                                        </select>
                                        <button type="submit" name="update_status" class="btn-update">Update</button>
                                    </form>
                                </td>
                                <td><?= !empty($row['admin_username']) ? htmlspecialchars($row['admin_username']) : '—' ?></td>
                                <td>
                                    <small>
                                        <strong>Submitted:</strong> <?= date("M d, Y h:i A", strtotime($row['date_submitted'])) ?><br>
                                        <strong>Updated:</strong> <?= date("M d, Y h:i A", strtotime($row['date_updated'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <form method="POST" action="delete_complaint.php" onsubmit="return confirm('Are you sure you want to delete this complaint?');">
                                        <input type="hidden" name="complaint_id" value="<?= $row['complaint_id'] ?>">
                                        <button type="submit" class="btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="no-complaints">No complaints found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>