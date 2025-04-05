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

<div class="admin-section" id="donors">
    <h3>Registered Donors</h3>
    <?php if (empty($donors)): ?>
        <p>No donors found.</p>
    <?php else: ?>
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
                        <td>
                           <!-- Add actions like View Profile, View Donations, Deactivate -->
                           <button class="action-button btn-view" disabled>View</button> <!-- Placeholder -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>


<div class="admin-section" id="approved-volunteers">
    <h3>Approved Volunteers</h3>
     <?php if (empty($approved_volunteers)): ?>
        <p>No approved volunteers found.</p>
    <?php else: ?>
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
                         <td>
                           <!-- Add actions like View Profile, View Tasks, Deactivate -->
                           <button class="action-button btn-view" disabled>View</button> <!-- Placeholder -->
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>


<?php require_once 'includes/admin_footer.php'; ?>