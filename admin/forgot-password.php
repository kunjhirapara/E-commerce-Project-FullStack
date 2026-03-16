<?php 
// Include security helper
require_once '../includes/security.php';

// Initialize secure session
Security::init_secure_session('ADMIN_SESSION');
  
include 'main-header.php'; 

$email = $emailErr = $successMsg = "";
$resetSent = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $emailErr = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailErr = "Invalid email format";
    } else {
        // Check if admin email exists
        $stmt = $conn->prepare("SELECT id, userName FROM admin_sign_in WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            
            // Generate a secure random token
            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $successMsg = "Password reset instructions have been sent to your admin email address.";
            $resetSent = true;
            
            // For demo purposes, store in session
            $_SESSION['demo_admin_reset_token'] = $token;
            $_SESSION['demo_admin_reset_email'] = $email;
            
        } else {
            // For security, don't reveal if email exists or not
            $successMsg = "If that email address is registered as an admin, you will receive password reset instructions.";
            $resetSent = true;
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
  position: relative;
}

.top-right-link {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  background: rgba(255, 255, 255, 0.95);
  padding: 0.6rem 1.2rem;
  border-radius: 25px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  font-size: 0.9rem;
  color: #555;
  text-decoration: none;
  transition: all 0.3s ease;
  backdrop-filter: blur(10px);
  z-index: 10;
}

.top-right-link a {
  color: #ee0979;
  text-decoration: none;
  font-weight: 600;
  margin-left: 0.3rem;
}

.top-right-link:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

.top-right-link a:hover {
  text-decoration: underline;
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

.info-badge {
  background: rgba(13, 202, 240, 0.1);
  border-left: 4px solid #0dcaf0;
  padding: 0.7rem 1rem;
  margin: 0.85rem 1.5rem;
  border-radius: 6px;
  font-size: 0.82rem;
  display: flex;
  align-items: start;
  gap: 0.5rem;
}

.info-badge i {
  color: #0a7ea4;
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

.demo-link {
  background: rgba(255, 193, 7, 0.1);
  border-left: 4px solid #ffc107;
  padding: 0.7rem 1rem;
  margin: 0.85rem 1.5rem;
  border-radius: 6px;
  font-size: 0.78rem;
  word-break: break-all;
}

.demo-link strong {
  display: block;
  margin-bottom: 0.5rem;
  color: #856404;
}

.demo-link a {
  color: #ee0979;
  text-decoration: underline;
  font-weight: 600;
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
  .top-right-link {
    top: 1rem;
    right: 1rem;
    font-size: 0.8rem;
    padding: 0.5rem 1rem;
  }

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

  .info-badge,
  .success-badge,
  .demo-link {
    margin: 1rem;
    font-size: 0.75rem;
  }
}
</style>

<section class="auth-section">
  <div class="top-right-link">
    Remember your password? <a href="signin.php">Sign in</a>
  </div>

  <div class="auth-card">
    <div class="auth-header">
      <h2>Admin Password Reset</h2>
      <p>Recover access to your admin dashboard</p>
    </div>

    <?php if (!$resetSent): ?>
    <div class="info-badge">
      <i class="fa-solid fa-info-circle"></i>
      <div>
        Enter your admin email address and we'll send you instructions to reset your password.
      </div>
    </div>

    <div class="auth-body">
      <form method="post" action="">
        <div class="form-floating-custom">
          <input type="email" name="email" id="email" placeholder=" " autocomplete="email" required
            value="<?php echo htmlspecialchars($email); ?>">
          <label for="email">Admin Email Address</label>
          <?php if($emailErr): ?>
          <span class="message-error"><?php echo $emailErr; ?></span>
          <?php endif; ?>
        </div>

        <button type="submit" class="submit-btn">Send Reset Instructions</button>
      </form>
    </div>
    <?php else: ?>
    <div class="success-badge">
      <i class="fa-solid fa-check-circle"></i>
      <div>
        <?php echo $successMsg; ?>
      </div>
    </div>

    <?php if (isset($_SESSION['demo_admin_reset_token'])): ?>
    <div class="demo-link">
      <strong>🔧 Demo Mode - Admin Reset Link:</strong>
      <a href="reset-password.php?token=<?php echo urlencode($_SESSION['demo_admin_reset_token']); ?>">
        Click here to reset your admin password
      </a>
      <br><small>(In production, this would be sent to your email)</small>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <div class="back-link">
      <a href="signin.php"><i class="fa-solid fa-arrow-left"></i> Back to Admin Sign In</a>
    </div>
  </div>
</section>

</body>

</html>