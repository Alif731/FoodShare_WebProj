<?php
require_once 'includes/admin_header.php'; // Includes Bootstrap, navbar, sidebar

$feedback_message = '';
$feedback_type = 'danger'; // Default to danger for errors

// --- Fetch Donors ---
$donors = [];
try {
    $stmt_donors = $pdo->query("SELECT user_id, first_name, last_name, email, phone, registration_date FROM users WHERE role = 'donor' ORDER BY registration_date DESC");
    $donors = $stmt_donors->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { error_log("Fetch donors error: " . $e->getMessage()); $feedback_message = "Could not load donor data."; }

// --- Fetch Approved Volunteers ---
$approved_volunteers = [];
try {
    $stmt_volunteers = $pdo->query("SELECT user_id, first_name, last_name, email, phone, registration_date FROM users WHERE role = 'volunteer' AND is_approved = 1 ORDER BY registration_date DESC");
    $approved_volunteers = $stmt_volunteers->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { error_log("Fetch approved volunteers error: " . $e->getMessage()); $feedback_message = ($feedback_message ? $feedback_message." " : "")."Could not load volunteer data."; }

?>

<!-- Page Title & Header -->
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Manage Users</h1>
     <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-sm btn-outline-success" disabled><i class="bi bi-person-plus-fill me-1"></i> Add User</button>
    </div>
</div>

<!-- Display Feedback Message -->
<?php if ($feedback_message): ?>
    <div class="alert alert-<?php echo $feedback_type; ?> alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($feedback_message); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>


<!-- Donors Card -->
<div class="card shadow-sm mb-4" id="donors">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-gift-fill me-2 text-success"></i>Registered Donors</h5>
    </div>
    <div class="card-body">
        <?php if (empty($donors) && !$feedback_message): ?>
            <p class="text-center text-muted">No donors found.</p>
        <?php elseif (!empty($donors)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Registered On</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($donors as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?: '-'); ?></td>
                                <td><small><?php echo date("Y-m-d", strtotime($user['registration_date'])); ?></small></td>
                                <td class="text-center action-buttons">
                                   <a href="#" class="text-secondary p-0 me-1" title="View Profile (Not implemented)"><i class="bi bi-eye-fill fs-5"></i></a>
                                   <a href="#" class="text-info p-0 me-1" title="View Donations (Not implemented)"><i class="bi bi-list-task fs-5"></i></a>
                                   <button class="btn btn-link text-warning p-0" disabled title="Deactivate (Not implemented)"><i class="bi bi-person-dash-fill fs-5"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Table Footer -->
             <div class="d-flex justify-content-between align-items-center mt-2">
                 <small class="text-muted">Showing <?php echo count($donors); ?> <?php echo (count($donors) === 1) ? 'donor' : 'donors'; ?></small>
             </div>
        <?php endif; ?>
    </div>
</div>


<!-- Approved Volunteers Card -->
<div class="card shadow-sm" id="approved-volunteers">
     <div class="card-header bg-light">
        <h5 class="mb-0"><i class="bi bi-person-check-fill me-2 text-success"></i>Approved Volunteers</h5>
    </div>
     <div class="card-body">
         <?php if (empty($approved_volunteers) && !$feedback_message): ?>
            <p class="text-center text-muted">No approved volunteers found.</p>
        <?php elseif (!empty($approved_volunteers)): ?>
             <div class="table-responsive">
                <table class="table table-striped table-hover table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Email</th>
                            <th scope="col">Phone</th>
                            <th scope="col">Registered On</th>
                            <th scope="col" class="text-center">Actions</th>
                        </tr>
                    </thead>
                     <tbody>
                        <?php foreach ($approved_volunteers as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone'] ?: '-'); ?></td>
                                <td><small><?php echo date("Y-m-d", strtotime($user['registration_date'])); ?></small></td>
                                <td class="text-center action-buttons">
                                   <a href="#" class="text-secondary p-0 me-1" title="View Profile (Not implemented)"><i class="bi bi-eye-fill fs-5"></i></a>
                                   <a href="#" class="text-info p-0 me-1" title="View Tasks (Not implemented)"><i class="bi bi-card-checklist fs-5"></i></a>
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