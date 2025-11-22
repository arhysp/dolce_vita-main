<?php
include __DIR__ . '/../includes/db.php';
session_start();
date_default_timezone_set('Asia/Manila');
include 'admin_header.php';

$resident_id = isset($_GET['resident_id']) ? (int)$_GET['resident_id'] : 0;

$stmt = $conn->prepare("SELECT due_id, month, year, resident_receipt_path, resident_receipt_status, admin_comments 
                        FROM dues 
                        WHERE resident_id=? AND resident_receipt_path IS NOT NULL AND resident_receipt_status='Pending' 
                        ORDER BY year DESC, month DESC");
$stmt->bind_param('i', $resident_id);
$stmt->execute();
$dues = $stmt->get_result();
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Pending Resident Receipts</title>
    <link rel="stylesheet" href="../assets/css/admin_manage_dues.css">
</head>

<body>
    <div class="dashboard-container">
        <div class="dashboard-card">
            <div class="dashboard-header">
                <a class="btn-back" href="admin_manage_dues.php?resident_id=<?php echo $resident_id; ?>">&larr; Back</a>
                <h2>Pending Resident Receipts</h2>
            </div>
            <div class="table-responsive">
                <table class="dues-table-modern">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Receipt</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($dues && $dues->num_rows > 0): while ($d = $dues->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('F', mktime(0, 0, 0, $d['month'], 1)) . " " . $d['year']; ?></td>
                                    <td>
                                        <button class="btn-view-receipt" data-path="<?php echo htmlspecialchars($d['resident_receipt_path']); ?>">View</button>
                                        <a href="<?php echo htmlspecialchars($d['resident_receipt_path']); ?>" target="_blank">Open</a>
                                    </td>
                                    <td>
                                        <form action="admin_approve_resident_receipt.php" method="POST" style="display:inline-block;">
                                            <input type="hidden" name="due_id" value="<?php echo $d['due_id']; ?>">
                                            <input type="hidden" name="resident_id" value="<?php echo $resident_id; ?>">
                                            <button type="submit" name="action" value="approve" class="btn-status-paid">Approve</button>
                                        </form>
                                        <form action="admin_approve_resident_receipt.php" method="POST" style="display:inline-block;">
                                            <input type="hidden" name="due_id" value="<?php echo $d['due_id']; ?>">
                                            <input type="hidden" name="resident_id" value="<?php echo $resident_id; ?>">
                                            <input type="text" name="admin_comments" placeholder="Comment" required>
                                            <button type="submit" name="action" value="reject" class="btn-status-unpaid">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="3">No pending receipts.</td>
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
            <div id="receiptPreview"></div>
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