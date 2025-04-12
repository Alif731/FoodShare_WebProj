<?php
require_once 'includes/admin_header.php';

$feedback_message = '';
$feedback_type = ''; // 'success' or 'error'

// --- Handle Volunteer Approval/Rejection Actions ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['user_id'])) {
    $action = $_POST['action'];
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if ($user_id) {
        try {
            if ($action === 'approve') {
                // Approve the volunteer
                $sql = "UPDATE users SET is_approved = 1 WHERE user_id = :user_id AND role = 'volunteer' AND is_approved = 0";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                if ($stmt->execute() && $stmt->rowCount() > 0) {
                    $feedback_message = "Volunteer approved successfully.";
                    $feedback_type = 'success';
                    // TODO: Optionally send an email notification to the volunteer
                } else {
                    $feedback_message = "Failed to approve volunteer (already approved or doesn't exist?).";
                    $feedback_type = 'error';
                }
            } elseif ($action === 'reject') {
                // Reject (Delete) the volunteer - Use with caution!
                // Alternative: Set is_approved = -1 or similar if you want to keep the record
                $sql = "DELETE FROM users WHERE user_id = :user_id AND role = 'volunteer' AND is_approved = 0";
                 $stmt = $pdo->prepare($sql);
                 $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                 if ($stmt->execute() && $stmt->rowCount() > 0) {
                    $feedback_message = "Volunteer rejected and removed successfully.";
                    $feedback_type = 'success';
                     // TODO: Optionally send an email notification
                 } else {
                    $feedback_message = "Failed to reject volunteer (already approved/rejected or doesn't exist?).";
                    $feedback_type = 'error';
                 }
            } else {
                 $feedback_message = "Invalid action.";
                 $feedback_type = 'error';
            }
        } catch (PDOException $e) {
            error_log("Volunteer action failed: " . $e->getMessage());
            $feedback_message = "Database error during volunteer action.";
            $feedback_type = 'error';
        }
    } else {
        $feedback_message = "Invalid user ID.";
        $feedback_type = 'error';
    }

    // Optional: Redirect after POST to prevent resubmission (PRG pattern)
    // This makes feedback slightly trickier (use sessions or query params)
    // header("Location: manage_volunteers.php?feedback=" . urlencode($feedback_message) . "&type=" . $feedback_type);
    // exit;
}

// --- Fetch Pending Volunteers ---
$pending_volunteers = [];
try {
    $stmt_pending = $pdo->query("SELECT user_id, first_name, last_name, email, phone, registration_date FROM users WHERE role = 'volunteer' AND is_approved = 0 ORDER BY registration_date ASC");
    $pending_volunteers = $stmt_pending->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch pending volunteers error: " . $e->getMessage());
    echo "<div class='error-message'>Could not load pending volunteers.</div>";
}

// --- Fetch Approved Volunteers ---
$approved_volunteers = [];
try {
    $stmt_approved = $pdo->query("SELECT user_id, first_name, last_name, email, phone, registration_date FROM users WHERE role = 'volunteer' AND is_approved = 1 ORDER BY first_name ASC");
    $approved_volunteers = $stmt_approved->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
     error_log("Fetch approved volunteers error: " . $e->getMessage());
    echo "<div class='error-message'>Could not load approved volunteers.</div>";
}
?>

<!-- Page Title -->
<h2>Manage Volunteers</h2>

<!-- Page Header Actions (Example) -->
<div class="page-header-actions">
    <a href="#" class="btn btn-success"><i class="fa-solid fa-plus"></i> Add Volunteer</a>
    <button class="btn btn-secondary" disabled><i class="fa-solid fa-filter"></i> Filter</button>
    <button class="btn btn-info" disabled><i class="fa-solid fa-file-export"></i> Export</button>
</div>

<!-- Pending Volunteers Section -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3>Pending Volunteer Applications</h3>
    </div>
    <div class="admin-card-body">
        <?php if ($feedback_message && $feedback_type == 'error' && strpos($feedback_message, 'pending') !== false): ?>
            <div class="error-message"><?php echo htmlspecialchars($feedback_message); ?></div>
        <?php elseif ($feedback_message && $feedback_type == 'success' && strpos($feedback_message, 'pending') !== false): ?>
             <div class="success-message"><?php echo htmlspecialchars($feedback_message); ?></div>
        <?php endif; ?>

        <?php if (empty($pending_volunteers)): ?>
            <p>No pending volunteer applications.</p>
        <?php else: ?>
            <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAllPending" title="Select All"></th> <!-- Checkbox -->
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Registered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_volunteers as $volunteer): ?>
                            <tr>
                                <td><input type="checkbox" name="selected_pending[]" value="<?php echo $volunteer['user_id']; ?>"></td>
                                <td><?php echo htmlspecialchars($volunteer['first_name'] . ' ' . $volunteer['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($volunteer['email']); ?></td>
                                <td><?php echo htmlspecialchars($volunteer['phone'] ?: 'N/A'); ?></td>
                                <td><?php echo date("Y-m-d H:i", strtotime($volunteer['registration_date'])); ?></td>
                                <td class="action-buttons">
                                    <form action="manage_volunteers.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $volunteer['user_id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn-approve" title="Approve">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                    <form action="manage_volunteers.php" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to reject and remove this volunteer application?');">
                                        <input type="hidden" name="user_id" value="<?php echo $volunteer['user_id']; ?>">
                                        <button type="submit" name="action" value="reject" class="btn-reject" title="Reject">
                                            <i class="fa-solid fa-trash-alt"></i>
                                        </button>
                                    </form>
                                    <a href="#" class="btn-view" title="View Details (Not implemented)"><i class="fa-solid fa-eye"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
             <!-- Add Table Footer if needed -->
             <div class="table-footer">
                 <span>Showing <?php echo count($pending_volunteers); ?> entries</span>
                 <!-- Add pagination controls here later -->
             </div>
        <?php endif; ?>
    </div>
</div>


<!-- Approved Volunteers Section -->
<div class="admin-card" id="approved">
     <div class="admin-card-header">
        <h3>Approved Volunteers</h3>
    </div>
     <div class="admin-card-body">
         <?php if (empty($approved_volunteers)): ?>
            <p>No approved volunteers found.</p>
        <?php else: ?>
             <div class="admin-table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Registered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approved_volunteers as $volunteer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($volunteer['first_name'] . ' ' . $volunteer['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($volunteer['email']); ?></td>
                                <td><?php echo htmlspecialchars($volunteer['phone'] ?: 'N/A'); ?></td>
                                <td><?php echo date("Y-m-d", strtotime($volunteer['registration_date'])); ?></td>
                                <td class="action-buttons">
                                   <a href="#" class="btn-view" title="View Details (Not implemented)"><i class="fa-solid fa-eye"></i></a>
                                   <button class="btn-reject" disabled title="Deactivate (Not implemented)"><i class="fa-solid fa-user-slash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
             <!-- Add Table Footer if needed -->
             <div class="table-footer">
                 <span>Showing <?php echo count($approved_volunteers); ?> entries</span>
             </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>