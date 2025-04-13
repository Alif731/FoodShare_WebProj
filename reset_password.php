<?php
require_once 'includes/db_connect.php';
//require_once 'includes/functions.php'; // Optional

$errors = [];
$success_message = '';
$token_valid = false;
$user_id_to_reset = null;
$token_from_url = ''; // Store raw token for form submission

// --- 1. Validate Token on Page Load (GET Request) ---
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['token'])) {
    $token_from_url = $_GET['token'];

    // Basic token validation (e.g., check length)
    if (empty($token_from_url) || !ctype_xdigit($token_from_url) || strlen($token_from_url) !== 64) { // 32 bytes = 64 hex chars
        $errors[] = "Invalid token format.";
    } else {
        try {
            // Find user based on the RAW token by checking against the HASHED token in DB
            // This requires iterating or a more complex query if many users request resets.
            // A selector/validator approach is more efficient for lookup.
            // For this example, we fetch all potentially valid hashes first.

            $sql_find_token = "SELECT user_id, reset_token_hash, reset_token_expiry FROM users WHERE reset_token_expiry > NOW() AND reset_token_hash IS NOT NULL";
            $stmt_find_token = $pdo->query($sql_find_token);

            while ($row = $stmt_find_token->fetch(PDO::FETCH_ASSOC)) {
                // Verify the raw token from URL against the stored hash
                if (password_verify($token_from_url, $row['reset_token_hash'])) {
                    // Token matches! Check expiry just in case (redundant with query but safe)
                     $expiry_dt = new DateTime($row['reset_token_expiry']);
                     if ($expiry_dt > new DateTime('NOW')) {
                        $token_valid = true;
                        $user_id_to_reset = $row['user_id'];
                        break; // Found valid user, stop checking
                     }
                }
            }

            if (!$token_valid) {
                $errors[] = "Password reset link is invalid or has expired.";
            }

        } catch (PDOException $e) {
            error_log("Reset Password Token Check Error: " . $e->getMessage());
            $errors[] = "An error occurred while validating the reset link.";
        } catch (Exception $e) {
             error_log("Reset Password Date Check Error: " . $e->getMessage());
             $errors[] = "An error occurred while validating the reset link expiry.";
        }
    }
}
// --- 2. Handle Form Submission (POST Request) ---
elseif ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_reset_password'])) {
    $token_from_form = $_POST['token'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Re-validate token from hidden field (prevents submitting form after expiry)
     if (empty($token_from_form) || !ctype_xdigit($token_from_form) || strlen($token_from_form) !== 64) {
        $errors[] = "Invalid request (missing token).";
    } else {
         // Perform the same token validation as in the GET request
         try {
             $sql_recheck = "SELECT user_id, reset_token_hash, reset_token_expiry FROM users WHERE reset_token_expiry > NOW() AND reset_token_hash IS NOT NULL";
             $stmt_recheck = $pdo->query($sql_recheck);
             $recheck_valid = false;
             $recheck_user_id = null;
             while ($row = $stmt_recheck->fetch(PDO::FETCH_ASSOC)) {
                 if (password_verify($token_from_form, $row['reset_token_hash'])) {
                     $recheck_valid = true;
                     $recheck_user_id = $row['user_id'];
                     break;
                 }
             }
             if (!$recheck_valid) {
                  $errors[] = "Password reset link is invalid or has expired. Please request a new one.";
             } else {
                 // Token is still valid, proceed with password validation
                 if (empty($password) || empty($confirm_password)) {
                     $errors[] = "Both password fields are required.";
                 } elseif ($password !== $confirm_password) {
                     $errors[] = "Passwords do not match.";
                 } elseif (strlen($password) < 6) { // Basic length check - add more complex rules!
                     $errors[] = "Password must be at least 6 characters long.";
                 }

                 // If no validation errors, update the password
                 if (empty($errors)) {
                     $new_password_hash = password_hash($password, PASSWORD_DEFAULT);

                     // Update password AND clear the reset token fields
                     $sql_update_pass = "UPDATE users SET password = :password, reset_token_hash = NULL, reset_token_expiry = NULL WHERE user_id = :user_id";
                     $stmt_update_pass = $pdo->prepare($sql_update_pass);
                     $stmt_update_pass->bindParam(':password', $new_password_hash);
                     $stmt_update_pass->bindParam(':user_id', $recheck_user_id);

                     if ($stmt_update_pass->execute()) {
                         $success_message = "Your password has been successfully updated! You can now log in with your new password.";
                         $token_valid = false; // Prevent form from showing again
                     } else {
                         $errors[] = "Failed to update password. Please try again.";
                     }
                 }
             }

         } catch (PDOException $e) {
             error_log("Reset Password Submit Error: " . $e->getMessage());
             $errors[] = "An database error occurred. Please try again later.";
         } catch (Exception $e) {
             error_log("Reset Password General Error: " . $e->getMessage());
             $errors[] = "An unexpected error occurred.";
         }
    }
     // Need to pass the token again if there were errors on POST
     if(!empty($errors)) {
        $token_valid = true; // Allow form redisplay
        $token_from_url = $token_from_form; // Keep token for hidden field
     }
}
// --- 3. Handle case where no token is provided ---
elseif ($_SERVER["REQUEST_METHOD"] == "GET" && !isset($_GET['token'])) {
     $errors[] = "No reset token provided. Please use the link from your email.";
}


require_once 'includes/header.php'; // Include Bootstrap header
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="bi bi-shield-lock-fill me-2"></i>Reset Your Password</h4>
                </div>
                <div class="card-body">

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php foreach ($errors as $error): ?>
                                <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                             <?php if(!$token_valid): // Add link only if token invalid/missing ?>
                                <hr>
                                <a href="forgot_password.php" class="alert-link">Request a new reset link?</a>
                             <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success" role="alert">
                            <?php echo htmlspecialchars($success_message); ?>
                            <hr>
                             <a href="login.php" class="alert-link">Proceed to Login</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($token_valid && !$success_message): // Show form only if token is valid and not successfully reset yet ?>
                    <form action="reset_password.php" method="POST" novalidate>
                        <!-- Include token in hidden field to pass it during POST -->
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token_from_url); ?>">

                        <div class="mb-3">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" class="form-control <?php echo (!empty($errors) && strpos(implode(' ', $errors), 'Password') !== false) ? 'is-invalid' : ''; ?>" id="password" name="password" required aria-describedby="passwordHelp">
                             <div id="passwordHelp" class="form-text">
                                Must be at least 6 characters long.
                            </div>
                            <div class="invalid-feedback">
                                Please enter a valid new password.
                            </div>
                        </div>
                         <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control <?php echo (!empty($errors) && strpos(implode(' ', $errors), 'match') !== false) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" required>
                            <div class="invalid-feedback">
                                Passwords do not match.
                            </div>
                        </div>
                        <button type="submit" name="submit_reset_password" class="btn btn-primary w-100">Update Password</button>
                    </form>
                     <?php endif; ?>

                     <?php if (!$token_valid && empty($errors) && empty($success_message) && $_SERVER["REQUEST_METHOD"] == "GET"):
                        // Initial state if accessed without a token and no errors yet
                        echo '<p class="text-muted">Please use the password reset link sent to your email.</p>';
                        echo '<a href="forgot_password.php">Request a new link?</a>';
                     endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; // Include Bootstrap footer ?>