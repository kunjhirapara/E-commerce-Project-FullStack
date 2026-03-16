<?php 
// Include security helper
require_once '../includes/security.php';

// Initialize secure session
Security::init_secure_session('ADMIN_SESSION');
  
include 'main-header.php'; 

$password = $confirmPassword = $passwordErr = $confirmPasswordErr = $successMsg = $tokenErr = "";
$validToken = false;
$redirectAfterReset = false;

// Check if token is provided
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    // Validate token from session (demo mode)
    if (isset($_SESSION['demo_admin_reset_token']) && $_SESSION['demo_admin_reset_token'] === $token && isset($_SESSION['demo_admin_reset_email'])) {
        $validToken = true;
        $resetEmail = $_SESSION['demo_admin_reset_email'];
    } else {
        $tokenErr = "Invalid or expired reset link. Please request a new one.";
    }
} else {
    $tokenErr = "No reset token provided.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $validToken) {
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Validate password
    if (empty($password)) {
        $passwordErr = "Password is required";
    } elseif (strlen($password) < 4) {
        $passwordErr = "Password must be at least 4 characters";
    }
    
    if (empty($confirmPassword)) {
        $confirmPasswordErr = "Please confirm your password";
    } elseif ($password !== $confirmPassword) {
        $confirmPasswordErr = "Passwords do not match";
    }
    
    // If no errors, update password
    if (empty($passwordErr) && empty($confirmPasswordErr)) {
        $hashedPassword = Security::hash_password($password);
        
        $stmt = $conn->prepare("UPDATE admin_sign_in SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $hashedPassword, $resetEmail);
        
        if ($stmt->execute()) {
            $successMsg = "Admin password successfully reset! Redirecting to sign in...";
          $redirectAfterReset = true;
            
            // Clear reset token
            unset($_SESSION['demo_admin_reset_token']);
            unset($_SESSION['demo_admin_reset_email']);
        } else {
            $passwordErr = "Error resetting password. Please try again.";
        }
        
        $stmt->close();
    }
}
?>

<style>
.auth-section {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.75rem;
}

.auth-card {
  background: white;
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  overflow: hidden;
  max-width: 480px;
  width: 100%;
  animation: slideUp 0.4s ease-out;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.auth-header {
  background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
  color: white;
  padding: 1.75rem 1.5rem;
  text-align: center;
}

.auth-header h2 {
  margin: 0 0 0.25rem 0;
  font-size: 1.6rem;
  font-weight: 700;
}

.auth-header p {
  margin: 0;
  font-size: 0.88rem;
  opacity: 0.9;
}

.error-badge {
  background: rgba(220, 53, 69, 0.1);
  border-left: 4px solid #dc3545;
  padding: 0.7rem 1rem;
  margin: 0.85rem 1.5rem;
  border-radius: 6px;
  font-size: 0.82rem;
  display: flex;
  align-items: start;
  gap: 0.5rem;
}

.error-badge i {
  color: #dc3545;
  font-size: 1.05rem;
  flex-shrink: 0;
  margin-top: 2px;
}

.success-badge {
  background: rgba(25, 135, 84, 0.1);
  border-left: 4px solid #198754;
  padding: 0.7rem 1rem;
  margin: 0.85rem 1.5rem;
  border-radius: 6px;
  font-size: 0.82rem;
  display: flex;
  align-items: start;
  gap: 0.5rem;
}

.success-badge i {
  color: #198754;
  font-size: 1.05rem;
  flex-shrink: 0;
  margin-top: 2px;
}

.auth-body {
  padding: 1.5rem 1.5rem;
}

.form-floating-custom {
  position: relative;
  margin-bottom: 1.1rem;
}

.form-floating-custom input {
  width: 100%;
  height: 50px;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  padding: 0.75rem 1rem;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

.form-floating-custom input:focus {
  border-color: #ee0979;
  box-shadow: 0 0 0 3px rgba(238, 9, 121, 0.1);
  outline: none;
}

.form-floating-custom label {
  position: absolute;
  top: 50%;
  left: 1rem;
  transform: translateY(-50%);
  font-size: 0.95rem;
  color: #666;
  transition: all 0.3s ease;
  pointer-events: none;
  background: white;
  padding: 0 0.25rem;
}

.form-floating-custom input:focus~label,
.form-floating-custom input:not(:placeholder-shown)~label {
  top: 0;
  font-size: 0.75rem;
  color: #ee0979;
  font-weight: 600;
}

.submit-btn {
  width: 100%;
  height: 50px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%);
  color: white;
  font-size: 0.98rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 0.4rem;
}

.submit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(238, 9, 121, 0.4);
}

.submit-btn:active {
  transform: translateY(0);
}

.back-link {
  text-align: center;
  padding: 1rem;
  font-size: 0.88rem;
  color: #666;
  border-top: 1px solid #f0f0f0;
}

.back-link a {
  color: #ee0979;
  text-decoration: none;
  font-weight: 600;
}

.back-link a:hover {
  text-decoration: underline;
}

.message-error {
  display: block;
  color: #dc3545;
  font-size: 0.75rem;
  margin-top: 0.25rem;
  margin-left: 0.5rem;
}

@media (max-width: 576px) {
  .auth-card {
    border-radius: 0;
  }

  .auth-header {
    padding: 1.5rem 1rem;
  }

  .auth-header h2 {
    font-size: 1.5rem;
  }

  .auth-body {
    padding: 1.5rem 1rem;
  }
}
</style>

<section class="auth-section">
  <div class="auth-card">
    <div class="auth-header">
      <h2>Create New Admin Password</h2>
      <p>Set a strong password for your admin account</p>
    </div>

    <?php if ($tokenErr): ?>
    <div class="error-badge">
      <i class="fa-solid fa-exclamation-triangle"></i>
      <div><?php echo $tokenErr; ?></div>
    </div>
    <div class="back-link">
      <a href="forgot-password.php"><i class="fa-solid fa-arrow-left"></i> Request new reset link</a>
    </div>
    <?php elseif ($successMsg): ?>
    <div class="success-badge">
      <i class="fa-solid fa-check-circle"></i>
      <div><?php echo $successMsg; ?></div>
    </div>
    <?php if ($redirectAfterReset): ?>
    <script>
      setTimeout(function() {
        window.location.href = "signin.php";
      }, 3000);
    </script>
    <?php endif; ?>
    <?php else: ?>
    <div class="auth-body">
      <form method="post"
        action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?token=' . urlencode($token); ?>">
        <div class="form-floating-custom">
          <input type="password" name="password" id="password" placeholder=" " autocomplete="new-password" required>
          <label for="password">New Admin Password</label>
          <?php if($passwordErr): ?>
          <span class="message-error"><?php echo $passwordErr; ?></span>
          <?php endif; ?>
        </div>

        <div class="form-floating-custom">
          <input type="password" name="confirmPassword" id="confirmPassword" placeholder=" " autocomplete="new-password"
            required>
          <label for="confirmPassword">Confirm Password</label>
          <?php if($confirmPasswordErr): ?>
          <span class="message-error"><?php echo $confirmPasswordErr; ?></span>
          <?php endif; ?>
        </div>

        <button type="submit" class="submit-btn">Reset Admin Password</button>
      </form>
    </div>

    <div class="back-link">
      <a href="signin.php"><i class="fa-solid fa-arrow-left"></i> Back to Admin Sign In</a>
    </div>
    <?php endif; ?>
  </div>
</section>

</body>

</html>