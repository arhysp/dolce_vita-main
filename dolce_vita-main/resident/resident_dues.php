<?php
include __DIR__ . '/../includes/db.php';
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_SESSION['resident_id'])) {
    header('Location: login.php');
    exit;
}

$resident_id = (int)$_SESSION['resident_id'];

// fetch resident
$st = $conn->prepare("SELECT resident_id, first_name, middle_name, last_name, lot_number, blk_number FROM residents WHERE resident_id = ? AND status='approved' LIMIT 1");
$st->bind_param('i', $resident_id);
$st->execute();
$res = $st->get_result();
if ($res->num_rows === 0) {
    echo "Resident not found or not approved.";
    exit;
}
$resident = $res->fetch_assoc();

$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$monthToCheck = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');

// Fetch dues for the resident
$dq = $conn->prepare("SELECT * FROM dues WHERE resident_id = ? AND year = ? ORDER BY month ASC");
$dq->bind_param('ii', $resident_id, $year);
$dq->execute();
$dues = $dq->get_result();

function fullname($r)
{
    return htmlspecialchars($r['first_name'] . ' ' . ($r['middle_name'] ? $r['middle_name'] . ' ' : '') . $r['last_name']);
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>My Dues — <?php echo fullname($resident); ?></title>
    <link rel="stylesheet" href="../assets/css/resident_dues.css">
</head>

<body>
    <?php include 'resident_header.php'; ?>
    <aside class="sidebar">
        <ul>
            <li><a href="resident_announcements.php">View Announcements</a></li>
            <li><a href="resident_complaints.php">Post Complaints</a></li>
            <li><a href="resident_dues.php" class="active">View Dues</a></li>
        </ul>
    </aside>

    <div class="dashboard-container">
        <div class="dashboard-card">
            <div class="dashboard-header">
                <h2>My Dues</h2>
                <div class="resident-meta">
                    <div class="resident-name"><?php echo fullname($resident); ?></div>
                    <div class="resident-location">Lot <?php echo htmlspecialchars($resident['lot_number']); ?>, Block <?php echo htmlspecialchars($resident['blk_number']); ?></div>
                </div>
            </div>

            <form method="GET" class="dues-filters">
                <div>
                    <label>Year</label>
                    <select name="year" onchange="this.form.submit()">
                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php if ($y == $year) echo 'selected'; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label>Month</label>
                    <select name="month" onchange="this.form.submit()">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php if ($m == $monthToCheck) echo 'selected'; ?>><?php echo date('F', mktime(0, 0, 0, $m, 1)); ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </form>

            <div class="table-responsive">
                <table class="dues-table-modern">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Amount (₱)</th>
                            <th>Status</th>
                            <th>Date Paid</th>
                            <th>Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $foundMonth = false;
                        if ($dues && $dues->num_rows > 0):
                            while ($d = $dues->fetch_assoc()):
                                if ($d['month'] == $monthToCheck) $foundMonth = true;
                                $receiptPath = $d['resident_receipt_path'] ?? null;
                                $receiptStatus = $d['resident_receipt_status'] ?? null;
                                $adminComments = $d['admin_comments'] ?? null;
                        ?>
                                <tr>
                                    <td><?php echo date('F', mktime(0, 0, 0, $d['month'], 1)) . " " . $d['year']; ?></td>
                                    <td style="<?php if ($d['status'] === 'Paid') echo 'background:#e5ffe5;color:#249d4a;font-weight:bold;'; ?>">
                                        &#8369; <?php echo number_format((float)($d['status'] === 'Paid' ? $d['amount_paid'] : $d['amount']), 2); ?>
                                    </td>
                                    <td><span class="badge <?php echo strtolower($d['status']); ?>"><?php echo htmlspecialchars($d['status']); ?></span></td>
                                    <td><?php echo $d['date_paid'] ?? '-'; ?></td>
                                    <td>
                                        <?php if ($receiptPath): ?>
                                            <span class="badge <?php echo strtolower($receiptStatus); ?>"><?php echo htmlspecialchars($receiptStatus); ?></span>
                                            <?php if ($adminComments): ?>
                                                <div class="admin-comments"><strong>Admin Comments:</strong> <?php echo htmlspecialchars($adminComments); ?></div>
                                            <?php endif; ?>
                                            <button class="btn-view-receipt" data-path="<?php echo htmlspecialchars($receiptPath); ?>">View</button>
                                            <a class="btn-open" href="<?php echo htmlspecialchars($receiptPath); ?>" target="_blank" rel="noopener">Open</a>
                                            <?php if ($receiptStatus === 'Pending' || $receiptStatus === 'Rejected'): ?>
                                                <form action="resident_upload_receipt.php" method="POST" enctype="multipart/form-data" style="margin-top:5px;">
                                                    <input type="hidden" name="due_id" value="<?php echo (int)$d['due_id']; ?>">
                                                    <input type="file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf" required>
                                                    <button type="submit" class="btn-action-upload">Re-upload</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <form action="resident_upload_receipt.php" method="POST" enctype="multipart/form-data">
                                                <input type="hidden" name="due_id" value="<?php echo (int)$d['due_id']; ?>">
                                                <input type="file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf" required>
                                                <button type="submit" class="btn-action-upload">Upload Receipt</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php
                            endwhile;
                            if (!$foundMonth):
                            ?>
                                <tr>
                                    <td colspan="5" class="no-data">No record found for <?php echo date('F', mktime(0, 0, 0, $monthToCheck, 1)) . " " . $year; ?>. Please check with admin.</td>
                                </tr>
                            <?php
                            endif;
                        else:
                            ?>
                            <tr>
                                <td colspan="5" class="no-data">No dues records for <?php echo $year; ?>.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="receiptModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span id="modalClose" class="modal-close">&times;</span>
            <div id="receiptPreview" class="receipt-preview"></div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('receiptModal');
        const preview = document.getElementById('receiptPreview');
        const modalClose = document.getElementById('modalClose');

        document.querySelectorAll('.btn-view-receipt').forEach(btn => {
            btn.addEventListener('click', () => {
                const path = btn.dataset.path;
                const fullPath = "<?php echo $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/dolcevita/'; ?>" + path;

                preview.innerHTML = '';
                const ext = path.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'].includes(ext)) {
                    const img = document.createElement('img');
                    img.src = fullPath;
                    img.style.maxWidth = '100%';
                    img.style.height = 'auto';
                    preview.appendChild(img);
                } else if (ext === 'pdf') {
                    const iframe = document.createElement('iframe');
                    iframe.src = fullPath;
                    iframe.style.width = '100%';
                    iframe.style.height = '80vh';
                    preview.appendChild(iframe);
                } else {
                    const a = document.createElement('a');
                    a.href = fullPath;
                    a.innerText = 'Open file in new tab';
                    a.target = '_blank';
                    preview.appendChild(a);
                }
                modal.style.display = 'block';

            });
        });

        modalClose.addEventListener('click', () => modal.style.display = 'none');
        window.addEventListener('click', e => {
            if (e.target === modal) modal.style.display = 'none';
        });
    </script>
</body>

</html>