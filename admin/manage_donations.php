<?php
require_once 'includes/admin_header.php'; // Includes new structure, sidebar, topbar

// --- Fetch All Donations with User Info ---
$donations = [];
// Define allowed statuses for security
$allowed_statuses = ['pending', 'assigned', 'collected', 'delivered', 'cancelled'];
$filter_status = '';
if (isset($_GET['status']) && in_array($_GET['status'], $allowed_statuses)) {
    $filter_status = $_GET['status'];
}

$feedback_message = '';
$feedback_type = '';

// --- Placeholder for future actions (e.g., cancelling) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['donation_id'])) {
    // Example: Handle Cancellation
    if ($_POST['action'] === 'cancel_donation') {
        $donation_id_to_cancel = filter_input(INPUT_POST, 'donation_id', FILTER_VALIDATE_INT);
        if ($donation_id_to_cancel) {
             try {
                // Only allow cancelling pending or assigned donations for simplicity
                $sql_cancel = "UPDATE donations SET status = 'cancelled'
                               WHERE donation_id = :donation_id AND status IN ('pending', 'assigned')";
                $stmt_cancel = $pdo->prepare($sql_cancel);
                $stmt_cancel->bindParam(':donation_id', $donation_id_to_cancel, PDO::PARAM_INT);

                if ($stmt_cancel->execute() && $stmt_cancel->rowCount() > 0) {
                    $feedback_message = "Donation (#" . $donation_id_to_cancel . ") cancelled successfully.";
                    $feedback_type = 'success';
                } else {
                    $feedback_message = "Could not cancel donation (#" . $donation_id_to_cancel . "). It might already be collected, delivered, cancelled, or does not exist.";
                     $feedback_type = 'error';
                }
            } catch (PDOException $e) {
                error_log("Cancel donation failed: " . $e->getMessage());
                $feedback_message = "Database error while cancelling donation.";
                $feedback_type = 'error';
            }
        } else {
            $feedback_message = "Invalid donation ID for cancellation.";
            $feedback_type = 'error';
        }
    }
    // Add other actions here (e.g., manual assignment)
}

// --- Fetch Donation Data ---
try {
    $sql = "SELECT
                d.*,
                u_donor.first_name AS donor_first_name,
                u_donor.last_name AS donor_last_name,
                u_donor.email AS donor_email,
                u_volunteer.first_name AS volunteer_first_name,
                u_volunteer.last_name AS volunteer_last_name,
                u_volunteer.email AS volunteer_email
            FROM donations d
            JOIN users u_donor ON d.donor_id = u_donor.user_id
            LEFT JOIN users u_volunteer ON d.assigned_volunteer_id = u_volunteer.user_id";

    $params = [];
    if (!empty($filter_status)) {
        $sql .= " WHERE d.status = :status";
        $params[':status'] = $filter_status;
    }

    $sql .= " ORDER BY d.created_at DESC"; // Show newest first

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $donation_count = count($donations); // Count for footer display

} catch (PDOException $e) {
    error_log("Fetch donations error: " . $e->getMessage());
    // Display error within the content area
    $feedback_message = "Could not load donation data due to a database error.";
    $feedback_type = 'error';
    $donations = []; // Ensure it's an empty array on error
    $donation_count = 0;
}

?>

<!-- Page Title -->
<h2>Manage Donations</h2>

<!-- Page Header Actions -->
<div class="page-header-actions">
    <!-- Filter Form integrated -->
    <form action="manage_donations.php" method="GET" class="filter-form" style="display: inline-flex; align-items: center; gap: 10px;">
        
        <!-- *** START: MODIFIED SELECT WRAPPER *** -->
        <div class="custom-select-wrapper">
            <label for="status" class="sr-only">Filter by Status:</label> <!-- Screen reader label -->
            <select name="status" id="status" class="custom-select" onchange="this.form.submit()">
                 <option value="">All Statuses</option>
                 <?php foreach ($allowed_statuses as $status_option): ?>
                     <option value="<?php echo $status_option; ?>" <?php echo ($filter_status == $status_option) ? 'selected' : ''; ?>>
                         <?php echo ucfirst($status_option); ?>
                     </option>
                 <?php endforeach; ?>
            </select>
            <!-- Custom arrow will be added via CSS -->
        </div>
        <!-- *** END: MODIFIED SELECT WRAPPER *** -->

        <noscript><button type="submit" class="btn btn-secondary btn-sm"><i class="fa-solid fa-filter"></i> Filter</button></noscript> <!-- Show button if JS disabled -->

        <?php if ($filter_status): // Show clear filter button if a filter is active ?>
            <a href="manage_donations.php" class="btn btn-outline-secondary btn-sm" title="Clear Filter"><i class="fa-solid fa-times"></i> Clear</a>
        <?php endif; ?>
    </form>
    <!-- Other buttons -->
    <button class="btn btn-info btn-sm" disabled><i class="fa-solid fa-file-export"></i> Export</button>
</div>


<!-- Display Feedback Message -->
<?php if ($feedback_message): ?>
    <div class="<?php echo ($feedback_type === 'success') ? 'success-message' : 'error-message'; ?>" style="margin-bottom: 15px;">
        <?php echo htmlspecialchars($feedback_message); ?>
    </div>
<?php endif; ?>


<!-- Donations Table Card -->
<div class="admin-card">
    <!-- ... (Rest of the card header, body, table, footer remains the same as before) ... -->
     <div class="admin-card-header">
        <h3>
            Donation List
            <?php if ($filter_status) echo " <span style='font-weight: normal; color: #6c757d;'>(Filtered by: " . htmlspecialchars(ucfirst($filter_status)) . ")</span>"; ?>
        </h3>
    </div>
    <div class="admin-card-body">
        <?php if (empty($donations) && !$feedback_message): ?>
            <p>No donations found<?php if($filter_status) echo " with status '" . htmlspecialchars($filter_status) . "'"; ?>.</p>
        <?php elseif (!empty($donations)): ?>
            <div class="admin-table-responsive">
                 <table class="admin-table">
                    <!-- Table head -->
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Status</th>
                            <th>Description</th>
                            <th>Qty</th>
                            <th>Donor</th>
                            <th>Volunteer</th>
                            <th>Pickup Address</th>
                            <th>Created</th>
                            <th>Collected</th>
                            <th>Delivered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <!-- Table body -->
                     <tbody>
                        <?php foreach ($donations as $donation): ?>
                            <tr>
                                <!-- Table data cells -->
                                <td><?php echo $donation['donation_id']; ?></td>
                                <td>
                                    <span class="status-label status-label-<?php echo htmlspecialchars($donation['status']); ?>">
                                        <?php echo htmlspecialchars($donation['status']); ?>
                                    </span>
                                </td>
                                <td title="<?php echo htmlspecialchars($donation['food_description']); ?>">
                                    <?php echo htmlspecialchars(mb_strimwidth($donation['food_description'], 0, 50, "...")); ?>
                                </td>
                                <td><?php echo htmlspecialchars($donation['quantity'] ?: 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($donation['donor_first_name'] . ' ' . $donation['donor_last_name']); ?></td>
                                <td>
                                    <?php if ($donation['volunteer_first_name']): ?>
                                        <?php echo htmlspecialchars($donation['volunteer_first_name'] . ' ' . $donation['volunteer_last_name']); ?>
                                    <?php else: ?>
                                        <span style="color: #999;">Not Assigned</span>
                                    <?php endif; ?>
                                </td>
                                <td title="<?php echo htmlspecialchars($donation['pickup_address']); ?>">
                                     <?php echo htmlspecialchars(mb_strimwidth($donation['pickup_address'], 0, 40, "...")); ?>
                                 </td>
                                <td><?php echo date("Y-m-d H:i", strtotime($donation['created_at'])); ?></td>
                                <td><?php echo $donation['collection_time'] ? date("Y-m-d H:i", strtotime($donation['collection_time'])) : '-'; ?></td>
                                <td><?php echo $donation['delivery_time'] ? date("Y-m-d H:i", strtotime($donation['delivery_time'])) : '-'; ?></td>
                                <td class="action-buttons">
                                    <!-- Action buttons -->
                                   <a href="#" class="btn-view" title="View Details (Not implemented)"><i class="fa-solid fa-eye"></i></a>
                                   <?php if (in_array($donation['status'], ['pending', 'assigned'])): ?>
                                   <form action="manage_donations.php<?php echo $filter_status ? '?status='.$filter_status : ''; ?>" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to cancel this donation? This cannot be undone.');">
                                        <input type="hidden" name="donation_id" value="<?php echo $donation['donation_id']; ?>">
                                        <button type="submit" name="action" value="cancel_donation" class="btn-reject" title="Cancel Donation">
                                            <i class="fa-solid fa-ban"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                   <?php if ($donation['status'] === 'pending'): ?>
                                        <a href="#" class="btn-assign" title="Assign Volunteer (Not implemented)"><i class="fa-solid fa-user-plus"></i></a>
                                   <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                 </table>
            </div>
            <!-- Table Footer -->
             <div class="table-footer">
                 <span>Showing <?php echo $donation_count; ?> <?php echo ($donation_count === 1) ? 'entry' : 'entries'; ?> <?php if($filter_status) echo "(filtered)"; ?></span>
             </div>
        <?php endif; ?>
    </div> <!-- /.admin-card-body -->
</div> <!-- /.admin-card -->


<?php require_once 'includes/admin_footer.php'; ?>