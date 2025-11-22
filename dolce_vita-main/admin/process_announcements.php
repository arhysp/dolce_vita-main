<?php
include __DIR__ . '/../includes/db.php';
session_start();

// Only admins can post or delete
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit;
}

// Handle Delete Announcement
if (isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);

    // Get image name first to delete file if exists
    $stmt = $conn->prepare("SELECT image FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $announcement = $result->fetch_assoc();

    if ($announcement && !empty($announcement['image'])) {
        $imagePath = __DIR__ . '/uploads/' . $announcement['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete from database
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $_SESSION['success'] = 'Announcement deleted successfully!';
    header('Location: admin_announcements.php');
    exit;
}

//  Handle Create Announcement
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $author = 'Admin'; // always admin for this page

    $imageName = null;

    // Handle image upload if provided
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = __DIR__ . '/uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $imageName;

        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileType, $allowed)) {
            move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
        } else {
            $_SESSION['error'] = 'Invalid image file type.';
            header('Location: admin_announcements.php');
            exit;
        }
    }

    // Insert announcement
    $stmt = $conn->prepare("INSERT INTO announcements (title, message, image, author) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $title, $message, $imageName, $author);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Announcement posted successfully!';
    } else {
        $_SESSION['error'] = 'Something went wrong. Please try again.';
    }

    $stmt->close();
    header('Location: admin_announcements.php');
    exit;
}
