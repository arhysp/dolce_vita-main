<?php
include __DIR__ . '/../includes/db.php';
session_start();

$currentPage = 'manage_residents';

// Approve / Reject logic
if (isset($_POST['approve']) || isset($_POST['reject'])) {
    $resident_id = intval($_POST['resident_id']);
    $status = isset($_POST['approve']) ? 'approved' : 'rejected';
    $msg = $status === 'approved' ? "Your account has been approved!" : "Your account has been rejected.";

    // Get resident email safely
    $stmt = $conn->prepare("SELECT email FROM residents WHERE resident_id = ?");
    $stmt->bind_param("i", $resident_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $email = ($res->num_rows > 0) ? $res->fetch_assoc()['email'] : null;
    $stmt->close();

    if ($email) {
        // Update status
        $stmt = $conn->prepare("UPDATE residents SET status = ? WHERE resident_id = ?");
        $stmt->bind_param("si", $status, $resident_id);
        $stmt->execute();
        $stmt->close();

        // Insert notification
        $stmt2 = $conn->prepare("INSERT INTO notifications (resident_email, message) VALUES (?, ?)");
        $stmt2->bind_param("ss", $email, $msg);
        $stmt2->execute();
        $stmt2->close();
    }
}

// Fetch all residents
$residents = $conn->query("SELECT * FROM residents ORDER BY resident_id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Manage Residents</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <?php include 'admin_header.php'; ?>
    <div class="admin-container">
        <aside class="sidebar">
            <ul>
                <li><a href="admin_residents.php" class="active">Manage Residents Accounts</a></li>
                <li><a href="admin_announcements.php">Post Announcements</a></li>
                <li><a href="admin_complaints.php">View Complaints</a></li>
                <li><a href="admin_dues.php">Manage Dues</a></li>
            </ul>
        </aside>

        <section class="admin-hero">
            <div class="admin-content table-container">
                <h1>Residents Approval</h1>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Lot Number</th>
                            <th>Blk Number</th>
                            <th>Contact</th>
                            <th>Proof</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($residents->num_rows > 0): ?>
                            <?php while ($row = $residents->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['resident_id']) ?></td>
                                    <td><?= htmlspecialchars($row['first_name']) ?></td>
                                    <td><?= htmlspecialchars($row['last_name']) ?></td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['lot_number']) ?></td>
                                    <td><?= htmlspecialchars($row['blk_number']) ?></td>
                                    <td><?= htmlspecialchars($row['contact_no']) ?></td>
                                    <td>
                                        <?php if (!empty($row['proof'])): ?>
                                            <a href="../<?= htmlspecialchars($row['proof']) ?>" target="_blank" style="color:#4dd0e1;">View</a>
                                        <?php else: ?>
                                            <span style="color:#ccc;">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['status']) ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'pending'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="resident_id" value="<?= htmlspecialchars($row['resident_id']) ?>">
                                                <button type="submit" class="btn approve" name="approve">Approve</button>
                                                <button type="submit" class="btn reject" name="reject">Reject</button>
                                            </form>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" style="text-align:center;">No residents registered yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
</body>

</html>