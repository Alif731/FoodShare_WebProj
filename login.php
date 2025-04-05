<link rel="stylesheet" href="css/index.css">


<?php
include 'includes/db_connect.php'; // Starts session
$error_message = '';

// Redirect if already logged in
if (isset($_SESSION["user_id"])) {
    if ($_SESSION["role"] === 'admin') {
        header("Location: admin/dashboard.php");
    } elseif ($_SESSION["role"] === 'volunteer') {
        header("Location: volunteer_dashboard.php");
    } else { // donor
        header("Location: donor_dashboard.php");
    }
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } else {
        try {
            $sql = "SELECT user_id, first_name, password, role, is_approved FROM users WHERE email = :email";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // Verify password
                if (password_verify($password, $user['password'])) {

                    // Check volunteer approval status
                    if ($user['role'] === 'volunteer' && $user['is_approved'] != 1) {
                         $error_message = "Your volunteer account is pending approval by an administrator.";
                    } else {
                        // Password is correct, start session
                        $_SESSION["user_id"] = $user['user_id'];
                        $_SESSION["first_name"] = $user['first_name'];
                        $_SESSION["role"] = $user['role'];

                        // Redirect based on role
                        if ($user['role'] === 'admin') {
                            header("Location: admin/dashboard.php");
                        } elseif ($user['role'] === 'volunteer') {
                            header("Location: volunteer_dashboard.php");
                        } else { // donor
                            header("Location: donor_dashboard.php");
                        }
                        exit; // Important: stop script execution after redirect
                    }
                } else {
                    // Password is not valid
                    $error_message = "Invalid email or password.";
                }
            } else {
                // No account found with that email
                $error_message = "Invalid email or password.";
            }
        } catch (PDOException $e) {
            error_log("Login failed: " . $e->getMessage());
            $error_message = "An error occurred during login. Please try again later.";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="form-container">
    <h2>Login to Your Account</h2>

    <?php if (!empty($error_message)): ?>
        <div class="error-message">
            <p><?php echo htmlspecialchars($error_message); ?></p>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="form-button">Login</button>
        <p class="form-link">Don't have an account? <a href="donor_register.php">Register as Donor</a> | <a href="volunteer_register.php">Register as Volunteer</a></p>
        <p class="form-link"><a href="#">Forgot Password?</a></p> <!-- Add password reset functionality later -->

    </form>
</div>

<?php include 'includes/footer.php'; ?>