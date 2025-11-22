<?php
include __DIR__ . '/../includes/db.php';
session_start();

date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}
$currentPage = 'admin dues';

$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$residents = false;

if ($search !== '') {
    $sql = "SELECT resident_id, first_name, middle_name, last_name, blk_number, lot_number FROM residents WHERE status='approved'";
    $sql .= " AND (first_name LIKE '%$search%' OR last_name LIKE '%$search%' OR blk_number LIKE '%$search%' OR lot_number LIKE '%$search%')";
    $sql .= " ORDER BY last_name, first_name";
    $residents = $conn->query($sql);
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Admin — Residents Dues</title>
    <link rel="stylesheet" href="../assets/css/admin_dues.css">
</head>

<body>
    <?php include 'admin_header.php'; ?>

    <aside class="sidebar">
        <ul>
            <li><a href="admin_residents.php">Manage Residents Accounts</a></li>
            <li><a href="admin_announcements.php">Post Announcements</a></li>
            <li><a href="admin_complaints.php">View Complaints</a></li>
            <li><a href="admin_dues.php" class="active">Manage Dues</a></li>
        </ul>
    </aside>

    <div class="dues-container">
        <h1>Residents — Dues Management</h1>

        <form method="GET" class="search-form">
            <input type="text" name="search" placeholder="Search by name or lot number or blk number..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>

        <?php if ($search !== ''): ?>
            <?php if ($residents && $residents->num_rows > 0): ?>
                <table class="residents-table">
                    <thead>
                        <tr>
                            <th>Resident</th>
                            <th>Lot Number</th>
                            <th>Blk Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($r = $residents->fetch_assoc()):
                            $resident_id = (int)$r['resident_id'];
                            $full = htmlspecialchars($r['first_name'] . ' ' . ($r['middle_name'] ? $r['middle_name'] . ' ' : '') . $r['last_name']);
                        ?>
                            <tr>
                                <td><?php echo $full; ?></td>
                                <td><?php echo htmlspecialchars($r['lot_number']); ?></td>
                                <td><?php echo htmlspecialchars($r['blk_number']); ?></td>
                                <td><a class="manage-btn" href="admin_manage_dues.php?resident_id=<?php echo $resident_id; ?>">Manage Dues</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty">No residents found matching your search.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>

</html>