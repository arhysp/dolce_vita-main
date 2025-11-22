<?php
include __DIR__ . '/../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$amount = isset($_POST['amount']) ? $_POST['amount'] : '0';

if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid ID';
    exit;
}

$amountClean = number_format((float)str_replace(',', '', $amount), 2, '.', '');

$stmt = $conn->prepare("UPDATE dues SET amount = ? WHERE due_id = ? LIMIT 1");
$stmt->bind_param('di', $amountClean, $id);
$stmt->execute();

echo json_encode(['success' => true]);
