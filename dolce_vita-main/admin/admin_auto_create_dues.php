<?php
include __DIR__ . '/../includes/db.php';
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_POST['resident_id'], $_POST['month'], $_POST['year'])) {
    exit('Invalid request');
}

$resident_id = (int)$_POST['resident_id'];
$month = (int)$_POST['month'];
$year = (int)$_POST['year'];

// Check if due already exists
$stmt = $conn->prepare("SELECT due_id FROM dues WHERE resident_id=? AND month=? AND year=? LIMIT 1");
$stmt->bind_param('iii', $resident_id, $month, $year);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    echo 'exists';
    exit;
}

// Insert new due
$stmt = $conn->prepare("INSERT INTO dues (resident_id, month, year, status, amount) VALUES (?, ?, ?, 'Unpaid', 0)");
$stmt->bind_param('iii', $resident_id, $month, $year);
if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error';
}
