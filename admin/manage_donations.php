<?php
require_once 'includes/admin_header.php'; // Includes Bootstrap links and layout

// --- Fetch All Donations with User Info ---
$donations = [];
// Define allowed statuses for security and dropdown options
$allowed_statuses = ['pending', 'assigned', 'collected', 'delivered', 'cancelled'];
$filter_status = '';
// Validate the status parameter from GET request
if (isset($_GET['status']) && in_array($_GET['status'], $allowed_statuses)) {
    $filter_status = $_GET['status'];
}

$feedback_message = '';
$feedback_type = ''; // Will be 'success' or 'danger' for Bootstrap alerts

// --- Handle Actions (e.g., Cancelling a Donation) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['donation_id'])) {
    // Example: Handle Cancellation
    if ($_POST['action'] === 'cancel_donation') {
        $donation_id_to_cancel = filter_input(INPUT_POST, 'donation_id', FILTER_VALIDATE_INT);
        if ($donation_id_to_cancel) {
             try {
                // Define which statuses can be cancelled
                $cancellable_statuses = ['pending', 'assigned'];
                $sql_cancel = "UPDATE donations SET status = 'cancelled'
                               WHERE donation_id = :donation_id AND status IN ('" . implode("','", $cancellable_statuses) . "')";
                $stmt_cancel = $pdo->prepare($sql_cancel);
                $stmt_cancel->bindParam(':donation_id', $donation_id_to_cancel, PDO::PARAM_INT);

                if ($stmt_cancel->execute() && $stmt_cancel->rowCount() > 0) {
                    $feedback_message = "Donation (#" . $donation_id_to_cancel . ") cancelled successfully.";
                    $feedback_type = 'success';
                } else {
                    // Check current status if rowCount is 0
                    $stmt_check = $pdo->prepare("SELECT status FROM donations WHERE donation_id = :donation_id");
                    $stmt_check->bindParam(':donation_id', $donation_id_to_cancel, PDO::PARAM_INT);
                    $stmt_check->execute();
                    $current_status = $stmt_check->fetchColumn();
                    if ($current_status && !in_array($current_status, $cancellable_statuses)) {
                         $feedback_message = "Cannot cancel donation (#" . $donation_id_to_cancel . ") because its status is '" . $current_status . "'.";
                    } else {
                         $feedback_message = "Could not cancel donation (#" . $donation_id_to_cancel . "). It might not exist or another error occurred.";
                    }
                     $feedback_type = 'warning'; // Use warning if cancellation wasn't possible due to status
                }
            } catch (PDOException $e) {
                error_log("Cancel donation failed: " . $e->getMessage());
                $feedback_message = "Database error while cancelling donation.";
                $feedback_type = 'danger';
            }
        } else {
            $feedback_message = "Invalid donation ID for cancellation.";
            $feedback_type = 'danger';
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
    $feedback_message = "Could not load donation data due to a database error.";
    $feedback_type = 'danger';
    $donations = []; // Ensure it's an empty array on error
    $donation_count = 0;
}

?>

<!-- Page Title & Header using Bootstrap flex utilities -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Donations</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <!-- Filter Form -->
        <form action="manage_donations.php" method="GET" class="d-inline-flex align-items-center me-2">
            <select name="status" id="status" class="form-select form-select-sm me-2" onchange="this.form.submit()" aria-label="Filter by Status">
                 <option value="">All Statuses</option>
                 <?php foreach ($allowed_statuses as $status_option): ?>
                     <option value="<?php echo $status_option; ?>" <?php echo ($filter_status == $status_option) ? 'selected' : ''; ?>>
                         <?php echo ucfirst($status_option); ?>
                     </option>
                 <?php endforeach; ?>
            </select>
            <noscript><button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button></noscript>
            <?php if ($filter_status): // Show clear filter button if a filter is active ?>
                <a href="manage_donations.php" class="btn btn-sm btn-outline-secondary ms-1" title="Clear Filter"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </form>
         <!-- Other Buttons -->
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary" disabled><i class="bi bi-upload me-1"></i> Export</button>
        </div>
    </div>
</div>


<!-- Display Feedback Message using Bootstrap Alerts -->
<?php if ($feedback_message): ?>
    <div class="alert alert-<?php echo $feedback_type; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($feedback_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>


<!-- Donations Table Card using Bootstrap Card component -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">
            Donation List
            <?php if ($filter_status) echo " <small class='text-muted'>(Filtered by: " . htmlspecialchars(ucfirst($filter_status)) . ")</small>"; ?>
        </h5>
    </div>
    <div class="card-body">
        <?php if (empty($donations) && !$feedback_message): ?>
            <p class="text-center text-muted">No donations found<?php if($filter_status) echo " with status '" . htmlspecialchars($filter_status) . "'"; ?>.</p>
        <?php elseif (!empty($donations)): ?>
            <!-- Responsive Table Wrapper -->
            <div class="table-responsive">
                 <!-- Bootstrap Table Classes -->
                 <table class="table table-striped table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Status</th>
                            <th scope="col">Description</th>
                            <th scope="col">Qty</th>
                            <th scope="col">Donor</th>
                            <th scope="col">Volunteer</th>
                            <th scope="col">Pickup Address</th>
                            <th scope="col">Created</th>
                            <th scope="col">Collected</th>
                            <th scope="col">Delivered</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donations as $donation): ?>
                            <tr>
                                <td><?php echo $donation['donation_id']; ?></td>
                                <td>
                                    <?php // Bootstrap Badges for status
                                        $status_class = 'secondary'; // Default
                                        switch ($donation['status']) {
                                            case 'pending': $status_class = 'warning text-dark'; break;
                                            case 'assigned': $status_class = 'info text-dark'; break;
                                            case 'collected': $status_class = 'primary'; break;
                                            case 'delivered': $status_class = 'success'; break;
                                            case 'cancelled': $status_class = 'danger'; break;
                                        }
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?> text-capitalize"><?php echo htmlspecialchars($donation['status']); ?></span>
                                </td>
                                <td title="<?php echo htmlspecialchars($donation['food_description']); ?>">
                                    <?php echo htmlspecialchars(mb_strimwidth($donation['food_description'], 0, 40, "...")); ?>
                                </td>
                                <td><?php echo htmlspecialchars($donation['quantity'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($donation['donor_first_name'] . ' ' . $donation['donor_last_name']); ?></td>
                                <td>
                                    <?php if ($donation['volunteer_first_name']): ?>
                                        <?php echo htmlspecialchars($donation['volunteer_first_name'] . ' ' . $donation['volunteer_last_name']); ?>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic">None</span>
                                    <?php endif; ?>
                                </td>
                                 <td title="<?php echo htmlspecialchars($donation['pickup_address']); ?>">
                                     <?php echo htmlspecialchars(mb_strimwidth($donation['pickup_address'], 0, 35, "...")); ?>
                                 </td>
                                <td><small><?php echo date("d/m/y H:i", strtotime($donation['created_at'])); ?></small></td>
                                <td><small><?php echo $donation['collection_time'] ? date("d/m/y H:i", strtotime($donation['collection_time'])) : '-'; ?></small></td>
                                <td><small><?php echo $donation['delivery_time'] ? date("d/m/y H:i", strtotime($donation['delivery_time'])) : '-'; ?></small></td>
                                <td class="text-center action-buttons"> <!-- Bootstrap text-center and custom class -->
                                   <!-- Action Buttons using Bootstrap Icons and btn-link for minimal styling -->
                                   <a href="#" class="text-primary mx-1" title="View Details (Not implemented)"><i class="bi bi-eye-fill"></i></a>
                                   <?php if (in_array($donation['status'], ['pending', 'assigned'])): // Show cancel only if applicable ?>
                                   <form action="manage_donations.php<?php echo $filter_status ? '?status='.$filter_status : ''; ?>" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this donation? This cannot be undone.');">
                                        <input type="hidden" name="donation_id" value="<?php echo $donation['donation_id']; ?>">
                                        <button type="submit" name="action" value="cancel_donation" class="btn btn-link text-danger p-0 mx-1" title="Cancel Donation">
                                            <i class="bi bi-ban-fill"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                   <?php if ($donation['status'] === 'pending'): ?>
                                        <a href="#" class="text-info mx-1" title="Assign Volunteer (Not implemented)"><i class="bi bi-person-plus-fill"></i></a>
                                   <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                 </table>
            </div>
             <!-- Table Footer / Pagination -->
             <div class="d-flex justify-content-between align-items-center mt-3">
                 <small class="text-muted">Showing <?php echo $donation_count; ?> <?php echo ($donation_count === 1) ? 'entry' : 'entries'; ?> <?php if($filter_status) echo "(filtered)"; ?></small>
                 <!-- Bootstrap Pagination component would go here if needed -->
                 <!-- Example:
                 <nav aria-label="Table navigation">
                   <ul class="pagination pagination-sm mb-0">
                     <li class="page-item disabled"><a class="page-link" href="#">Previous</a></li>
                     <li class="page-item active"><a class="page-link" href="#">1</a></li>
                     <li class="page-item"><a class="page-link" href="#">2</a></li>
                     <li class="page-item"><a class="page-link" href="#">Next</a></li>
                   </ul>
                 </nav>
                 -->
             </div>
        <?php endif; ?>
    </div> <!-- /.card-body -->
</div> <!-- /.card -->


<?php require_once 'includes/admin_footer.php'; // Includes closing tags and Bootstrap JS ?>