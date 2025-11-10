<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Dubz Adventours - Philippines Travel & Tours'; ?></title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container nav-container">
            <div class="nav-brand">
                <a href="index.php" class="brand-link">
                    <img src="attached_assets/dubz Official LOGO_1761442704294.png" alt="Dubz Adventours Logo" class="logo">
                    <span class="brand-name">Dubz Adventours</span>
                </a>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="tours.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tours.php' ? 'active' : ''; ?>">Tours</a></li>
                <li><a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About</a></li>
                <li><a href="contacts.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'contacts.php' ? 'active' : ''; ?>">Contact</a></li>
                <li><a href="chucknorris.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'chucknorris.php' ? 'active' : ''; ?>">Jokes</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li><a href="admin_dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                <?php endif; ?>
                <li><a href="logout.php" class="logout-btn">Logout</a></li>
            </ul>
        </div>
    </nav>
