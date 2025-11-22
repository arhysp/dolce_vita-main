<?php
include __DIR__ . '/../includes/db.php';
session_start();
date_default_timezone_set('Asia/Manila');
include 'admin_header.php';

if (!isset($_GET['resident_id'])) {
    header('Location: admin_dues.php');
    exit;
}
$resident_id = (int)$_GET['resident_id'];

// Fetch resident info
$st = $conn->prepare("SELECT resident_id, first_name, middle_name, last_name, lot_number, blk_number 
                      FROM residents WHERE resident_id = ? AND status='approved' LIMIT 1");
$st->bind_param('i', $resident_id);
$st->execute();
$res = $st->get_result();
if ($res->num_rows === 0) {
    echo "Resident not found or not approved.";
    exit;
}
$resident = $res->fetch_assoc();

$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$monthToCreate = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');

// Auto-create current month if missing
$chk2 = $conn->prepare("SELECT due_id FROM dues WHERE resident_id=? AND month=? AND year=? LIMIT 1");
$chk2->bind_param('iii', $resident_id, $monthToCreate, $year);
$chk2->execute();
$rchk2 = $chk2->get_result();
if ($rchk2->num_rows === 0) {
    $ins2 = $conn->prepare("INSERT INTO dues (resident_id, month, year, amount, status) VALUES (?, ?, ?, 0, 'Unpaid')");
    $ins2->bind_param('iii', $resident_id, $monthToCreate, $year);
    $ins2->execute();
}

// Fetch all dues for the resident
$dq = $conn->prepare("SELECT * FROM dues WHERE resident_id = ? AND year = ? ORDER BY month ASC");
$dq->bind_param('ii', $resident_id, $year);
$dq->execute();
$dues = $dq->get_result();

function fullname($r)
{
    return htmlspecialchars($r['first_name'] . ' ' . ($r['middle_name'] ? $r['middle_name'] . ' ' : '') . $r['last_name']);
}

// Count pending resident receipts
$pendingStmt = $conn->prepare("SELECT COUNT(*) as pending_count FROM dues WHERE resident_id = ? AND resident_receipt_path IS NOT NULL AND resident_receipt_status='Pending'");
$pendingStmt->bind_param('i', $resident_id);
$pendingStmt->execute();
$pendingCount = $pendingStmt->get_result()->fetch_assoc()['pending_count'];
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Manage Dues — <?php echo fullname($resident); ?></title>
    <link rel="stylesheet" href="../assets/css/admin_manage_dues.css">
</head>

<body>
    <div class="dashboard-container">
        <div class="dashboard-card">
            <div class="dashboard-header">
                <a class="btn-back" href="admin_dues.php">&larr; Residents</a>
                <h2>Manage Dues</h2>
                <div class="resident-meta">
                    <div class="resident-name"><?php echo fullname($resident); ?></div>
                    <div class="resident-location">Lot <?php echo htmlspecialchars($resident['lot_number']); ?>, Block <?php echo htmlspecialchars($resident['blk_number']); ?></div>
                </div>
            </div>

            <!-- UPDATED FORM WITH BOTH BUTTONS SIDE BY SIDE -->
            <form method="GET" class="dues-filters"
                style="display:flex; align-items:center; gap:15px; flex-wrap:wrap;">

                <input type="hidden" name="resident_id" value="<?php echo $resident_id; ?>">

                <div>
                    <label>Year</label>
                    <select name="year" onchange="this.form.submit()">
                        <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                            <option value="<?php echo $y; ?>" <?php if ($y == $year) echo 'selected'; ?>>
                                <?php echo $y; ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div>
                    <label>Month</label>
                    <select name="month">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?php echo $m; ?>" <?php if ($m == $monthToCreate) echo 'selected'; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <!-- Create Missing Month -->
                <button id="create-missing"
                    data-resident="<?php echo $resident_id; ?>"
                    class="btn-add">
                    Create Missing Month
                </button>

                <!-- Pending Receipts Button (NOW BESIDE CREATE MONTH) -->
                <a href="admin_pending_resident_receipts.php?resident_id=<?php echo $resident_id; ?>"
                    class="btn-add">
                    Pending Resident Receipts (<?php echo $pendingCount; ?>)
                </a>

            </form>
            <!-- END UPDATED FORM -->

            <div class="table-responsive">
                <table class="dues-table-modern">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Amount (₱)</th>
                            <th>Status</th>
                            <th>Date Paid</th>
                            <th>Admin Receipt</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($dues && $dues->num_rows > 0): while ($d = $dues->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo date('F', mktime(0, 0, 0, $d['month'], 1)) . " " . $d['year']; ?></td>
                                    <td style="<?php if ($d['status'] === 'Paid') echo 'background:#e5ffe5;color:#249d4a;font-weight:bold;'; ?>">
                                        &#8369; <?php echo number_format($d['status'] === 'Paid' ? $d['amount_paid'] : $d['amount'], 2); ?>
                                    </td>
                                    <td><span class="badge <?php echo strtolower($d['status']); ?>"><?php echo $d['status']; ?></span></td>
                                    <td><?php echo $d['date_paid'] ?? '-'; ?></td>
                                    <td>
                                        <?php if ($d['admin_receipt_path']): ?>
                                            <button class="btn-view-receipt" data-path="<?php echo htmlspecialchars($d['admin_receipt_path']); ?>">View</button>
                                            <a class="btn-open" href="<?php echo htmlspecialchars($d['admin_receipt_path']); ?>" target="_blank">Open</a>
                                        <?php else: ?>
                                            <span class="badge badge-empty">No Receipt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <!-- Admin upload receipt -->
                                        <form action="admin_upload_receipt.php" method="POST" enctype="multipart/form-data" style="margin-bottom:7px;">
                                            <input type="hidden" name="due_id" value="<?php echo $d['due_id']; ?>">
                                            <input type="hidden" name="resident_id" value="<?php echo $resident_id; ?>">
                                            <input type="file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf" required>
                                            <button type="submit" class="btn-action-upload">Upload</button>
                                        </form>

                                        <!-- Mark as paid/unpaid -->
                                        <?php if ($d['status'] === 'Unpaid'): ?>
                                            <form action="admin_update_dues_status.php" method="POST">
                                                <input type="hidden" name="action" value="paid">
                                                <input type="hidden" name="id" value="<?php echo $d['due_id']; ?>">
                                                <input type="hidden" name="resident_id" value="<?php echo $resident_id; ?>">
                                                <input type="number" step="0.01" name="amount_paid" required placeholder="Amount Paid">
                                                <button type="submit" class="btn-status-paid">Mark as Paid</button>
                                            </form>
                                        <?php else: ?>
                                            <form action="admin_update_dues_status.php" method="POST">
                                                <input type="hidden" name="action" value="unpaid">
                                                <input type="hidden" name="id" value="<?php echo $d['due_id']; ?>">
                                                <input type="hidden" name="resident_id" value="<?php echo $resident_id; ?>">
                                                <button type="submit" class="btn-status-unpaid">Set as Unpaid</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="6">No dues found. Use "Create Missing Month" to add.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
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

        // Create missing month
        document.getElementById('create-missing')?.addEventListener('click', function(e) {
            e.preventDefault();
            const residentId = this.dataset.resident;
            const form = this.closest('form');
            const month = form.querySelector('select[name="month"]').value;
            const year = form.querySelector('select[name="year"]').value;
            fetch('admin_auto_create_dues.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        resident_id: residentId,
                        month: month,
                        year: year
                    })
                }).then(res => res.text())
                .then(data => {
                    if (data.includes('success'))
                        location.href = `admin_manage_dues.php?resident_id=${residentId}&year=${year}`;
                    else alert(data);
                }).catch(err => alert('Request failed: ' + err));
        });
    </script>
</body>

</html>