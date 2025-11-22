<?php
include __DIR__ . '/../includes/db.php';
session_start();

$username = "poch";
$email = "chioloesguerra@gmail.com";
$password = "123456";  // temporary password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admins (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashed_password);

if ($stmt->execute()) {
    echo "Admin account created successfully.";
} else {
    echo "Error: " . $stmt->error;
}
