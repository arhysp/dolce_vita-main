<?php
session_start();

// Check if admin or resident is logged in
$isAdmin = isset($_SESSION['admin_id']);
$isResident = isset($_SESSION['resident_id']);

// Set current page for navbar
$currentPage = 'about';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>About | Dolce Vita Community</title>
    <link rel="stylesheet" href="assets/css/about.css">
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


    <main class="about-section">
        <div class="blur-container">
            <div class="about-content">
                <h1>ABOUT US</h1>
                <p>
                    The <strong>Dolce Vita Community</strong> was created to bring residents together
                    in one interactive digital space. Our goal is to make communication, updates,
                    and community services easier and more accessible for everyone.
                </p>
                <p>
                    Through this platform, residents can stay informed
                    and take part in various community events and initiatives — all while fostering
                    a sense of unity and shared responsibility.
                </p>
                <p>
                    We believe in a modern and efficient community where every member enjoys
                    living the <em>sweet life</em> — <strong>Dolce Vita</strong>.
                </p>
                <a href="community.php" class="btn">Learn More</a>
            </div>
        </div>

        <!-- Team grid section -->
        <section class="team-section">
            <h2>Meet Our Team</h2>
            <div class="team-grid">
                <div class="team-card">
                    <img src="assets/profiles/poch.jpg" alt="Pochiolo Esguerra">
                    <h3>Pochiolo Esguerra</h3>
                    <p>Project Manager and Backend Developer</p>
                </div>
                <div class="team-card">
                    <img src="assets/profiles/alexa.jpg" alt="Alexa Pascual">
                    <h3>Alexa Pascual</h3>
                    <p>Document Designer and Presentation Specialist</p>
                </div>
                <div class="team-card">
                    <img src="assets/profiles/bert.jpg" alt="John Albert Dela Rosa">
                    <h3>John Albert Dela Rosa</h3>
                    <p>Quality Assurance Tester</p>
                </div>
                <div class="team-card">
                    <img src="assets/profiles/andrei.jpg" alt="Andrei Dizon">
                    <h3>Roshan Dizon</h3>
                    <p>Lead Writer and System Analyst</p>
                </div>
                <div class="team-card">
                    <img src="assets/profiles/kylle.jpg" alt="Kylle Real">
                    <h3>Kylle Real</h3>
                    <p>Frontend Developer</p>
                </div>
            </div>
        </section>
    </main>