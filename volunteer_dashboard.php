<?php
include 'includes/db_connect.php';

// Authentication Check: Ensure user is logged in, is a volunteer, AND approved
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== 'volunteer') {
    header("Location: login.php"); // Not logged in or not a volunteer
    exit;
}
// Check approval status after ensuring they are a volunteer
try {
    $stmt = $pdo->prepare("SELECT is_approved FROM users WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user || $user['is_approved'] != 1) {
        // Log them out or show a specific "pending approval" message page
        session_destroy(); // Log them out if not approved
        header("Location: login.php?status=pending"); // Redirect with a status message
        exit;
    }
} catch (PDOException $e) {
     error_log("Volunteer approval check failed: " . $e->getMessage());
     // Handle error, maybe redirect to login with a generic error
     header("Location: login.php?status=error");
     exit;
}


$volunteer_id = $_SESSION["user_id"];
$errors = [];
$success_message = '';

// --- Handle Accepting a Task ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accept_task'])) {
    $donation_id_to_accept = $_POST['donation_id'] ?? null;

    if ($donation_id_to_accept) {
        try {
            $pdo->beginTransaction();

            // Check if donation is still pending and not assigned
            $stmt_check = $pdo->prepare("SELECT status, assigned_volunteer_id FROM donations WHERE donation_id = :donation_id");
            $stmt_check->bindParam(':donation_id', $donation_id_to_accept);
            $stmt_check->execute();
            $donation_status = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($donation_status && $donation_status['status'] === 'pending' && is_null($donation_status['assigned_volunteer_id'])) {
                // Assign the donation to the current volunteer
                $sql_assign = "UPDATE donations SET status = 'assigned', assigned_volunteer_id = :volunteer_id WHERE donation_id = :donation_id AND status = 'pending'"; // Double check status
                $stmt_assign = $pdo->prepare($sql_assign);
                $stmt_assign->bindParam(':volunteer_id', $volunteer_id);
                $stmt_assign->bindParam(':donation_id', $donation_id_to_accept);

                if ($stmt_assign->execute() && $stmt_assign->rowCount() > 0) {
                     $pdo->commit();
                    $success_message = "Task accepted successfully! Please coordinate pickup.";
                } else {
                    $pdo->rollBack();
                    $errors[] = "Could not accept task. It might have been taken by another volunteer or an error occurred.";
                }
            } else {
                 $pdo->rollBack();
                $errors[] = "This task is no longer available or already assigned.";
            }

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log("Accept task failed: " . $e->getMessage());
            $errors[] = "An error occurred while accepting the task.";
        }
    } else {
        $errors[] = "Invalid request.";
    }
}

// --- Handle Marking as Collected/Delivered (Add similar POST handling) ---
// Example: Mark as Collected
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_collected'])) {
    $donation_id_to_update = $_POST['donation_id'] ?? null;
    if ($donation_id_to_update) {
         try {
            $sql = "UPDATE donations SET status = 'collected', collection_time = NOW()
                    WHERE donation_id = :donation_id AND assigned_volunteer_id = :volunteer_id AND status = 'assigned'";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':donation_id', $donation_id_to_update);
            $stmt->bindParam(':volunteer_id', $volunteer_id);
            if($stmt->execute() && $stmt->rowCount() > 0) {
                $success_message = "Donation marked as collected!";
            } else {
                $errors[] = "Could not mark as collected. Status might have changed or it's not assigned to you.";
            }
        } catch (PDOException $e) {
            error_log("Mark collected failed: " . $e->getMessage());
            $errors[] = "An error occurred.";
        }
    }
}
// Example: Mark as Delivered
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['mark_delivered'])) {
     $donation_id_to_update = $_POST['donation_id'] ?? null;
     if ($donation_id_to_update) {
         try {
             $sql = "UPDATE donations SET status = 'delivered', delivery_time = NOW()
                     WHERE donation_id = :donation_id AND assigned_volunteer_id = :volunteer_id AND status = 'collected'";
              $stmt = $pdo->prepare($sql);
             $stmt->bindParam(':donation_id', $donation_id_to_update);
             $stmt->bindParam(':volunteer_id', $volunteer_id);
             if($stmt->execute() && $stmt->rowCount() > 0) {
                 $success_message = "Donation marked as delivered! Thank you!";
             } else {
                 $errors[] = "Could not mark as delivered. Status must be 'collected' and assigned to you.";
             }
         } catch (PDOException $e) {
             error_log("Mark delivered failed: " . $e->getMessage());
             $errors[] = "An error occurred.";
         }
     }
}


// --- Fetch Available Tasks (Pending Donations) ---
$available_tasks = [];
try {
    $stmt = $pdo->prepare("
        SELECT d.donation_id, d.food_description, d.quantity, d.pickup_address, d.pickup_time_preference, d.created_at, u.first_name AS donor_name
        FROM donations d
        JOIN users u ON d.donor_id = u.user_id
        WHERE d.status = 'pending'
        ORDER BY d.created_at ASC
    ");
    $stmt->execute();
    $available_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetching available tasks failed: " . $e->getMessage());
    $errors[] = "Could not load available tasks.";
}

// --- Fetch Volunteer's Current/Completed Tasks ---
$my_tasks = [];
try {
    $stmt = $pdo->prepare("
        SELECT d.donation_id, d.food_description, d.quantity, d.pickup_address, d.pickup_time_preference, d.status, d.created_at, d.collection_time, d.delivery_time, u.first_name AS donor_name, u.phone AS donor_phone
        FROM donations d
        JOIN users u ON d.donor_id = u.user_id
        WHERE d.assigned_volunteer_id = :volunteer_id
        ORDER BY FIELD(d.status, 'assigned', 'collected', 'delivered', 'cancelled'), d.created_at DESC
    "); // Order by status progression
    $stmt->bindParam(':volunteer_id', $volunteer_id);
    $stmt->execute();
    $my_tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetching volunteer tasks failed: " . $e->getMessage());
     $errors[] = "Could not load your tasks.";
}
?>

<?php include 'includes/header.php'; ?>

<div class="container dashboard-container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['first_name']); ?>! (Volunteer Dashboard)</h2>

     <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?><p><?php echo htmlspecialchars($error); ?></p><?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if ($success_message): ?>
        <div class="success-message"><p><?php echo htmlspecialchars($success_message); ?></p></div>
    <?php endif; ?>

    <!-- Available Tasks -->
    <div class="dashboard-box">
        <h3>Available Donation Pickups</h3>
        <?php if (empty($available_tasks)): ?>
            <p>No pending donations available right now. Check back later!</p>
        <?php else: ?>
            <ul class="task-list">
                <?php foreach ($available_tasks as $task): ?>
                    <li>
                        <div>
                            <strong><?php echo htmlspecialchars($task['food_description']); ?></strong> (<?php echo htmlspecialchars($task['quantity'] ?: 'N/A'); ?>)<br>
                            <small>From: <?php echo htmlspecialchars($task['donor_name']); ?></small><br>
                            <small>Location: <?php echo htmlspecialchars($task['pickup_address']); ?></small><br>
                            <small>Preference: <?php echo htmlspecialchars($task['pickup_time_preference'] ?: 'Any'); ?></small><br>
                             <small>Listed: <?php echo date("M d, Y H:i", strtotime($task['created_at'])); ?></small>
                        </div>
                        <form action="volunteer_dashboard.php" method="POST" style="display: inline;">
                            <input type="hidden" name="donation_id" value="<?php echo $task['donation_id']; ?>">
                            <button type="submit" name="accept_task" class="action-button btn-accept">Accept Task</button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <!-- My Assigned/Completed Tasks -->
    <div class="dashboard-box">
         <h3>Your Assigned & Completed Tasks</h3>
         <?php if (empty($my_tasks)): ?>
            <p>You have not accepted any tasks yet.</p>
        <?php else: ?>
            <ul class="task-list">
                <?php foreach ($my_tasks as $task): ?>
                     <li>
                        <div>
                             <strong><?php echo htmlspecialchars($task['food_description']); ?></strong> (<?php echo htmlspecialchars($task['quantity'] ?: 'N/A'); ?>)<br>
                             <small>From: <?php echo htmlspecialchars($task['donor_name']); ?> (Phone: <?php echo htmlspecialchars($task['donor_phone'] ?: 'N/A'); ?>)</small><br>
                             <small>Pickup Address: <?php echo htmlspecialchars($task['pickup_address']); ?></small><br>
                             <small>Status: <span class="donation-status status-<?php echo htmlspecialchars($task['status']); ?>"><?php echo ucfirst(htmlspecialchars($task['status'])); ?></span></small><br>
                             <?php if($task['collection_time']): ?><small>Collected: <?php echo date("M d, Y H:i", strtotime($task['collection_time'])); ?></small><br><?php endif; ?>
                             <?php if($task['delivery_time']): ?><small>Delivered: <?php echo date("M d, Y H:i", strtotime($task['delivery_time'])); ?></small><?php endif; ?>
                        </div>
                        <div> <!-- Action buttons -->
                            <?php if ($task['status'] === 'assigned'): ?>
                                <form action="volunteer_dashboard.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="donation_id" value="<?php echo $task['donation_id']; ?>">
                                    <button type="submit" name="mark_collected" class="action-button btn-collected">Mark Collected</button>
                                </form>
                            <?php elseif ($task['status'] === 'collected'): ?>
                                <form action="volunteer_dashboard.php" method="POST" style="display: inline;">
                                     <input type="hidden" name="donation_id" value="<?php echo $task['donation_id']; ?>">
                                    <button type="submit" name="mark_delivered" class="action-button btn-delivered">Mark Delivered</button>
                                </form>
                            <?php endif; ?>
                             <!-- Add button/link for details/notes if needed -->
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

     <div class="dashboard-box">
        <h3>Account</h3>
        <p><a href="#">Edit Profile</a> | <a href="logout.php">Logout</a></p>
     </div>

</div>

<?php include 'includes/footer.php'; ?>