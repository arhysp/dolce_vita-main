<?php
include __DIR__ . '/../includes/db.php';
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_POST['due_id'], $_POST['resident_id'], $_POST['action'])) {
    exit('Invalid request');
}

$due_id = (int)$_POST['due_id'];
$resident_id = (int)$_POST['resident_id'];
$action = $_POST['action'];

if ($action === 'approve') {
    $stmt = $conn->prepare("UPDATE dues SET resident_receipt_status='Approved', approved_at=NOW() WHERE due_id=? AND resident_id=?");
    $stmt->bind_param('ii', $due_id, $resident_id);
    $stmt->execute();
} elseif ($action === 'reject') {
    $admin_comments = $_POST['admin_comments'] ?? '';
    $stmt = $conn->prepare("UPDATE dues SET resident_receipt_status='Rejected', admin_comments=? WHERE due_id=? AND resident_id=?");
    $stmt->bind_param('sii', $admin_comments, $due_id, $resident_id);
    $stmt->execute();
} else {
    exit('Invalid action');
}

header("Location: admin_pending_resident_receipts.php?resident_id={$resident_id}");
exit;
