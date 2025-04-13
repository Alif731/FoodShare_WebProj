<?php
require_once 'includes/db_connect.php'; // Ensure session started and DB connected
//require_once 'includes/functions.php'; // Assuming you might have helper functions (optional)

$errors = [];
$success_message = '';
$submitted_email = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_forgot_password'])) {
    $submitted_email = trim($_POST['email'] ?? '');

    if (empty($submitted_email) || !filter_var($submitted_email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    } else {
        try {
            // Find user by email (only donors or volunteers)
            $sql_find = "SELECT user_id, first_name FROM users WHERE email = :email AND role IN ('donor', 'volunteer')";
            $stmt_find = $pdo->prepare($sql_find);
            $stmt_find->bindParam(':email', $submitted_email);
            $stmt_find->execute();
            $user = $stmt_find->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                // --- Generate Secure Token ---
                $token_bytes = random_bytes(32); // Generate 32 random bytes
                $token = bin2hex($token_bytes); // Convert to hexadecimal string (64 chars)

                // --- Generate Expiry Time (e.g., 1 hour from now) ---
                $expires = new DateTime('NOW');
                $expires->add(new DateInterval('PT1H')); // PT1H = Period Time 1 Hour
                $expiry_formatted = $expires->format('Y-m-d H:i:s');

                // --- Hash the Token for Storage ---
                // Use password_hash for consistency and security features (cost, algorithm)
                $token_hash = password_hash($token, PASSWORD_DEFAULT);

                // --- Update User Record ---
                $sql_update = "UPDATE users SET reset_token_hash = :token_hash, reset_token_expiry = :expiry WHERE user_id = :user_id";
                $stmt_update = $pdo->prepare($sql_update);
                $stmt_update->bindParam(':token_hash', $token_hash);
                $stmt_update->bindParam(':expiry', $expiry_formatted);
                $stmt_update->bindParam(':user_id', $user['user_id']);

                if ($stmt_update->execute()) {
                    // --- Construct Reset Link ---
                    // IMPORTANT: Replace 'https://yourdomain.com' with your actual domain and path
                    $reset_link = "http://localhost/FoodShare/reset_password.php?token=" . $token; // Use the RAW token in the link

                    // --- Send Email (Simulation) ---
                    // !! IMPORTANT !! In a real application, use a library like PHPMailer
                    // or an email service API (SendGrid, Mailgun) to send the actual email.
                    // Do NOT rely on PHP's mail() function on shared hosting without configuration.

                    $email_subject = "Password Reset Request - FoodShare Connect";
                    $email_body = "Hello " . htmlspecialchars($user['first_name']) . ",\n\n";
                    $email_body .= "You requested a password reset. Click the link below to set a new password:\n\n";
                    $email_body .= $reset_link . "\n\n";
                    $email_body .= "This link will expire in 1 hour.\n\n";
                    $email_body .= "If you did not request this, please ignore this email.\n\n";
                    $email_body .= "Regards,\nThe FoodShare Connect Team";

                    // Simulated sending: Display a success message and the link (for testing)
                    // In production, replace this block with actual email sending code
                    $success_message = "If an account with that email exists, a password reset link has been sent (check spam folder)."; // Generic message
                    // For testing ONLY - REMOVE THIS IN PRODUCTION
                    $success_message .= "<br><br><strong>Testing Only (Remove in Production):</strong><br>Reset Link: <a href='" . htmlspecialchars($reset_link) . "'>" . htmlspecialchars($reset_link) . "</a>";
                    // error_log("Password reset link for " . $submitted_email . ": " . $reset_link); // Log link for testing

                    // if (mail($submitted_email, $email_subject, $email_body)) {
                    //      $success_message = "If an account with that email exists, a password reset link has been sent.";
                    // } else {
                    //      error_log("Failed to send password reset email to: " . $submitted_email);
                    //      $errors[] = "Could not send the reset email. Please contact support.";
                    // }

                } else {
                    $errors[] = "Could not update user record. Please try again.";
                }

            } else {
                // Email not found or not a donor/volunteer
                // Show a generic success message to prevent user enumeration
                $success_message = "If an account with that email exists, a password reset link has been sent.";
            }
        } catch (PDOException $e) {
            error_log("Forgot Password Error: " . $e->getMessage());
            $errors[] = "An error occurred. Please try again later.";
        } catch (Exception $e) {
             error_log("Token Generation Error: " . $e->getMessage());
             $errors[] = "An error occurred while generating the reset link.";
        }
    }
}

require_once 'includes/header.php'; // Include Bootstrap header
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h4 class="mb-0"><i class="bi bi-key-fill me-2"></i>Forgot Password</h4>
                </div>
                <div class="card-body">
                    <p class="card-text">Enter the email address associated with your Donor or Volunteer account. We'll send you a link to reset your password.</p>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo $success_message; // Allow HTML for the test link ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!$success_message): // Only show form if not successfully submitted ?>
                    <form action="forgot_password.php" method="POST" novalidate>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control <?php echo (!empty($errors) && strpos(implode(' ', $errors), 'email') !== false) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($submitted_email); ?>" required>
                            <div class="invalid-feedback">
                                Please provide a valid email address.
                            </div>
                        </div>
                        <button type="submit" name="submit_forgot_password" class="btn btn-primary w-100">Send Password Reset Link</button>
                    </form>
                    <?php endif; ?>

                    <div class="mt-3 text-center">
                         <a href="login.php">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; // Include Bootstrap footer ?>