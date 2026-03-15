<?php
require_once __DIR__ . '/includes/security.php';
Security::init_secure_session('USER_SESSION');
include 'main-header.php';

$userName = 'User';
$userImage = '';
$userEmail = '';
?>
<script>
const IS_LOGGED_IN = <?= isset($_SESSION['email']) ? 'true' : 'false' ?>;
</script>
<?php
if (isset($_SESSION['email'])) {
  $userEmail = $_SESSION['email'];
}
if (isset($_SESSION ['last_signin_time']) && (time() - $_SESSION['last_signin_time']) > 1800) {
  header("Location:signout.php");
  exit();
}
$_SESSION['last_signin_time'] = time();

if (!empty($userEmail)) {
  try {
    $stmt = $conn->prepare("SELECT username, user_img FROM user_sign_in WHERE email = ?");
    if ($stmt) {
      $stmt->bind_param("s", $userEmail);
      $stmt->execute();
      $stmt->bind_result($dbUserName, $dbUserImage);
      if ($stmt->fetch()) {
        if (!empty($dbUserName)) {
          $userName = $dbUserName;
        }
        if (!empty($dbUserImage)) {
          $userImage = $dbUserImage;
        }
      }
      $stmt->close();
    }
  } catch (Throwable $e) {
    // Keep page functional during initial deployment before DB import/migration.
  }
}

$profileImageUrl = !empty($userImage)
  ? $Base_Url . '/assets/images/user-sign-up-uploads/' . $userImage
  : $Base_Url . '/assets/images/icons/vite.svg';
?>
<header class="section-navbar">
  <section class="top_txt">
    <div class="head container ">
      <div class="sing_in_up d-flex justify-content-between align-items-center w-100">
        <?php
        if (!isset($_SESSION["email"])) {
          echo '
          <div class="d-flex justify-content-end w-100 gap-3">
            <a href="./signin.php">SIGN IN</a>
            <a href="./signup.php">SIGN UP</a>
          </div>';
        } else {
          echo '
          <div class="d-flex gap-2">
        <p class="mb-0">
          Also check out the admin panel
        </p>
        <a href="'.$Base_Url .'/admin">Click Here</a>
      </div>
      <div class="d-flex gap-2 align-items-center">
        <p class="mb-0">My Profile</p>
        <div class="d-flex justify-content-end align-items-center z-1">
          <div class="dropdown" id="profileDropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none" id="profileToggle">
              <img src="' . $profileImageUrl . '" alt="avatar" width="40"
                height="40" class="rounded-circle">
            </a>
            <ul class="dropdown-menu shadow mt-2">
              <li class="px-3 py-2">
                <div class="d-flex align-items-center">
                  <img src="' . $profileImageUrl . '" alt="avatar"
                    width="50" height="50" class="rounded-circle me-2">
                  <div class="d-flex flex-column">
                    <strong>' . $userName . '</strong>
                    <span class="text-muted">' . $userEmail . '</span>
                  </div>
                </div>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <a class="dropdown-link" href="./my-dashboard.php">Dashboard</a>
              </li>
              <li>
                <hr class="dropdown-divider">
              </li>
              <li>
                <a class="dropdown-link text-danger" href="./signout.php">Sign Out</a>
              </li>
            </ul>
          </div>
        </div>
      </div>';
      }
      ?>
        <!-- <a href="./signout.php">LOG OUT</a> -->

      </div>
    </div>
  </section>
  <div class="container ">
    <div class="navbar-brand">
      <a href="index.php">
        <img src="<?php echo $Base_Url; ?>/assets/images/icons/vite.svg" alt="eCommerce logo" class="logo" />
      </a>
    </div>

    <nav class="navbar d-none d-md-block nav-desktop">
      <ul>
        <li class="nav-item">
          <a href="index.php" class="nav-link">Home</a>
        </li>
        <li class="nav-item">
          <a href="about.php" class="nav-link">about</a>
        </li>
        <li class="nav-item">
          <a href="product.php" class="nav-link">products</a>
        </li>
        <li class="nav-item">
          <a href="contact.php" class="nav-link">contact</a>
        </li>
        <li class="nav-item">
          <a href="add-to-wishlist.php" class="nav-link">wishlist</a>
        </li>
        <li class="nav-item">
          <a href="add-to-cart.php" class="nav-link add-to-cart-button mt-0" id="cart-value">
            <i class="fa-solid fa-cart-shopping">
            </i>
            <span class="cart-item-quantity">0</span>
          </a>
        </li>
      </ul>
    </nav>
    <nav class="navbar bg-body-tertiary d-md-none nav-mobile">
      <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar"
          aria-controls="offcanvasNavbar" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNavbar" aria-labelledby="offcanvasNavbarLabel">
          <div class="offcanvas-header">
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
          </div>
          <div class="offcanvas-body">
            <ul class="navbar-nav justify-content-start flex-grow-1 pe-3 align-items-start">
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="index.php">Home</a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="about.php">About</a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="product.php">Products</a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="contact.php">Contact</a>
              </li>
              <li class="nav-item">
                <a class="nav-link active" aria-current="page" href="add-to-wishlist.php">wishlist</a>
              </li>
              <li class="nav-item">
                <a href="add-to-cart.php" class="nav-link add-to-cart-button">
                  <i class="fa-solid fa-cart-shopping"> 0 </i>
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </nav>
  </div>
</header>