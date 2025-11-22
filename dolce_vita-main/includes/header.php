<header class="navbar">
    <div class="logo">
        <img src="/dolcevita/assets/background/logo.jpg"> <span class="logo-text">Dolce Vita Community Portal</span>
    </div>
    <nav>
        <ul>
            <?php
            $currentPage = $currentPage ?? '';
            $links = [
                'home' => ['label' => 'Home', 'url' => '/dolcevita/index.php'],
                'about' => ['label' => 'About', 'url' => '/dolcevita/about.php'],
                'community' => ['label' => 'Community', 'url' => '/dolcevita/community.php'],
                'login' => ['label' => 'Login', 'url' => '/dolcevita/login.php']
            ];
            foreach ($links as $key => $link):
                if ($currentPage !== $key):
            ?>
                    <li><a href="<?= $link['url'] ?>"><?= $link['label'] ?></a></li>
            <?php endif;
            endforeach; ?>
        </ul>
    </nav>
</header>