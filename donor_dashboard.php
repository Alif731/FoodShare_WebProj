<?php
include 'includes/db_connect.php';

// Authentication Check: Ensure user is logged in and is a donor
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'donor') {
    header("Location: login.php");
    exit;
}

$donor_id = $_SESSION["user_id"];
$errors = [];
$success_message = '';

// --- Handle New Donation Form Submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_donation'])) {
    $food_description = trim($_POST['food_description'] ?? '');
    $quantity = trim($_POST['quantity'] ?? '');
    $pickup_address = trim($_POST['pickup_address'] ?? '');
    $pickup_time_preference = trim($_POST['pickup_time_preference'] ?? '');

    // Basic Validation
    if (empty($food_description)) $errors[] = "Food description is required.";
    if (empty($pickup_address)) $errors[] = "Pickup address is required.";
    // Add more validation...

    if (empty($errors)) {
        try {
            $sql = "INSERT INTO donations (donor_id, food_description, quantity, pickup_address, pickup_time_preference, status)
                    VALUES (:donor_id, :food_description, :quantity, :pickup_address, :pickup_time_preference, 'pending')";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':donor_id', $donor_id);
            $stmt->bindParam(':food_description', $food_description);
            $stmt->bindParam(':quantity', $quantity);
            $stmt->bindParam(':pickup_address', $pickup_address);
            $stmt->bindParam(':pickup_time_preference', $pickup_time_preference);

            if ($stmt->execute()) {
                $success_message = "Donation listed successfully!";
            } else {
                $errors[] = "Failed to list donation. Please try again.";
            }
        } catch (PDOException $e) {
            error_log("Donation submission failed: " . $e->getMessage());
            $errors[] = "An error occurred. Please try again later.";
        }
    }
}

// --- Fetch Donor's Existing Donations ---
$donations = [];
try {
    $stmt = $pdo->prepare("SELECT donation_id, food_description, quantity, status, created_at, assigned_volunteer_id FROM donations WHERE donor_id = :donor_id ORDER BY created_at DESC");
    $stmt->bindParam(':donor_id', $donor_id);
    $stmt->execute();
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetching donor donations failed: " . $e->getMessage());
    // Display an error message on the page if needed
}

// --- Fetch Donor's Default Address (Optional Prefill) ---
$default_address = '';
try {
     $stmt = $pdo->prepare("SELECT address, city, postal_code FROM users WHERE user_id = :donor_id");
     $stmt->bindParam(':donor_id', $donor_id);
     $stmt->execute();
     $user_info = $stmt->fetch(PDO::FETCH_ASSOC);
     if ($user_info) {
        $addr_parts = array_filter([$user_info['address'], $user_info['city'], $user_info['postal_code']]);
        $default_address = implode(', ', $addr_parts);
     }
} catch (PDOException $e) {
     error_log("Fetching donor address failed: " . $e->getMessage());
}

?>

<?php include 'includes/header.php'; ?>

<div class="container dashboard-container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! (Donor Dashboard)</h2>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?><p><?php echo htmlspecialchars($error); ?></p><?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="success-message"><p><?php echo htmlspecialchars($success_message); ?></p></div>
    <?php endif; ?>

    <!-- List New Donation Form -->
    <div class="dashboard-box">
        <h3>List a New Food Donation</h3>
        <form action="donor_dashboard.php" method="POST">
            <div class="form-group">
                <label for="food_description">Food Description (Type, Allergens, Condition):</label>
                <textarea id="food_description" name="food_description" required></textarea>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity (e.g., 5 meals, 1 box, 10 kg):</label>
                <input type="text" id="quantity" name="quantity">
            </div>
            <div class="form-group">
                <label for="pickup_address">Pickup Address:</label>
                <textarea id="pickup_address" name="pickup_address" required><?php echo htmlspecialchars($default_address); ?></textarea>
                 <small>Please provide the full address for pickup.</small>
            </div>
             <div class="form-group">
                <label for="pickup_time_preference">Pickup Time Preference (e.g., Weekdays 9am-5pm):</label>
                <input type="text" id="pickup_time_preference" name="pickup_time_preference">
            </div>
            <button type="submit" name="submit_donation" class="form-button">List Donation</button>
        </form>
    </div>

    <!-- View Existing Donations -->
    <div class="dashboard-box">
        <h3>Your Donation History</h3>
        <?php if (empty($donations)): ?>
            <p>You have not listed any donations yet.</p>
        <?php else: ?>
            <ul class="donation-list">
                <?php foreach ($donations as $donation): ?>
                    <li>
                        <div>
                            <strong><?php echo htmlspecialchars($donation['food_description']); ?></strong>
                             (<?php echo htmlspecialchars($donation['quantity'] ?: 'N/A'); ?>) -
                            <small>Listed: <?php echo date("M d, Y H:i", strtotime($donation['created_at'])); ?></small>
                            <?php if($donation['assigned_volunteer_id']) echo " <small>(Volunteer Assigned)</small>"; ?>
                        </div>
                        <span class="donation-status status-<?php echo htmlspecialchars($donation['status']); ?>">
                            <?php echo ucfirst(htmlspecialchars($donation['status'])); ?>
                        </span>
                         <!-- Add Cancel/Edit buttons here if needed, requires more PHP logic -->
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

     <div class="dashboard-box">
        <h3>Account</h3>
        <p><a href="#">Edit Profile</a> | <a href="logout.php">Logout</a></p> <!-- Link to profile edit page -->
     </div>

</div>

<?php include 'includes/footer.php'; ?>