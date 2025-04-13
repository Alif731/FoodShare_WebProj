<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed people in need - FoodShare Connect</title>

    

    <!-- Favicons -->
    <link rel="icon" sizes="any" type="image/svg+xml" href="./images/Fav.svg"> <!-- Use SVG for favicon if possible -->
   <!-- <link rel="icon" sizes="512x512" href="./images/fav.png" type="image/png"> -->
   <!-- <link rel="apple-touch-icon" href="./images/fav.png" type="image/png"> -->

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Font Awesome (for footer icons primarily) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Optional: Bootstrap Icons (good alternative/addition) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Your Custom CSS (Load AFTER Bootstrap if needed for overrides) -->
    <link rel="stylesheet" href="./css/index.css">

    
   

</head>
<body class="d-flex flex-column min-vh-100">

<!-- Navbar using Bootstrap -->
<nav class="navbar navbar-expand-lg bg-light shadow-sm sticky-top">
  <div class="container"> <!-- Use .container for centered content or .container-fluid for full width -->
    <a class="navbar-brand" href="index.php">
        <img src="./images/FoodShare_logo.svg" alt="FoodShare Connect Logo" style="height: 100px; width: auto;"> <!-- Use SVG if possible -->
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" href="index.php#about-us">About Us</a> <!-- Updated href -->
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php#how-it-works">How it works</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="index.php#faqs">FAQs</a> <!-- Updated href -->
        </li>
      </ul>

      <!-- Right side actions -->
      <div class="d-flex align-items-center">
            <?php if (isset($_SESSION["user_id"])): ?>
                <?php // Logged-in User Dropdown ?>
                <div class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="userNavDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                  </a>
                  <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userNavDropdown">
                    <?php if ($_SESSION["role"] === 'donor'): ?>
                        <li><a class="dropdown-item" href="donor_dashboard.php"><i class="bi bi-speedometer2 me-2"></i>Donor Dashboard</a></li>
                    <?php elseif ($_SESSION["role"] === 'volunteer'): ?>
                        <li><a class="dropdown-item" href="volunteer_dashboard.php"><i class="bi bi-person-check me-2"></i>Volunteer Dashboard</a></li>
                    <?php elseif ($_SESSION["role"] === 'admin'): ?>
                        <li><a class="dropdown-item" href="admin/dashboard.php"><i class="bi bi-gear-fill me-2"></i>Admin Panel</a></li>
                    <?php endif; ?>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                  </ul>
                </div>
            <?php else: ?>
                <?php // Logged-out User Buttons ?>
                <a href="login.php" class="btn btn-outline-primary btn-signin">Login</a>
                <a href="signup.php" class="btn btn-warning btn-signup">Sign Up</a> <!-- Consider using btn-warning directly -->
            <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<!-- Main content starts here -->
<main class="flex-grow-1">