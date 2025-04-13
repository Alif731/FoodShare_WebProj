<?php require_once 'includes/admin_header.php'; ?>

<h2>Admin Dashboard</h2>

<?php
// Fetch Stats - Wrap in try-catch for robustness

$stats = [
    'total_donors' => 0,
    'pending_volunteers' => 0,
    'approved_volunteers' => 0,
    'pending_donations' => 0,
    'assigned_donations' => 0,
    'collected_donations' => 0,
    'delivered_donations' => 0,
];
$stats_error = '';

try {
    $stats['total_donors'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'donor'")->fetchColumn();
    $stats['pending_volunteers'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'volunteer' AND is_approved = 0")->fetchColumn();
    $stats['approved_volunteers'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'volunteer' AND is_approved = 1")->fetchColumn();
    $stats['pending_donations'] = $pdo->query("SELECT COUNT(*) FROM donations WHERE status = 'pending'")->fetchColumn();
    $stats['assigned_donations'] = $pdo->query("SELECT COUNT(*) FROM donations WHERE status = 'assigned'")->fetchColumn();
    $stats['collected_donations'] = $pdo->query("SELECT COUNT(*) FROM donations WHERE status = 'collected'")->fetchColumn();
    $stats['delivered_donations'] = $pdo->query("SELECT COUNT(*) FROM donations WHERE status = 'delivered'")->fetchColumn();

} catch (PDOException $e) {
    // Log error and display a message
    error_log("Admin Dashboard Stats Error: " . $e->getMessage());
    $stats_error = "Could not load all dashboard statistics due to a database error.";
}

?>

<!-- Page Title & Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <!-- Optional: Add buttons or date range picker here -->
</div>

<!-- Display Errors -->
<?php if ($stats_error): ?>
    <div class="alert alert-warning" role="alert">
        <?php echo htmlspecialchars($stats_error); ?>
    </div>
<?php endif; ?> 

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <!-- Pending Volunteers -->
    <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-warning shadow-sm h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-uppercase mb-1 small">Pending Volunteers</div>
                        <div class="h3 mb-0 fw-bold"><?php echo $stats['pending_volunteers']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-person-plus-fill fs-2 text-white-50"></i>
                    </div>
                </div>
            </div>
            <a class="card-footer text-white stretched-link text-decoration-none" href="manage_volunteers.php">
                <span class="small">View Details</span>
                <i class="bi bi-chevron-right small"></i>
            </a>
        </div>
    </div>

    <!-- Approved Volunteers -->
     <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-success shadow-sm h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-uppercase mb-1 small">Approved Volunteers</div>
                        <div class="h3 mb-0 fw-bold"><?php echo $stats['approved_volunteers']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-person-check-fill fs-2 text-white-50"></i>
                    </div>
                </div>
            </div>
             <a class="card-footer text-white stretched-link text-decoration-none" href="manage_volunteers.php#approved">
                <span class="small">View Details</span>
                <i class="bi bi-chevron-right small"></i>
            </a>
        </div>
    </div>

    <!-- Pending Donations -->
     <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-info shadow-sm h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-uppercase mb-1 small">Pending Donations</div>
                        <div class="h3 mb-0 fw-bold"><?php echo $stats['pending_donations']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-box-seam fs-2 text-white-50"></i>
                    </div>
                </div>
            </div>
             <a class="card-footer text-white stretched-link text-decoration-none" href="manage_donations.php?status=pending">
                <span class="small">View Details</span>
                <i class="bi bi-chevron-right small"></i>
            </a>
        </div>
    </div>

     <!-- Delivered Donations -->
     <div class="col-xl-3 col-md-6">
        <div class="card text-white bg-primary shadow-sm h-100">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <div class="text-uppercase mb-1 small">Donations Delivered</div>
                        <div class="h3 mb-0 fw-bold"><?php echo $stats['delivered_donations']; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-truck fs-2 text-white-50"></i>
                    </div>
                </div>
            </div>
             <a class="card-footer text-white stretched-link text-decoration-none" href="manage_donations.php?status=delivered">
                <span class="small">View Details</span>
                <i class="bi bi-chevron-right small"></i>
            </a>
        </div>
    </div>
</div> <!-- /.row -->


<!-- Quick Actions Card -->
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-lightning-charge-fill me-2"></i>Quick Actions</h5>
    </div>
    <div class="list-group list-group-flush">
        <a href="manage_volunteers.php" class="list-group-item list-group-item-action">
            <i class="bi bi-person-plus me-2 text-warning"></i> Manage Volunteer Applications
            <?php if($stats['pending_volunteers'] > 0): ?>
                <span class="badge bg-warning rounded-pill float-end"><?php echo $stats['pending_volunteers']; ?></span>
            <?php endif; ?>
        </a>
        <a href="manage_donations.php?status=pending" class="list-group-item list-group-item-action">
            <i class="bi bi-box-seam me-2 text-info"></i> View Pending Donations
            <?php if($stats['pending_donations'] > 0): ?>
                 <span class="badge bg-info rounded-pill float-end"><?php echo $stats['pending_donations']; ?></span>
             <?php endif; ?>
        </a>
        <a href="manage_donations.php" class="list-group-item list-group-item-action">
            <i class="bi bi-card-list me-2 text-secondary"></i> View All Donations
        </a>
        <a href="manage_users.php" class="list-group-item list-group-item-action">
            <i class="bi bi-people me-2 text-secondary"></i> View Registered Users
        </a>
        <!-- Add more links as needed -->
    </div>
</div>


<?php require_once 'includes/admin_footer.php';  ?>
