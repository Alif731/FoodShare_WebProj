<?php
require_once 'includes/admin_header.php'; // Includes Bootstrap links and new layout

// --- (Keep existing PHP logic for fetching data, feedback messages, actions) ---
$allowed_statuses = ['pending', 'assigned', 'collected', 'delivered', 'cancelled'];
$filter_status = '';
if (isset($_GET['status']) && in_array($_GET['status'], $allowed_statuses)) {
    $filter_status = $_GET['status'];
}
$feedback_message = '';
$feedback_type = '';
// --- (POST handling logic for cancel etc.) ---
// --- (Fetch Donation Data logic) ---
$donations = [];
$donation_count = 0; // Initialize
try {
    // ... (Existing SQL and PDO execution) ...
    $stmt->execute($params);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $donation_count = count($donations);
} catch (PDOException $e) {
    // ... (Existing error handling) ...
     $feedback_message = "Could not load donation data due to a database error.";
     $feedback_type = 'error';
}
?>

<!-- Page Title & Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Donations</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <!-- Filter Form -->
        <form action="manage_donations.php" method="GET" class="d-inline-flex align-items-center me-2">
             <!-- *** USE BOOTSTRAP form-select *** -->
            <select name="status" id="status" class="form-select form-select-sm me-2" onchange="this.form.submit()" aria-label="Filter by Status">
                 <option value="">All Statuses</option>
                 <?php foreach ($allowed_statuses as $status_option): ?>
                     <option value="<?php echo $status_option; ?>" <?php echo ($filter_status == $status_option) ? 'selected' : ''; ?>>
                         <?php echo ucfirst($status_option); ?>
                     </option>
                 <?php endforeach; ?>
            </select>
            <noscript><button type="submit" class="btn btn-sm btn-outline-secondary">Filter</button></noscript>
            <?php if ($filter_status): ?>
                <a href="manage_donations.php" class="btn btn-sm btn-outline-secondary ms-1" title="Clear Filter"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </form>
         <!-- Other Buttons -->
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-secondary" disabled><i class="bi bi-upload me-1"></i> Export</button>
            <!-- Add other buttons -->
        </div>
    </div>
</div>


<!-- Display Feedback Message -->
<?php if ($feedback_message): ?>
    <div class="alert alert-<?php echo ($feedback_type === 'success') ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($feedback_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>


<!-- Donations Table Card -->
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
            <div class="table-responsive">
                 <table class="table table-striped table-hover table-sm align-middle"> <!-- Bootstrap Table Classes -->
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
                                    <?php // Use Bootstrap Badges for status
                                        $status_class = 'secondary'; // Default
                                        switch ($donation['status']) {
                                            case 'pending': $status_class = 'warning text-dark'; break;
                                            case 'assigned': $status_class = 'info text-dark'; break;
                                            case 'collected': $status_class = 'primary'; break;
                                            case 'delivered': $status_class = 'success'; break;
                                            case 'cancelled': $status_class = 'danger'; break;
                                        }
                                    ?>
                                    <span class="badge bg-<?php echo $status_class; ?>"><?php echo ucfirst(htmlspecialchars($donation['status'])); ?></span>
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
                                <td class="text-center action-buttons"> <!-- Keep custom class or use BS utilities -->
                                   <a href="#" class="text-primary me-1" title="View Details"><i class="bi bi-eye-fill"></i></a>
                                   <?php if (in_array($donation['status'], ['pending', 'assigned'])): ?>
                                   <form action="manage_donations.php<?php echo $filter_status ? '?status='.$filter_status : ''; ?>" method="POST" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="donation_id" value="<?php echo $donation['donation_id']; ?>">
                                        <button type="submit" name="action" value="cancel_donation" class="btn btn-link text-danger p-0 me-1" title="Cancel Donation">
                                            <i class="bi bi-ban-fill"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                   <?php if ($donation['status'] === 'pending'): ?>
                                        <a href="#" class="text-info" title="Assign Volunteer"><i class="bi bi-person-plus-fill"></i></a>
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
                 <!-- Bootstrap Pagination component would go here -->
             </div>
        <?php endif; ?>
    </div> <!-- /.card-body -->
</div> <!-- /.card -->


<?php require_once 'includes/admin_footer.php'; // Includes closing tags and Bootstrap JS ?>