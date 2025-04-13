<?php include 'includes/header.php'?>
<?php include 'includes/db_connect.php'; ?>

    <!-- banner -->
    
        <section class="hero">
    <div class="hero-content">
        <h1>Share Your Plate, Share Your Heart</h1>
        <p>Connect with volunteers to donate your surplus food to those in need in our community.</p>
        <div class="hero-buttons">
            <a href="donor_register.php" class="btn">Donate Food Now</a>
            <a href="volunteer_register.php" class="btn secondary">Become a Volunteer</a>
        </div>
    </div>
</section> 
</div> 


<section class="impact-section">
        <div class="impact-container">
            <!-- Left: World Map -->
            <div class="impact-map">
                <img class="img-fluid" src="./images/world3.png" alt="World Map">
            </div>

            <!-- Right: Impact Content -->
            <div class="impact-content">
                <h2>Our Impact So Far</h2>
                <p>FoodShare donations not only provide food in emergencies but also facilitate school feeding, nutrition support, cash transfers and resilience programs all over the world.</p>
                <a href="#" class="learn-more">Learn more ></a>
            </div>
        </div>
        <?php
            // --- PHP Logic to Fetch Real Stats (Example) ---
            $totalMeals = 0;
            $totalDonors = 0;
            $totalVolunteers = 0;

            try {
                // Example: Count delivered donations (assuming 'quantity' roughly maps to meals)
                // This is a simplification - you might need a better way to track meals
                $stmt = $pdo->query("SELECT COUNT(*) FROM donations WHERE status = 'delivered'");
                $totalMeals = $stmt->fetchColumn() * 5; // Example: Assume avg 5 meals per donation

                 // Count active donors (you might define 'active' differently)
                $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'donor'");
                $totalDonors = $stmt->fetchColumn();

                 // Count approved volunteers
                $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'volunteer' AND is_approved = 1");
                $totalVolunteers = $stmt->fetchColumn();

            } catch (PDOException $e) {
                // Log error, keep defaults
                error_log("Stats Query Failed: " . $e->getMessage());
                $totalMeals = 5000; // Fallback defaults
                $totalDonors = 150;
                $totalVolunteers = 75;
            }
        ?>

        <!-- Bottom: Stats Section -->
        <div class="impact-stats">
        <div class="stat-item">
                 <!-- data-target holds the real value, JS animates to it -->
                <span class="label">Meals Donated (Est.)</span>
                <span class="count" data-target="<?php echo $totalMeals; ?>">100</span>
            </div>
            <div class="stat-item">
                <span class="label">Generous Donors</span>
                <span class="count" data-target="<?php echo $totalDonors; ?>">10</span>
            </div>
            <div class="stat-item">
                <span class="label">Active Volunteers</span>
                <span class="count" data-target="<?php echo $totalVolunteers; ?>">7</span>
            </div>
        </div>
        </div>
    </section>

    <section class="working" id="how-it-works">
    <div class="container">
        <h2>How It Works</h2>
        <div class="steps-container"> <!-- Add styling for this container -->
            <div class="step">
                <h3>1. Donor Registers & Lists Food</h3>
                <p>Generous individuals or businesses sign up and list details about the surplus food they wish to donate.</p>
            </div>
            <div class="step">
                <h3>2. Volunteer Accepts Task</h3>
                <p>Registered and approved volunteers browse available donations and accept a pickup task.</p>
            </div>
            <div class="step">
                <h3>3. Food is Collected & Delivered</h3>
                <p>The volunteer collects the food from the donor and delivers it safely to designated distribution points or directly to those in need.</p>
            </div>
             <div class="step">
                <h3>4. Impact is Made!</h3>
                <p>Less food waste, more meals for the hungry. Everyone wins!</p>
            </div>
        </div>
    </div>
</section>


    <!-- App Store Badges Section -->
    <!-- <section class="app-badges">
        <div class="container text-center">
            <h2>Download Our App</h2>
            <p>Available on iOS and Android</p>
            <img src="./images/app-store-badge.png" alt="App Store Badge" class="store-badge">
            <img src="./images/google-play-badge.png" alt="Google Play Badge" class="store-badge">
        </div>
    </section> 
                    <style>
                    .store-badge {
                        width: 150px;
                        height: auto; 
                    }
                    </style> -->


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="./js/script.js"></script>

<!-- Scroll to Top Button -->
<?php  include 'includes/scrolling.php' ?>
<!-- footer -->
<?php include './includes/footer.php' ?>