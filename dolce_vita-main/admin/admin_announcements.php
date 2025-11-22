<?php
include __DIR__ . '/../includes/db.php';
session_start();

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

$currentPage = 'announcements';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Announcements</title>
    <link rel="stylesheet" href="../assets/css/admin_announcements.css">
</head>

<body>

    <?php include 'admin_header.php'; ?>

    <div class="admin-container">

        <aside class="sidebar">
            <ul>
                <li><a href="admin_residents.php">Manage Residents Accounts</a></li>
                <li><a href="admin_announcements.php" class="active">Post Announcements</a></li>
                <li><a href="admin_complaints.php">View Complaints</a></li>
                <li><a href="admin_dues.php">Manage Dues</a></li>
            </ul>
        </aside>

        <main class="content-area">
            <div class="announcements-header">
                <h2>Post Announcements</h2>
                <button id="createBtn" class="btn">Create Announcement</button>
            </div>

            <div id="createForm" class="announcement-form hidden">
                <form action="process_announcements.php" method="POST" enctype="multipart/form-data">
                    <label>Title</label>
                    <input type="text" name="title" required>

                    <label>Message</label>
                    <textarea name="message" rows="5" required></textarea>

                    <label>Image (optional)</label>
                    <input type="file" name="image">

                    <input type="hidden" name="author" value="Admin">
                    <button type="submit" class="btn">Post Announcement</button>
                </form>
            </div>

            <div class="announcement-list">
                <?php
                // Fetch announcements
                $query = "SELECT * FROM announcements ORDER BY created_at DESC";
                $result = $conn->query($query);
                if ($result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                        <div class="announcement-card">
                            <?php if ($row['image']): ?>
                                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" alt="">
                            <?php endif; ?>
                            <div class="text">
                                <h3><?= htmlspecialchars($row['title']) ?></h3>
                                <p><?= nl2br(htmlspecialchars($row['message'])) ?></p>
                                <small>By <?= htmlspecialchars($row['author']) ?> | <?= htmlspecialchars($row['created_at']) ?></small>

                                <form action="process_announcements.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn delete-btn">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile;
                else: ?>
                    <p>No announcements yet.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('createBtn').addEventListener('click', () => {
            document.getElementById('createForm').classList.toggle('hidden');
        });
    </script>

</body>

</html>