<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$currentPage = $currentPage ?? '';

$links = [
    'home' => ['label' => 'Home', 'url' => '/dolcevita/admin/admin_dashboard.php'],
    'about' => ['label' => 'About', 'url' => '/dolcevita/about.php'],
    'community' => ['label' => 'Community', 'url' => '/dolcevita/community.php'],
    'logout' => ['label' => 'Logout', 'url' => '/dolcevita/logout.php']
];
?>

<header class="navbar">
    <div class="logo">
        <img src="/dolcevita/assets/background/logo.jpg"> <span class="logo-text">Dolce Vita Community Portal</span>
    </div>
    <nav>
        <ul>
            <?php foreach ($links as $key => $link):
                if ($currentPage !== $key):
            ?>
                    <li><a href="<?= $link['url'] ?>"><?= $link['label'] ?></a></li>
            <?php endif;
            endforeach; ?>
        </ul>
    </nav>
</header>