<?php
require_once 'includes/admin_header.php';

// --- Fetch All Donations with User Info ---
$donations = [];
$filter_status = $_GET['status'] ?? ''; // Basic filtering by status

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
    if (!empty($filter_status) && in_array($filter_status, ['pending', 'assigned', 'collected', 'delivered', 'cancelled'])) {
        $sql .= " WHERE d.status = :status";
        $params[':status'] = $filter_status;
    }

    $sql .= " ORDER BY d.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $donations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Fetch donations error: " . $e->getMessage());
    echo "<div class='error-message'>Could not load donation data.</div>";
}

?>

<h2>Manage Donations</h2>

<div class="page-header-actions">
    <button class="btn btn-secondary"><i class="fa-solid fa-filter"></i> Filter</button>
    <button class="btn btn-info" disabled><i class="fa-solid fa-file-export"></i> Export</button>
</div>

<div class="admin-card">
    <div class="admin-section">
        <div class="admin-card-header">
            <h3>Donation List <?php if($filter_status) echo "(Filtered by: " . ucfirst($filter_status) . ")"; ?></h3>
        </div>
        
        <div class="admin-card-body">
            <!-- Move filter form inside card body -->
            <form action="manage_donations.php" method="GET" class="filter-form">
                <div class="status-filter">
                    <label for="status">Filter by Status:</label>
                    <select name="status" id="status" class="status-select">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo ($filter_status == 'pending') ? 'selected' : ''; ?>>🕒 Pending</option>
                        <option value="assigned" <?php echo ($filter_status == 'assigned') ? 'selected' : ''; ?>>👤 Assigned</option>
                        <option value="collected" <?php echo ($filter_status == 'collected') ? 'selected' : ''; ?>>📦 Collected</option>
                        <option value="delivered" <?php echo ($filter_status == 'delivered') ? 'selected' : ''; ?>>✅ Delivered</option>
                        <option value="cancelled" <?php echo ($filter_status == 'cancelled') ? 'selected' : ''; ?>>❌ Cancelled</option>
                    </select>
                </div>
            </form>

            <?php if (empty($donations)): ?>
                <p>No donations found<?php if($filter_status) echo " with status '" . htmlspecialchars($filter_status) . "'"; ?>.</p>
            <?php else: ?>
                <div class="admin-table-responsive">
                    <table class="admin-table">
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
                        <tbody>
                            <?php foreach ($donations as $donation): ?>
                                <tr>
                                    <td><?php echo $donation['donation_id']; ?></td>
                                    <td>
                                        <span class="status-label status-label-<?php echo htmlspecialchars($donation['status']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($donation['status'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($donation['food_description']); ?></td>
                                    <td><?php echo htmlspecialchars($donation['quantity'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($donation['donor_first_name'] . ' ' . $donation['donor_last_name']); ?><br><small><?php echo htmlspecialchars($donation['donor_email']); ?></small></td>
                                    <td>
                                        <?php if ($donation['volunteer_first_name']): ?>
                                            <?php echo htmlspecialchars($donation['volunteer_first_name'] . ' ' . $donation['volunteer_last_name']); ?><br><small><?php echo htmlspecialchars($donation['volunteer_email']); ?></small>
                                        <?php else: ?>
                                            <span style="color: #999;">Not Assigned</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($donation['pickup_address']); ?></td>
                                    <td><?php echo date("y-m-d H:i", strtotime($donation['created_at'])); ?></td>
                                    <td><?php echo $donation['collection_time'] ? date("y-m-d H:i", strtotime($donation['collection_time'])) : '-'; ?></td>
                                    <td><?php echo $donation['delivery_time'] ? date("y-m-d H:i", strtotime($donation['delivery_time'])) : '-'; ?></td>
                                    <td>
                                        <!-- Add actions like View Details, Cancel Donation, Manually Assign -->
                                        <button class="action-button btn-view" disabled>Details</button> <!-- Placeholder -->
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>

