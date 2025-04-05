<?php
// Ensure session is started (might be called from db_connect already)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodShare Connect</title> <!-- Change Title -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Add Font Awesome or other icon library if needed -->
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <a href="index.php" class="logo">
                    <!-- <img src="assets/images/logo.png" alt="FoodShare Logo"> -->
                    FoodShare Connect
                </a>
                <ul class="nav-links">
                    <li><a href="index.php#how-it-works">How it Works</a></li>
                    <li><a href="index.php#impact">Impact</a></li>
                    <?php if (isset($_SESSION["user_id"])): ?>
                        <?php if ($_SESSION["role"] === 'donor'): ?>
                            <li><a href="donor_dashboard.php">Donor Dashboard</a></li>
                        <?php elseif ($_SESSION["role"] === 'volunteer'): ?>
                            <li><a href="volunteer_dashboard.php">Volunteer Dashboard</a></li>
                         <?php elseif ($_SESSION["role"] === 'admin'): ?>
                            <li><a href="admin/dashboard.php">Admin Panel</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li class="cta-nav"><a href="donor_register.php">Donate Food</a></li>
                        <li class="cta-nav"><a href="volunteer_register.php">Volunteer</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    <main> <!-- Main content starts here -->