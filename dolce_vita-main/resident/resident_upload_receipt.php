<?php
include __DIR__ . '/../includes/db.php';
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_POST['due_id']) || !isset($_FILES['receipt_file'])) {
    exit('Invalid request');
}

$due_id = (int)$_POST['due_id'];
$resident_id = (int)$_SESSION['resident_id'];

$file = $_FILES['receipt_file'];
$uploadDir = __DIR__ . '/../uploads/resident_receipts/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// Generate unique file name
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'resident_' . $due_id . '_' . time() . '.' . $ext;
$filepath = $uploadDir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    $dbPath = 'uploads/resident_receipts/' . $filename; // Path to save in DB

    // Reset admin comments & update receipt path & set status to Pending
    $stmt = $conn->prepare("
        UPDATE dues 
        SET resident_receipt_path = ?, resident_receipt_status = 'Pending', admin_comments = NULL 
        WHERE due_id = ? AND resident_id = ?
    ");
    $stmt->bind_param('sii', $dbPath, $due_id, $resident_id);
    $stmt->execute();

    header("Location: resident_dues.php");
    exit;
} else {
    exit('Failed to upload file.');
}
