<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentPage = $currentPage ?? '';

$links = [
    'home' => ['label' => 'Home', 'url' => '/dolcevita/resident/resident_dashboard.php'],
    'about' => ['label' => 'About', 'url' => '/dolcevita/about.php'],
    'community' => ['label' => 'Community', 'url' => '/dolcevita/community.php'],
    'logout' => ['label' => 'Logout', 'url' => '/dolcevita/logout.php']
];
?>

<header class="navbar main-header">
    <div class="logo">
        <img src="/dolcevita/assets/background/logo.jpg"> <span class="logo-text">Dolce Vita Community Portal</span>
    </div>
    <nav>
        <ul class="nav-links">
            <?php foreach ($links as $key => $link): ?>
                <li><a href="<?= htmlspecialchars($link['url']) ?>"
                        class="<?= $currentPage === $key ? 'active' : '' ?>">
                        <?= htmlspecialchars($link['label']) ?>
                    </a></li>
            <?php endforeach; ?>
        </ul>
    </nav>
</header>