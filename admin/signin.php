<?php
// Include security helper
require_once '../includes/security.php';
require_once __DIR__ . '/../config.php';

// Initialize secure session for admin
Security::init_secure_session('ADMIN_SESSION');

// Check if admin is already signed in
if (isset($_SESSION['admin_email']) && isset($_SESSION['admin_logged_in'])) {
    // Check if session is still valid (not expired)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) <= 1800) {
        // User is already logged in, redirect to dashboard
        header("Location: dashboard.php");
        exit();
    }
}

$email = $password = $emailErr = $passwordErr = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("
        SELECT a.password, r.role AS role_name 
        FROM admin_sign_in a
        JOIN roles r ON a.role_id = r.id
        WHERE a.email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($db_password, $role_name);
        $stmt->fetch();

        // Check if password is MD5 (legacy) and verify/upgrade
        if (strlen($db_password) === 32 && ctype_xdigit($db_password)) {
            // Legacy MD5 password - verify and upgrade
            if (md5($password) === $db_password) {
                // Password correct - upgrade to modern hash
                $stmt->close();
                $new_hash = Security::hash_password($password);
                $update_stmt = $conn->prepare("UPDATE admin_sign_in SET password = ? WHERE email = ?");
                $update_stmt->bind_param("ss", $new_hash, $email);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Set session variables
                $_SESSION['admin_email'] = $email;
                $_SESSION['admin_logged_in'] = $email;
                $_SESSION['admin_role'] = $role_name;
                $_SESSION['last_activity'] = time();

                if ($role_name === 'admin') {
                  header("Location: dashboard.php");
                }else{
                  header("Location: login.php");
                }
                exit();
            } else {
                $passwordErr = "Invalid email or password.";
            }
        } else {
            // Modern password hash - use password_verify()
            if (Security::verify_password($password, $db_password)) {
                $stmt->close();
                
                // Check if hash needs upgrading (algorithm improved)
                if (Security::needs_rehash($db_password)) {
                    $new_hash = Security::hash_password($password);
                    $update_stmt = $conn->prepare("UPDATE admin_sign_in SET password = ? WHERE email = ?");
                    $update_stmt->bind_param("ss", $new_hash, $email);
                    $update_stmt->execute();
                    $update_stmt->close();
                }
                
                // Set session variables
                $_SESSION['admin_email'] = $email;
                $_SESSION['admin_logged_in'] = $email;
                $_SESSION['admin_role'] = $role_name;
                $_SESSION['last_activity'] = time();

                if ($role_name === 'admin') {
                  header("Location: dashboard.php");
                }else{
                  header("Location: login.php");
                }
                exit();
            } else {
                $passwordErr = "Incorrect password";
            }
        }
    } else {
        $emailErr = "Email not found";
    }

    $stmt->close();
    $conn->close();
}

  include 'main-header.php';
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

.privacy-badge {
  background: rgba(13, 202, 240, 0.1);
  border-left: 4px solid #0dcaf0;
  padding: 0.7rem 1rem;
  margin: 0.85rem 1.5rem;
  border-radius: 6px;
  font-size: 0.8rem;
  display: flex;
  align-items: start;
  gap: 0.5rem;
}

.privacy-badge i {
  color: #0a7ea4;
  font-size: 1.05rem;
  flex-shrink: 0;
  margin-top: 2px;
}

.privacy-badge strong {
  color: #055160;
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
  border-color: #2a5298;
  box-shadow: 0 0 0 3px rgba(42, 82, 152, 0.1);
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
  color: #2a5298;
  font-weight: 600;
}

.submit-btn {
  width: 100%;
  height: 48px;
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

.auth-footer {
  text-align: center;
  padding: 1rem;
  font-size: 0.88rem;
  color: #666;
  border-top: 1px solid #f0f0f0;
}

.auth-footer a {
  color: #ee0979;
  text-decoration: none;
  font-weight: 600;
}

.auth-footer a:hover {
  text-decoration: underline;
}

.message-error {
  display: block;
  color: #dc3545;
  font-size: 0.72rem;
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

  .privacy-badge {
    margin: 1rem;
    font-size: 0.75rem;
  }
}
</style>

<section class="auth-section">
  <div class="auth-card">
    <div class="auth-header">
      <h2>Admin Portal</h2>
      <p>Management Dashboard Access</p>
    </div>

    <div class="privacy-badge">
      <i class="fa-solid fa-info-circle"></i>
      <div>
        <strong>Demo Admin Panel:</strong> Portfolio demo - Do not use real credentials or data.
      </div>
    </div>

    <div class="auth-body">
      <form method="post" action="">
        <div class="form-floating-custom">
          <input type="email" name="email" id="email" placeholder="Email..." value="<?php echo htmlspecialchars($email ?: 'hubo@mailinator.com'); ?>" autocomplete="email" required>
          <label for="email">Admin Email</label>
          <?php if($emailErr): ?>
          <span class="message-error"><?php echo $emailErr; ?></span>
          <?php endif; ?>
        </div>

        <div class="form-floating-custom">
          <input type="password" name="password" id="password" placeholder="Password..." value="1234" autocomplete="current-password" required>
          <label for="password">Password</label>
          <?php if($passwordErr): ?>
          <span class="message-error"><?php echo $passwordErr; ?></span>
          <?php endif; ?>
        </div>

        <div style="text-align: right; margin-bottom: 0.5rem;">
          <a href="forgot-password.php"
            style="font-size: 0.85rem; color: #ee0979; text-decoration: none; font-weight: 500;">Forgot password?</a>
        </div>

        <button type="submit" class="submit-btn">Sign In to Dashboard</button>
      </form>
    </div>

    <div class="auth-footer">
      Need admin access? <a href="signup.php">Request account</a>
    </div>
  </div>
</section>

</body>

</html>
</div>
</form>
</div>
</div>
</div>
</section>

</body>

</html>