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
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- Main and Admin Styles -->
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="admincss/admin_style.css">
    <!-- Select2 Styles and Scripts -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body class="admin-body">

    <div class="admin-wrapper">

        <!-- ===== Sidebar ===== -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="sidebar-logo">FoodShare Admin</a>
                <!-- Optional: Add a toggle button for smaller screens later -->
            </div>

            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'dashboard.php') ? 'active' : ''; ?>">
                            <i class="fa-solid fa-gauge-high fa-fw"></i> <!-- Icon -->
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="manage_volunteers.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_volunteers.php') ? 'active' : ''; ?>">
                           <i class="fa-solid fa-hand-holding-hand fa-fw"></i> <!-- Icon -->
                           <span>Volunteers</span>
                        </a>
                    </li>
                    <li>
                        <a href="manage_donations.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_donations.php') ? 'active' : ''; ?>">
                           <i class="fa-solid fa-box-open fa-fw"></i> <!-- Icon -->
                           <span>Donations</span>
                        </a>
                    </li>
                     <li>
                        <a href="manage_users.php" class="<?php echo (basename($_SERVER['PHP_SELF']) == 'manage_users.php') ? 'active' : ''; ?>">
                            <i class="fa-solid fa-users fa-fw"></i> <!-- Icon -->
                            <span>Users</span>
                        </a>
                    </li>
                     <!-- Add more sections like Settings, Reports etc. as needed -->
                     <li class="sidebar-separator"></li> <!-- Optional Separator -->
                     <li>
                        <a href="../logout.php">
                           <i class="fa-solid fa-right-from-bracket fa-fw"></i> <!-- Icon -->
                           <span>Logout</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        <!-- ===== End Sidebar ===== -->

        <!-- ===== Main Content ===== -->
        <div class="admin-main-content">
            <!-- Top Bar -->
            <header class="admin-topbar">
                <div class="topbar-left">
                    <!-- Hamburger for mobile - Needs JS -->
                    <button class="sidebar-toggle" id="sidebarToggle"><i class="fa-solid fa-bars"></i></button>
                    <!-- Breadcrumbs (Example - Make dynamic if needed) -->
                    <nav aria-label="breadcrumb">
                      <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            <?php // Simple breadcrumb based on filename
                                $page_title = ucfirst(str_replace(['manage_', '.php'], ['', ''], basename($_SERVER['PHP_SELF'])));
                                echo htmlspecialchars($page_title);
                            ?>
                        </li>
                      </ol>
                    </nav>
                </div>
                <div class="topbar-right">
                    <div class="user-profile">
                        <i class="fa-solid fa-user-circle"></i> <!-- User Icon -->
                        <span><?php echo htmlspecialchars($_SESSION['first_name']); ?></span>
                        <!-- Add dropdown later if needed -->
                    </div>
                </div>
            </header>
            <!-- End Top Bar -->

            <!-- Page Content Start -->
            <div class="admin-content-area">
                <!-- The specific page content (like tables) will go here -->