<link rel="stylesheet" href="css/index.css">

<?php
include 'includes/db_connect.php';
$errors = [];
$success_message = '';

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
    $role = 'donor'; // Hardcoded for this form

    // Basic Validation
    if (empty($first_name)) $errors[] = "First name is required.";
    if (empty($last_name)) $errors[] = "Last name is required.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (empty($password)) $errors[] = "Password is required.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long."; // Basic length check
    // Add more validation as needed (phone format, etc.)

    // Check if email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = :email");
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $errors[] = "An account with this email already exists.";
            }
        } catch (PDOException $e) {
            error_log("Email check failed: " . $e->getMessage());
            $errors[] = "Database error during registration. Please try again.";
        }
    }

    // If no errors, proceed with insertion
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password!
        $is_approved = 1; // Donors are auto-approved

        try {
            $sql = "INSERT INTO users (first_name, last_name, email, password, phone, address, city, postal_code, role, is_approved)
                    VALUES (:first_name, :last_name, :email, :password, :phone, :address, :city, :postal_code, :role, :is_approved)";
            $stmt = $pdo->prepare($sql);

            $stmt->bindParam(':first_name', $first_name);
            $stmt->bindParam(':last_name', $last_name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashed_password);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':city', $city);
            $stmt->bindParam(':postal_code', $postal_code);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':is_approved', $is_approved, PDO::PARAM_BOOL);

            if ($stmt->execute()) {
                $success_message = "Registration successful! You can now <a href='login.php'>log in</a>.";
                // Optionally: Redirect to login page after a delay
                // header("refresh:3;url=login.php");
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        } catch (PDOException $e) {
            error_log("Donor registration failed: " . $e->getMessage());
            $errors[] = "An error occurred during registration. Please try again later."; // User-friendly message
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="form-container">
    <h2>Register as a Food Donor</h2>
    <p>Thank you for your generosity! Please fill out the form below.</p>

    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($success_message): ?>
        <div class="success-message">
            <?php echo $success_message; // Contains HTML link, so no htmlspecialchars here ?>
        </div>
    <?php else: // Hide form on success ?>
    <form action="donor_register.php" method="POST">
        <div class="form-group">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
         <div class="form-group">
            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
         <div class="form-group">
            <label for="phone">Phone Number (Optional):</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
        </div>
         <div class="form-group">
            <label for="address">Default Pickup Address (Optional):</label>
            <textarea id="address" name="address"><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
        </div>
         <div class="form-group">
            <label for="city">City (Optional):</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>">
        </div>
         <div class="form-group">
            <label for="postal_code">Postal Code (Optional):</label>
            <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($_POST['postal_code'] ?? ''); ?>">
        </div>
        <button type="submit" class="form-button">Register as Donor</button>
        <p class="form-link">Already have an account? <a href="login.php">Login here</a>.</p>
    </form>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>