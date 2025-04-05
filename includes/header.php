<!DOCTYPE html>
<html lang="en">
<head>
    <!-- CDN JS and CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="includes/header.css">

   
    <title>Responsive Navbar</title>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg bg-light">
  <div class="container-fluid">
    <a class="navbar-brand"  onclick="window.location.href='index.php'" href="#"><img src="./images/FoodShare.png" width=85, height =55 alt="Logo"></a>
    
    <!-- Navbar Toggler -->
    <button class="navbar-toggler" type="button" id="navbarToggler">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Navbar Links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul  class="navbar-nav me-auto"> 
       
        <li class="nav-item">
          <a id="nav-link" href="#how-it-works" class="nav-link" >About us</a>
        </li>

        <li class="nav-item">
          <a id="nav-link" href="#how-it-works" class="nav-link" >How it works</a>
        </li>
        
        <li class="nav-item">
          <a id="nav-link" href="#how-it-works"  class="nav-link">FAQs</a>
        </li>
      </ul>

      <!-- Buttons (Clicking will close navbar) -->
      <div class="d-flex">

      <ul class="navbar-nav">
      <?php if (isset($_SESSION["user_id"])): ?>
                        <?php if ($_SESSION["role"] === 'donor'): ?>
                            <li class="nav-item"><a class="nav-link" href="donor_dashboard.php">Donor Dashboard</a></li>
                        <?php elseif ($_SESSION["role"] === 'volunteer'): ?>
                            <li class="nav-item"><a class="nav-link" href="volunteer_dashboard.php">Volunteer Dashboard</a></li>
                         <?php elseif ($_SESSION["role"] === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin/dashboard.php">Admin Panel</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"> <button id="sign_btn" class="btn btn-outline-primary me-2" onclick="window.location.href='login.php'"  onclick="closeNavbar()">Login</button></li>
                        <li class="nav-item"><button type="button" onclick="window.location.href='login.php'" class="btn btn-warning btn-custom-orange" onclick="closeNavbar()">Sign Up</button></li>
                    <?php endif; ?>
      </ul>
      
      </div>
    </div>
  </div>
</nav>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
