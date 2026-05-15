<?php 
// Include security helper
require_once 'includes/security.php';

// Initialize secure session
Security::init_secure_session('USER_SESSION');
  
  // Check if user is already signed in
  if (isset($_SESSION['email']) && isset($_SESSION['userLogId'])) {
    // Check if session is still valid (not expired)
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) <= 1800) {
      // User is already logged in, redirect to home page
      header("Location: my-dashboard.php");
      exit();
    }
  }
  
  include 'main-header.php'; 
$email = $password = $emailErr = $passwordErr = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password, id FROM user_sign_in WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $stmt->bind_result($db_password, $userLogId);
      $stmt->fetch();

      // Check if password is MD5 (legacy) and verify/upgrade
      if (strlen($db_password) === 32 && ctype_xdigit($db_password)) {
        // Legacy MD5 password - verify and upgrade
        if (md5($password) === $db_password) {
          // Password correct - upgrade to modern hash
          $stmt->close();
          $new_hash = Security::hash_password($password);
          $update_stmt = $conn->prepare("UPDATE user_sign_in SET password = ? WHERE email = ?");
          $update_stmt->bind_param("ss", $new_hash, $email);
          $update_stmt->execute();
          $update_stmt->close();
          
          // Set session variables and mark as logged in
          $_SESSION['email'] = $email;
          $_SESSION['userLogId'] = $userLogId;
          $_SESSION['last_activity'] = time();
          $_SESSION['login_success'] = true;
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
            $update_stmt = $conn->prepare("UPDATE user_sign_in SET password = ? WHERE email = ?");
            $update_stmt->bind_param("ss", $new_hash, $email);
            $update_stmt->execute();
            $update_stmt->close();
          }
          
          // Set session variables and mark as logged in
          $_SESSION['email'] = $email;
          $_SESSION['userLogId'] = $userLogId;
          $_SESSION['last_activity'] = time();
          $_SESSION['login_success'] = true;
        } else {
          $passwordErr = "Incorrect password";
        }
      }
      
      // If login was successful, handle cart and redirect
      if (isset($_SESSION['login_success'])) {
        unset($_SESSION['login_success']);
        ?>
<script>
const cartItem = localStorage.getItem("cartProducts");
localStorage.removeItem("cartProducts")
console.log("cartItem: " + cartItem);
if (cartItem) {
  const cartArray = JSON.parse(cartItem);

  cartArray.forEach(item => {
    const id = item.id;
    const quantity = item.productQuantity;

    fetch("./assets/api/add_to_cart_product.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        product_id: id,
        quantity: quantity,
      }),
    })

  });
} else {
  console.log("No cart items found in localStorage");
}
setTimeout(() => {
  window.location.href = "index.php";
}, 50);
</script>
<?php
        exit();
      }
    } else {
      $emailErr = "Email not found";
    }

    $stmt->close();
    $conn->close();
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
  background: linear-gradient(135deg, #134e5e 0%, #71b280 100%);
  color: white;
  padding: 1.75rem 1.5rem;
  text-align: center;
}

.auth-header h2 {
  margin: 0;
  font-size: 1.6rem;
  font-weight: 700;
}

.privacy-badge {
  background: rgba(255, 193, 7, 0.15);
  border-left: 4px solid #ffc107;
  padding: 0.7rem 1rem;
  margin: 0.85rem 1.5rem;
  border-radius: 6px;
  font-size: 0.8rem;
  display: flex;
  align-items: start;
  gap: 0.5rem;
}

.privacy-badge i {
  color: #f57c00;
  font-size: 1.05rem;
  flex-shrink: 0;
  margin-top: 2px;
}

.privacy-badge strong {
  color: #e65100;
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
  border-color: #134e5e;
  box-shadow: 0 0 0 3px rgba(19, 78, 94, 0.1);
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
  color: #134e5e;
  font-weight: 600;
}

.submit-btn {
  width: 100%;
  height: 50px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(135deg, #134e5e 0%, #71b280 100%);
  color: white;
  font-size: 0.98rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 0.4rem;
}

.submit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(19, 78, 94, 0.4);
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
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
}

.auth-footer a:hover {
  text-decoration: underline;
}

.error {
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

  .privacy-badge {
    margin: 1rem;
    font-size: 0.75rem;
  }
}
</style>

<section class="auth-section">
  <div class="auth-card">
    <div class="auth-header">
      <h2>Welcome Back</h2>
    </div>

    <div class="privacy-badge">
      <i class="fa-solid fa-triangle-exclamation"></i>
      <div>
        <strong>Demo Project:</strong> Do not use real personal information, emails, or photos.
      </div>
    </div>

    <div class="auth-body">
      <form method="post" action="">
        <div class="form-floating-custom">
          <input type="email" name="email" id="email" placeholder="Email..." default="hubo@mailinator.com" autocomplete="email" required>
          <label for="email">Email Address</label>
          <?php if($emailErr): ?>
          <span class="error"><?php echo $emailErr; ?></span>
          <?php endif; ?>
        </div>

        <div class="form-floating-custom">
          <input type="password" name="password" id="password" placeholder="Password..." default="1234" autocomplete="current-password" required>
          <label for="password">Password</label>
          <?php if($passwordErr): ?>
          <span class="error"><?php echo $passwordErr; ?></span>
          <?php endif; ?>
        </div>

        <div style="text-align: right; margin-bottom: 0.5rem;">
          <a href="forgot-password.php" style="font-size: 0.85rem; color: #134e5e; text-decoration: none; font-weight: 500;">Forgot password?</a>
        </div>

        <button type="submit" class="submit-btn">Sign In</button>
      </form>
    </div>

    <div class="auth-footer">
      Don't have an account? <a href="signup.php">Create one here</a>
    </div>
  </div>
</section>

</body>

</html>