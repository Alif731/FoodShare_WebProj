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
    echo "<div class='error-message'>Could not load all dashboard statistics.</div>";
}
?>

<div class="admin-stats-container">
    <div class="admin-stat-box">
        <span class="count"><?php echo $stats['pending_volunteers']; ?></span>
        <span class="label"><a href="manage_volunteers.php">Pending Volunteers</a></span>
    </div>
    <div class="admin-stat-box">
        <span class="count"><?php echo $stats['approved_volunteers']; ?></span>
        <span class="label"><a href="manage_volunteers.php#approved">Approved Volunteers</a></span>
    </div>
     <div class="admin-stat-box">
        <span class="count"><?php echo $stats['total_donors']; ?></span>
        <span class="label"><a href="manage_users.php#donors">Registered Donors</a></span>
    </div>
    <div class="admin-stat-box">
        <span class="count"><?php echo $stats['pending_donations']; ?></span>
        <span class="label"><a href="manage_donations.php?status=pending">Pending Donations</a></span>
    </div>
    <div class="admin-stat-box">
        <span class="count"><?php echo $stats['assigned_donations']; ?></span>
        <span class="label"><a href="manage_donations.php?status=assigned">Assigned Donations</a></span>
    </div>
     <div class="admin-stat-box">
        <span class="count"><?php echo $stats['collected_donations']; ?></span>
        <span class="label"><a href="manage_donations.php?status=collected">Collected Donations</a></span>
    </div>
    <div class="admin-stat-box">
        <span class="count"><?php echo $stats['delivered_donations']; ?></span>
        <span class="label"><a href="manage_donations.php?status=delivered">Delivered Donations</a></span>
    </div>
</div>

<div class="admin-section">
    <h3>Quick Actions</h3>
    <ul>
        <li><a href="manage_volunteers.php">Manage Volunteer Applications</a></li>
        <li><a href="manage_donations.php">View All Donations</a></li>
        <li><a href="manage_users.php">View Registered Users</a></li>
        <!-- Add links to other potential admin functions -->
    </ul>
</div>


<?php require_once 'includes/admin_footer.php'; ?>