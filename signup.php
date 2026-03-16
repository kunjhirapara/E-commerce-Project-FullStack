<?php 
// Include security helper
require_once 'includes/security.php';
require_once __DIR__ . '/config.php';

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


$email = $password = $userName = $Upload = $emailErr = $passwordErr = $userNameErr = $UploadErr ="";

function test_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";  
    } else {
        $email = test_input($_POST["email"]);
        if (!preg_match("/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$/", $email)) {
            $emailErr = "Invalid email format, spaces & backslashes are not allowed";
        }
    }

    if (empty($_POST["userName"])) {
        $userNameErr = "Username is required";
    } else {
        $userName = test_input($_POST["userName"]);
        if (!preg_match("/^[a-zA-Z0-9._-]{3,20}$/", $userName)) {
            $userNameErr = "Only letters, numbers, dots, underscores, and hyphens are allowed (3-20 characters)";
        }
    }

    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = test_input($_POST["password"]);

        if (!preg_match("/^[a-zA-Z0-9._-]{3,20}$/", $password)) {
            $passwordErr = "Only alphabets, numbers, and . _ - are allowed for Password, length 3 to 20 characters.";
        }
    }

    $UploadErr = [];

    if (isset($_FILES['Upload'])) {
      if( $emailErr === "" && $passwordErr === "" && $userNameErr === ""){
        $uploadResult = Security::store_uploaded_image($_FILES['Upload'], __DIR__ . '/assets/images/user-sign-up-uploads/', 'user_');
        if ($uploadResult['success']) {
          $Upload = $uploadResult['filename'];
        } else {
          $UploadErr[] = $uploadResult['error'];
        }
      }else{
        $UploadErr[] = 'It seems tha the fields above are not correct.';
      }
    } else {
        $UploadErr[] = 'No file uploaded or selected.';
    }



    $stmt = $conn->prepare("SELECT id FROM user_sign_in WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
      $emailErr = "Email already registered.";
      $stmt->close();
    } else {
      $stmt->close();
      if ($emailErr === "" && $passwordErr === "" && $userNameErr === "" && count($UploadErr) === 0) {
        // Use modern password hashing (bcrypt or Argon2)
        $hashed_password = Security::hash_password($password);
        $stmt = $conn->prepare("INSERT INTO user_sign_in(email, password, username, user_img) VALUES (?, ?, ?,?)");
        $stmt->bind_param("ssss", $email, $hashed_password, $userName, $Upload);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header("Location: signin.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
            $stmt->close();
        }
      }
    }

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
  color: #2c5364;
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
  max-width: 500px;
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
  background: linear-gradient(135deg, #0f2027 0%, #2c5364 100%);
  color: white;
  padding: 1.25rem 1.5rem;
  text-align: center;
}

.auth-header h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 700;
}

.privacy-badge {
  background: rgba(255, 193, 7, 0.15);
  border-left: 4px solid #ffc107;
  padding: 0.65rem 1rem;
  margin: 0.75rem 1.5rem;
  border-radius: 6px;
  font-size: 0.78rem;
  display: flex;
  align-items: start;
  gap: 0.5rem;
}

.privacy-badge i {
  color: #f57c00;
  font-size: 1rem;
  flex-shrink: 0;
  margin-top: 2px;
}

.privacy-badge strong {
  color: #e65100;
}

.auth-body {
  padding: 1.25rem 1.5rem;
}

.form-floating-custom {
  position: relative;
  margin-bottom: 0.85rem;
}

.form-floating-custom input,
.form-floating-custom .form-control {
  width: 100%;
  height: 48px;
  border: 2px solid #e0e0e0;
  border-radius: 10px;
  padding: 0.75rem 1rem;
  font-size: 0.92rem;
  transition: all 0.3s ease;
  box-sizing: border-box;
}

.form-floating-custom input:focus,
.form-floating-custom .form-control:focus {
  border-color: #2c5364;
  box-shadow: 0 0 0 3px rgba(44, 83, 100, 0.1);
  outline: none;
}

.form-floating-custom label {
  position: absolute;
  top: 50%;
  left: 1rem;
  transform: translateY(-50%);
  font-size: 0.92rem;
  color: #666;
  transition: all 0.3s ease;
  pointer-events: none;
  background: white;
  padding: 0 0.25rem;
}

.form-floating-custom input:focus~label,
.form-floating-custom input:not(:placeholder-shown)~label {
  top: 0;
  font-size: 0.72rem;
  color: #2c5364;
  font-weight: 600;
}

.file-upload-wrapper {
  margin-bottom: 0.85rem;
}

.file-upload-label {
  display: block;
  font-size: 0.82rem;
  font-weight: 600;
  color: #333;
  margin-bottom: 0.4rem;
}

.file-upload-input {
  width: 100%;
  border: 2px dashed #e0e0e0;
  border-radius: 10px;
  padding: 0.85rem;
  text-align: center;
  transition: all 0.3s ease;
  cursor: pointer;
  background: #f8f9fa;
  box-sizing: border-box;
}

.file-upload-input:hover {
  border-color: #667eea;
  background: #f0f2ff;
}

.file-upload-input input[type="file"] {
  width: 100%;
  font-size: 0.82rem;
  cursor: pointer;
}

.submit-btn {
  width: 100%;
  height: 48px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(135deg, #0f2027 0%, #2c5364 100%);
  color: white;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-top: 0.35rem;
}

.submit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(44, 83, 100, 0.4);
}

.submit-btn:active {
  transform: translateY(0);
}

.auth-footer {
  text-align: center;
  padding: 0.85rem;
  font-size: 0.85rem;
  color: #666;
  border-top: 1px solid #f0f0f0;
  display: none;
  /* Hidden as link moved to top-right */
}

.auth-footer a {
  color: #667eea;
  text-decoration: none;
  font-weight: 600;
}

.auth-footer a:hover {
  text-decoration: underline;
}

.error-message {
  display: block;
  color: #dc3545;
  font-size: 0.72rem;
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

  .auth-header h2 {
    font-size: 1.5rem;
  }

  .auth-body {
    padding: 1rem;
  }

  .privacy-badge {
    margin: 1rem;
    font-size: 0.75rem;
  }
}
</style>

<section class="auth-section">
  <div class="top-right-link">
    Already have an account? <a href="signin.php">Sign in here</a>
  </div>

  <div class="auth-card">
    <div class="auth-header">
      <h2>Create Account</h2>
    </div>

    <div class="privacy-badge">
      <i class="fa-solid fa-shield-halved"></i>
      <div>
        <strong>Privacy Notice:</strong> This is a demo project. Do NOT use real email, photos, or personal data.
      </div>
    </div>

    <div class="auth-body">
      <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" enctype="multipart/form-data">
        <div class="form-floating-custom">
          <input type="text" name="userName" id="userName" placeholder=" " autocomplete="username" required>
          <label for="userName">Username*</label>
          <?php if($userNameErr): ?>
          <span class="error-message"><?php echo $userNameErr; ?></span>
          <?php endif; ?>
        </div>

        <div class="form-floating-custom">
          <input type="email" name="email" id="email" placeholder=" " autocomplete="email" required>
          <label for="email">Email Address*</label>
          <?php if($emailErr): ?>
          <span class="error-message"><?php echo $emailErr; ?></span>
          <?php endif; ?>
        </div>

        <div class="form-floating-custom">
          <input type="password" name="password" id="password" placeholder=" " autocomplete="new-password" required>
          <label for="password">Password*</label>
          <?php if($passwordErr): ?>
          <span class="error-message"><?php echo $passwordErr; ?></span>
          <?php endif; ?>
        </div>

        <div class="file-upload-wrapper">
          <label class="file-upload-label">Profile Picture*</label>
          <div class="file-upload-input">
            <input type="file" name="Upload" id="Upload" required accept="image/*">
          </div>
          <?php if(!empty($UploadErr) && is_array($UploadErr)): ?>
          <span class="error-message">
            <?php foreach ($UploadErr as $error) { echo htmlspecialchars($error) . '<br>'; } ?>
          </span>
          <?php endif; ?>
        </div>

        <button type="submit" class="submit-btn">Create Account</button>
      </form>
    </div>

    <div class="auth-footer">
      Already have an account? <a href="signin.php">Sign in here</a>
    </div>
  </div>
</section>
</body>

</html>