<?php
session_start();

// Check if admin or resident is logged in
$isAdmin = isset($_SESSION['admin_id']);
$isResident = isset($_SESSION['resident_id']);

// Set current page for navbar
$currentPage = 'community';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Community - Dolce Vita Portal</title>
    <link rel="stylesheet" href="assets/css/community.css">
</head>

<body>

    <?php
    if ($isAdmin) {
        include 'admin/admin_header.php';
    } elseif ($isResident) {
        include 'resident/resident_header.php';
    } else {
        include 'includes/header.php';
    }
    ?>

    <!-- Add this wrapper with padding -->
    <main class="main-content">
        <div class="blur-container">
            <section class="community-section">
                <div class="intro">
                    <h1>Welcome to Our Community</h1>
                    <p>
                        The Dolce Vita Community Portal is a space for collaboration, sharing experiences, and building meaningful connections.
                        We value every member’s voice — here’s what people have to say.
                    </p>
                </div>

                <div class="feedbacks">
                    <h2>Community Feedback</h2>
                    <div class="feedback-list">
                        <div class="feedback">
                            <h3>Bok Barboza</h3>
                            <p class="role">Resident</p>
                            <p class="message">
                                “I love how this platform connects people with the same goals. Everyone here is so supportive and kind!”
                            </p>
                        </div>

                        <div class="feedback">
                            <h3>Ricardo Manzano</h3>
                            <p class="role">Resident</p>
                            <p class="message">
                                “The events and projects here inspire real change. It’s great to see everyone working together.”
                            </p>
                        </div>

                        <div class="feedback">
                            <h3>Dionisio Almarez</h3>
                            <p class="role">Resident</p>
                            <p class="message">
                                “I joined recently and already feel welcome. The team responds quickly and values every opinion!”
                            </p>
                        </div>
                    </div>
                </div>

                <div class="feedback-form">
                    <h2>Share Your Feedback</h2>
                    <form>
                        <input type="text" placeholder="Your Name" required>
                        <input type="email" placeholder="Your Email" required>
                        <textarea placeholder="Write your feedback..." required></textarea>
                        <button type="submit">Submit Feedback</button>
                    </form>
                </div>
        </div>
        </section>
    </main>

</body>

</html>