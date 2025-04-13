<?php
require_once 'includes/db_connect.php'; // Ensure session started and DB connected
$errors = [];
$success_message = '';
// Store submitted values for repopulation
$form_values = [
    'first_name' => $_POST['first_name'] ?? '',
    'last_name' => $_POST['last_name'] ?? '',
    'email' => $_POST['email'] ?? '',
    'phone' => $_POST['phone'] ?? '',
    'address' => $_POST['address'] ?? '',
    'city' => $_POST['city'] ?? '',
    'postal_code' => $_POST['postal_code'] ?? '',
    'motivation' => $_POST['motivation'] ?? '',
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $role = 'volunteer'; // Hardcoded
    $motivation = trim($_POST['motivation'] ?? '');

    // Basic Validation
    if (empty($first_name)) $errors['first_name'] = "First name is required.";
    if (empty($last_name)) $errors['last_name'] = "Last name is required.";
     if (empty($email)) {
        $errors['email'] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Valid email is required.";
    }
     if (empty($password)) {
        $errors['password'] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Password must be at least 6 characters long.";
    }
    if ($password !== $confirm_password) $errors['confirm_password'] = "Passwords do not match.";
    if (empty($phone)) $errors['phone'] = "Phone number is required for coordination.";
    // Add phone number format validation if desired


    // Check if email already exists only if initial validation passes
    if (empty($errors)) {
       try {
            $stmt_check = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
            $stmt_check->bindParam(':email', $email);
            $stmt_check->execute();
            if ($stmt_check->rowCount() > 0) {
                $errors['email'] = "An account with this email already exists.";
            }
        } catch (PDOException $e) {
            error_log("Email check failed: " . $e->getMessage());
            $errors['db'] = "Database error during registration. Please try again.";
        }
    }

    // If no errors, proceed with insertion
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $is_approved = 0; // Volunteers start as NOT approved

        try {
            // Add motivation to the INSERT statement if you added the column to the DB
            // Example assumes 'motivation' column exists in 'users' table
             // $sql = "INSERT INTO users (first_name, last_name, email, password, phone, address, city, postal_code, role, is_approved, motivation)
             //         VALUES (:first_name, :last_name, :email, :password, :phone, :address, :city, :postal_code, :role, :is_approved, :motivation)";
             $sql = "INSERT INTO users (first_name, last_name, email, password, phone, address, city, postal_code, role, is_approved)
                     VALUES (:first_name, :last_name, :email, :password, :phone, :address, :city, :postal_code, :role, :is_approved)";
            $stmt_insert = $pdo->prepare($sql);
            // Bind parameters...
            $stmt_insert->bindParam(':first_name', $first_name);
            $stmt_insert->bindParam(':last_name', $last_name);
            $stmt_insert->bindParam(':email', $email);
            $stmt_insert->bindParam(':password', $hashed_password);
            $stmt_insert->bindParam(':phone', $phone);
            $stmt_insert->bindParam(':address', $address);
            $stmt_insert->bindParam(':city', $city);
            $stmt_insert->bindParam(':postal_code', $postal_code);
            $stmt_insert->bindParam(':role', $role);
            $stmt_insert->bindParam(':is_approved', $is_approved, PDO::PARAM_BOOL);
            // if ($motivation !== '') $stmt_insert->bindParam(':motivation', $motivation); // Bind motivation if column exists

            if ($stmt_insert->execute()) {
                $success_message = "Registration submitted successfully! Your application is pending review by an administrator. You will be notified via email once approved.";
                 // Clear form values on success
                 $form_values = array_fill_keys(array_keys($form_values), '');
            } else {
                $errors['db'] = "Registration failed. Please try again.";
            }
        } catch (PDOException $e) {
             error_log("Volunteer registration failed: " . $e->getMessage());
             $errors['db'] = "An error occurred during registration. Please try again later.";
        }
    }
}

require_once 'includes/header.php'; // Include Bootstrap header
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">

            <div class="card shadow-sm">
                <div class="card-header  text-white" style='background-color: #ff8c00;'>
                     <h4 class="mb-0"><i class="bi bi-person-heart me-2"></i>Register as a Volunteer</h4>
                </div>
                <div class="card-body p-4">
                     <p class="card-text mb-4">Help us bridge the gap! Register below and wait for admin approval to start helping.</p>

                     <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                             <h5 class="alert-heading">Please fix the following errors:</h5>
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success" role="alert">
                            <h5 class="alert-heading">Application Submitted!</h5>
                            <?php echo htmlspecialchars($success_message); ?>
                            <hr>
                            <a href="index.php" class="alert-link">Return to Homepage</a>
                        </div>
                    <?php else: // Hide form on success ?>

                    <form action="volunteer_register.php" method="POST" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php echo isset($errors['first_name']) ? 'is-invalid' : ''; ?>" id="first_name" name="first_name" value="<?php echo htmlspecialchars($form_values['first_name']); ?>" required>
                                 <div class="invalid-feedback"><?php echo htmlspecialchars($errors['first_name'] ?? ''); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?php echo isset($errors['last_name']) ? 'is-invalid' : ''; ?>" id="last_name" name="last_name" value="<?php echo htmlspecialchars($form_values['last_name']); ?>" required>
                                 <div class="invalid-feedback"><?php echo htmlspecialchars($errors['last_name'] ?? ''); ?></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($form_values['email']); ?>" required>
                             <div class="invalid-feedback"><?php echo htmlspecialchars($errors['email'] ?? ''); ?></div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" id="password" name="password" required aria-describedby="passwordHelp">
                                <div id="passwordHelp" class="form-text small">Must be at least 6 characters long.</div>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($errors['password'] ?? ''); ?></div>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" required>
                                 <div class="invalid-feedback"><?php echo htmlspecialchars($errors['confirm_password'] ?? ''); ?></div>
                            </div>
                        </div>

                         <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control <?php echo isset($errors['phone']) ? 'is-invalid' : ''; ?>" id="phone" name="phone" value="<?php echo htmlspecialchars($form_values['phone']); ?>" required aria-describedby="phoneHelp">
                             <div id="phoneHelp" class="form-text small">Required for pickup/delivery coordination.</div>
                             <div class="invalid-feedback"><?php echo htmlspecialchars($errors['phone'] ?? ''); ?></div>
                         </div>

                         <hr class="my-4">
                         <h5 class="mb-3">Optional Details</h5>

                         <div class="mb-3">
                            <label for="address" class="form-label">Street Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"><?php echo htmlspecialchars($form_values['address']); ?></textarea>
                         </div>

                         <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($form_values['city']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($form_values['postal_code']); ?>">
                            </div>
                         </div>

                         <div class="mb-3">
                            <label for="motivation" class="form-label">Why do you want to volunteer?</label>
                            <textarea class="form-control" id="motivation" name="motivation" rows="3"><?php echo htmlspecialchars($form_values['motivation']); ?></textarea>
                        </div>

                        <div class="d-grid gap-2 mt-4"> <!-- Full width button -->
                            <button type="submit" class="btn btn-success btn-lg text-white">Submit Volunteer Application</button>
                        </div>
                    </form>
                    <?php endif; // End hide form on success ?>
                </div><!-- /.card-body -->
                 <div class="card-footer text-center bg-light py-3">
                     <p class="mb-0">Already have an account? <a href="login.php">Login here</a>.</p>
                 </div>
            </div><!-- /.card -->

        </div><!-- /.col -->
    </div><!-- /.row -->
</div><!-- /.container -->

<?php require_once 'includes/footer.php'; // Include Bootstrap footer ?>