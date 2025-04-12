<?php
require_once 'includes/admin_header.php';

// --- Fetch Donors ---
$donors = [];
try {
    $stmt_donors = $pdo->query("SELECT user_id, first_name, last_name, email, phone, registration_date FROM users WHERE role = 'donor' ORDER BY registration_date DESC");
    $donors = $stmt_donors->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch donors error: " . $e->getMessage());
    echo "<div class='error-message'>Could not load donor data.</div>";
}

// --- Fetch Approved Volunteers (Redundant if already done on manage_volunteers, but good for a dedicated user page) ---
$approved_volunteers = [];
try {
    $stmt_volunteers = $pdo->query("SELECT user_id, first_name, last_name, email, phone, registration_date FROM users WHERE role = 'volunteer' AND is_approved = 1 ORDER BY registration_date DESC");
    $approved_volunteers = $stmt_volunteers->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch approved volunteers error: " . $e->getMessage());
    echo "<div class='error-message'>Could not load approved volunteer data.</div>";
}

?>

<h2>Manage Users</h2>

<div class="page-header-actions">
    <a href="#" class="btn btn-success"><i class="fa-solid fa-plus"></i> Add Volunteer</a>
    <button class="btn btn-secondary" disabled><i class="fa-solid fa-filter"></i> Filter</button>
    <button class="btn btn-info" disabled><i class="fa-solid fa-file-export"></i> Export</button>
</div>

<div class="admin-card">
    <div class="admin-section" id="donors">
        <div class="admin-card-header">
            <h3>Registered Donors</h3>
        </div>
        
        <div class="admin-card-body">
            <?php if (empty($donors)): ?>
                <p>No donors found.</p>
            <?php else: ?>
                <div class="admin-table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Registered On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donors as $user): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                                    <td><?php echo date("Y-m-d", strtotime($user['registration_date'])); ?></td>
                                    <td class="actions-buttons">
                                        <button class="action-button btn-view" disabled>View </i></button> <!-- Placeholder -->
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

<div class="admin-card">
    <div class="admin-section" id="approved-volunteers">
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
                                <th>User ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Registered On</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($approved_volunteers as $user): ?>
                                <tr>
                                    <td><?php echo $user['user_id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></td>
                                    <td><?php echo date("Y-m-d", strtotime($user['registration_date'])); ?></td>
                                    <td class="action-buttons">
                                        <a href="#" class="btn-view" title="View Details (Not implemented)"><i class="fa-solid fa-eye"></i></a>
                                        <button class="btn-reject" disabled title="Deactivate (Not implemented)"><i class="fa-solid fa-user-slash"></i></button>
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