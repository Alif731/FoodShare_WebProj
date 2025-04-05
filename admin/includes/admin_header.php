<?php
// Force HTTPS only if it's detected/required for production
// if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
//     $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//     header('HTTP/1.1 301 Moved Permanently');
//     header('Location: ' . $location);
//     exit;
// }

// Ensure session is started AND includes the DB connection
// The '../' is crucial because this file is inside admin/includes/
require_once dirname(__DIR__, 2) . '/includes/db_connect.php'; // Go up two levels for main includes

// --- ADMIN AUTHENTICATION CHECK ---
if (!isset($_SESSION["user_id"]) || !isset($_SESSION["role"]) || $_SESSION["role"] !== 'admin') {
    // Not logged in as an admin, redirect to main login
    header("Location: ../login.php?error=unauthorized"); // Use '../' to go up one level
    exit;
}
// --- END ADMIN AUTHENTICATION CHECK ---

// Optional: Regenerate session ID periodically for added security
// if (!isset($_SESSION['last_regen'])) $_SESSION['last_regen'] = time();
// if (time() - $_SESSION['last_regen'] > 1800) { // e.g., every 30 minutes
//     session_regenerate_id(true);
//     $_SESSION['last_regen'] = time();
// }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - FoodShare Connect</title>
    <!-- Link to main styles and admin-specific styles -->
    <link rel="stylesheet" href="../css/style.css"> <!-- Go up one level -->
    <link rel="stylesheet" href="../css/admin_style.css"> <!-- Go up one level -->
     <!-- Add Font Awesome or icon library if desired -->
</head>
<body>
    <header class="admin-header">
        <nav class="navbar">
            <div class="container">
                <a href="dashboard.php" class="logo">Admin Panel</a>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="manage_volunteers.php">Volunteers</a></li>
                    <li><a href="manage_donations.php">Donations</a></li>
                    <li><a href="manage_users.php">Users</a></li>
                    <li><a href="../logout.php">Logout (<?php echo htmlspecialchars($_SESSION['first_name']); ?>)</a></li>
                </ul>
            </div>
        </nav>
    </header>
    <main class="admin-main"> <!-- Main content starts here -->
        <div class="container"> <!-- Admin content often uses full width or specific container -->