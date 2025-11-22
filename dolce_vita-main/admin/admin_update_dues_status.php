<?php
include __DIR__ . '/../includes/db.php';
session_start();
date_default_timezone_set('Asia/Manila');

if (!isset($_POST['action'], $_POST['id'], $_POST['resident_id'])) {
    exit('Invalid request');
}

$action = $_POST['action'];
$due_id = (int)$_POST['id'];
$resident_id = (int)$_POST['resident_id'];
$year = isset($_POST['year']) ? (int)$_POST['year'] : (int)date('Y');

if ($action === 'paid') {
    if (!isset($_POST['amount_paid']) || !is_numeric($_POST['amount_paid'])) {
        exit('Amount required.');
    }
    $amount_paid = (float)$_POST['amount_paid'];
    $date_paid = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("UPDATE dues SET status='Paid', amount_paid=?, date_paid=? WHERE due_id=? AND resident_id=?");
    $stmt->bind_param('dsii', $amount_paid, $date_paid, $due_id, $resident_id);
    $stmt->execute();
} elseif ($action === 'unpaid') {
    $stmt = $conn->prepare("UPDATE dues SET status='Unpaid', amount_paid=NULL, date_paid=NULL WHERE due_id=? AND resident_id=?");
    $stmt->bind_param('ii', $due_id, $resident_id);
    $stmt->execute();
} else {
    exit('Invalid action');
}

header("Location: admin_manage_dues.php?resident_id={$resident_id}&year={$year}");
exit;
