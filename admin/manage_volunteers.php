<?php
require_once 'includes/admin_header.php'; // Includes Bootstrap, navbar, sidebar

$feedback_message = '';
$feedback_type = ''; // 'success' or 'danger' for Bootstrap alert classes

// --- Handle Volunteer Approval/Rejection Actions ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && isset($_POST['user_id'])) {
    $action = $_POST['action'];
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

    if ($user_id) {
        try {
            if ($action === 'approve') {
                $sql = "UPDATE users SET is_approved = 1 WHERE user_id = :user_id AND role = 'volunteer' AND is_approved = 0";
                $stmt = $pdo->prepare($sql);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                if ($stmt->execute() && $stmt->rowCount() > 0) {
                    $feedback_message = "Volunteer (#" . $user_id . ") approved successfully.";
                    $feedback_type = 'success';
                } else { $feedback_message = "Failed to approve volunteer."; $feedback_type = 'danger'; }
            } elseif ($action === 'reject') {
                $sql = "DELETE FROM users WHERE user_id = :user_id AND role = 'volunteer' AND is_approved = 0";
                 $stmt = $pdo->prepare($sql);
                 $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                 if ($stmt->execute() && $stmt->rowCount() > 0) {
                    $feedback_message = "Volunteer (#" . $user_id . ") rejected and removed successfully.";
                    $feedback_type = 'success';
                 } else { $feedback_message = "Failed to reject volunteer."; $feedback_type = 'danger'; }
            } else { $feedback_message = "Invalid action."; $feedback_type = 'danger'; }
        } catch (PDOException $e) {
            error_log("Volunteer action failed: " . $e->getMessage());
            $feedback_message = "Database error during volunteer action."; $feedback_type = 'danger';
        }
    } else { $feedback_message = "Invalid user ID."; $feedback_type = 'danger'; }
}

// --- Fetch Pending Volunteers ---
$pending_volunteers = [];
try {
    $stmt_pending = $pdo->query("SELECT user_id, first_name, last_name, email, phone, registration_date FROM users WHERE role = 'volunteer' AND is_approved = 0 ORDER BY registration_date ASC");
    $pending_volunteers = $stmt_pending->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { error_log("Fetch pending volunteers error: " . $e->getMessage()); $feedback_message = "Could not load pending volunteers."; $feedback_type = 'danger'; }

// --- Fetch Approved Volunteers ---
$approved_volunteers = [];
try {
    $stmt_approved = $pdo->query("SELECT user_id, first_name, last_name, email, phone, registration_date FROM users WHERE role = 'volunteer' AND is_approved = 1 ORDER BY first_name ASC");
    $approved_volunteers = $stmt_approved->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { error_log("Fetch approved volunteers error: " . $e->getMessage()); $feedback_message = "Could not load approved volunteers."; $feedback_type = 'danger'; }
?>

<!-- Page Title & Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Volunteers</h1>
     <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-success" disabled><i class="bi bi-person-plus-fill me-1"></i> Add Volunteer</button>
    </div>
</div>

<!-- Display Feedback Message -->
<?php if ($feedback_message): ?>
    <div class="alert alert-<?php echo $feedback_type; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($feedback_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>


<!-- Pending Volunteers Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-warning">
        <h5 class="mb-0"><i class="bi bi-hourglass-split me-2"></i>Pending Volunteer Applications</h5>
    </div>
    <div class="card-body">
        <?php if (empty($pending_volunteers)): ?>
            <p class="text-center text-muted">No pending volunteer applications.</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Registered On</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_volunteers as $volunteer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($volunteer['first_name'] . ' ' . $volunteer['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($volunteer['email']); ?></td>
                                <td><?php echo htmlspecialchars($volunteer['phone'] ?: '-'); ?></td>
                                <td><small><?php echo date("Y-m-d H:i", strtotime($volunteer['registration_date'])); ?></small></td>
                                <td class="text-center action-buttons">
                                    <form action="manage_volunteers.php" method="POST">
                                        <input type="hidden" name="user_id" value="<?php echo $volunteer['user_id']; ?>">
                                        <button type="submit" name="action" value="approve" class="btn btn-link text-success p-0 me-1" title="Approve">
                                            <i class="bi bi-check-circle-fill fs-5"></i>
                                        </button>
                                    </form>
                                    <form action="manage_volunteers.php" method="POST" onsubmit="return confirm('Are you sure you want to reject and remove this volunteer application?');">
                                        <input type="hidden" name="user_id" value="<?php echo $volunteer['user_id']; ?>">
                                        <button type="submit" name="action" value="reject" class="btn btn-link text-danger p-0 me-1" title="Reject">
                                            <i class="bi bi-trash-fill fs-5"></i>
                                        </button>
                                    </form>
                                    <a href="#" class="text-secondary p-0" title="View Details (Not implemented)"><i class="bi bi-eye-fill fs-5"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
             <!-- Table Footer -->
             <div class="d-flex justify-content-between align-items-center mt-2">
                 <small class="text-muted">Showing <?php echo count($pending_volunteers); ?> pending <?php echo (count($pending_volunteers) === 1) ? 'application' : 'applications'; ?></small>
             </div>
        <?php endif; ?>
    </div>
</div>

<!-- Approved Volunteers Card -->
<div class="card shadow-sm" id="approved">
     <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-person-check-fill me-2"></i>Approved Volunteers</h5>
    </div>
     <div class="card-body">
         <?php if (empty($approved_volunteers)): ?>
            <p class="text-center text-muted">No approved volunteers found.</p>
        <?php else: ?>
             <div class="table-responsive">
                <table class="table table-striped table-hover table-sm align-middle">
                     <thead class="table-light">
                        <tr>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Registered On</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($approved_volunteers as $volunteer): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($volunteer['first_name'] . ' ' . $volunteer['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($volunteer['email']); ?></td>
                                <td><?php echo htmlspecialchars($volunteer['phone'] ?: '-'); ?></td>
                                <td><small><?php echo date("Y-m-d", strtotime($volunteer['registration_date'])); ?></small></td>
                                <td class="text-center action-buttons">
                                   <a href="#" class="text-secondary p-0 me-1" title="View Details (Not implemented)"><i class="bi bi-eye-fill fs-5"></i></a>
                                   <button class="btn btn-link text-warning p-0" disabled title="Deactivate (Not implemented)"><i class="bi bi-person-dash-fill fs-5"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
             <!-- Table Footer -->
             <div class="d-flex justify-content-between align-items-center mt-2">
                 <small class="text-muted">Showing <?php echo count($approved_volunteers); ?> approved <?php echo (count($approved_volunteers) === 1) ? 'volunteer' : 'volunteers'; ?></small>
             </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; // Includes closing tags and Bootstrap JS ?>