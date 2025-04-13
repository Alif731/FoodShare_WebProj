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

<div class="container mt-4 mb-5"> <!-- Bootstrap Container -->
    <h2 class="mb-4">Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! (Donor)</h2>

    <!-- Display Feedback Messages -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error!</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- List New Donation Form Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="bi bi-gift-fill me-2"></i>List a New Food Donation</h5>
        </div>
        <div class="card-body">
            <form action="donor_dashboard.php" method="POST">
                <div class="mb-3">
                    <label for="food_description" class="form-label">Food Description <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="food_description" name="food_description" rows="3" required placeholder="e.g., 5 Sandwiches (Chicken), 2 Boxes Pasta (uncooked), 1 Tray Lasagna (cooked today) - Include type, quantity, allergens, condition..."></textarea>
                     <div class="form-text">Be descriptive! This helps volunteers understand the donation.</div>
                </div>
                 <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="quantity" class="form-label">Quantity Estimate</label>
                        <input type="text" class="form-control" id="quantity" name="quantity" placeholder="e.g., 5 meals, 1 box, 10 kg">
                    </div>
                     <div class="col-md-6 mb-3">
                         <label for="pickup_time_preference" class="form-label">Pickup Time Preference</label>
                        <input type="text" class="form-control" id="pickup_time_preference" name="pickup_time_preference" placeholder="e.g., Weekdays 9am-5pm, Anytime today">
                     </div>
                 </div>
                <div class="mb-3">
                    <label for="pickup_address" class="form-label">Full Pickup Address <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="pickup_address" name="pickup_address" rows="2" required><?php echo htmlspecialchars($default_address); ?></textarea>
                    <div class="form-text">Provide the complete address where the volunteer should collect the food.</div>
                </div>
                <button type="submit" name="submit_donation" class="btn btn-success"><i class="bi bi-check-circle-fill me-2"></i>Submit Donation</button>
            </form>
        </div>
    </div>

    <!-- View Existing Donations Card -->
    <div class="card shadow-sm">
        <div class="card-header">
             <h5 class="mb-0"><i class="bi bi-list-task me-2"></i>Your Donation History</h5>
        </div>
        <div class="card-body p-0"> <!-- Remove padding for table flush -->
            <?php if (empty($donations)): ?>
                <p class="text-center text-muted p-3">You have not listed any donations yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 align-middle"> <!-- Bootstrap Table -->
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Description</th>
                                <th scope="col">Quantity</th>
                                <th scope="col">Listed On</th>
                                <th scope="col">Status</th>
                                <th scope="col" class="text-center">Volunteer</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donations as $donation):
                                // Determine badge color based on status
                                $status_class = 'secondary'; // Default
                                switch ($donation['status']) {
                                    case 'pending': $status_class = 'warning text-dark'; break;
                                    case 'assigned': $status_class = 'info text-dark'; break;
                                    case 'collected': $status_class = 'primary'; break;
                                    case 'delivered': $status_class = 'success'; break;
                                    case 'cancelled': $status_class = 'danger'; break;
                                }
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars(mb_strimwidth($donation['food_description'], 0, 60, "...")); ?></td>
                                    <td><?php echo htmlspecialchars($donation['quantity'] ?: '-'); ?></td>
                                    <td><small><?php echo date("M d, Y H:i", strtotime($donation['created_at'])); ?></small></td>
                                    <td>
                                        <span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst(htmlspecialchars($donation['status'])); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php if($donation['assigned_volunteer_id']): ?>
                                            <i class="bi bi-person-check-fill text-success" title="Volunteer Assigned"></i>
                                        <?php else: ?>
                                            <i class="bi bi-hourglass-split text-muted" title="Waiting for Volunteer"></i>
                                        <?php endif; ?>
                                         <!-- Add Cancel button here if status allows -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
         <!-- Optional Card Footer for pagination or actions -->
         <!-- <div class="card-footer text-muted text-center"> ... </div> -->
    </div>

     <!-- Account Actions -->
     <!-- <div class="mt-4 text-center">
        <a href="#" class="btn btn-outline-secondary btn-sm">Edit Profile</a>
     </div> -->

</div> <!-- /.container -->

<?php require_once 'includes/footer.php'; ?>