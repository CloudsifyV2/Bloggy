<?php
// Get first letter of username (fallback to "?" if not set)
$userInitial = isset($_SESSION['username']) && $_SESSION['username'] !== ''
    ? strtoupper(substr($_SESSION['username'], 0, 1))
    : 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/nav.css">
</head>
<body>
    <nav class="navbar" id="navbar">
        <div class="navbar-container">
            <a href="hub.php" class="navbar-logo">
                <span>Bloggy</span>
            </a>
            
            <?php
            $currentPage = basename($_SERVER['PHP_SELF']);
            ?>
            <ul class="navbar-menu">
                <li><a href="hub.php" class="<?= $currentPage === 'hub.php' ? 'active' : '' ?>">Home</a></li>
                <li><a href="create_post.php" class="<?= $currentPage === 'create_post.php' ? 'active' : '' ?>">Create</a></li>
                <li><a href="find.php" class="<?= $currentPage === 'find.php' ? 'active' : '' ?>">Find</a></li>
                <li><a href="manage.php" class="<?= $currentPage === 'manage.php' ? 'active' : '' ?>">Manage</a></li>
            </ul>

            <div class="navbar-actions">
                <button class="navbar-search" aria-label="Search">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>

                <div class="user-menu">
                    <div class="user-avatar" id="userAvatar"><?= htmlspecialchars($userInitial) ?></div>
                    <div class="dropdown-menu" id="userDropdown">
                        <a href="profile.php">Profile</a>
                        <a href="settings.php">Settings</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>
            </div>

            <button class="navbar-toggle" id="navbarToggle" aria-label="Toggle menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </nav>

    <?php if (isset($_SESSION['error']) && $_SESSION['error'] !== ''): ?>
        <div class="error-box" id="errorBox">
            <span class="error-message"><?= htmlspecialchars($_SESSION['error']); ?></span>
            <button class="error-close" id="closeError">&times;</button>
        </div>
    <?php 
        unset($_SESSION['error']); 
    endif; 
    ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggle = document.querySelector('.navbar-toggle');
            const menu = document.querySelector('.navbar-menu');
            const avatar = document.getElementById('userAvatar');
            const dropdown = document.getElementById('userDropdown');

            if (toggle && menu) {
                toggle.addEventListener('click', () => {
                    toggle.classList.toggle('active');
                    menu.classList.toggle('active');
                });
            }

            if (avatar && dropdown) {
                avatar.addEventListener('click', (e) => {
                    e.stopPropagation();
                    dropdown.classList.toggle('show');
                });

                document.addEventListener('click', (e) => {
                    if (!dropdown.contains(e.target) && e.target !== avatar) {
                        dropdown.classList.remove('show');
                    }
                });
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            const closeError = document.getElementById('closeError');
            const errorBox = document.getElementById('errorBox');

            if (closeError && errorBox) {
                closeError.addEventListener('click', () => {
                    errorBox.style.opacity = "0";
                    errorBox.style.transform = "translateY(-6px)";
                    setTimeout(() => errorBox.remove(), 200);
                });
            }
        });
    </script>
</body>
</html>
