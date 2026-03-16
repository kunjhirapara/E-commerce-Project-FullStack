<?php
require_once __DIR__ . '/../includes/security.php';
Security::init_secure_session('ADMIN_SESSION');
include '../conn.php';

if (!isset($_SESSION['admin_email'])) {
  header("Location: signin.php");
  exit();
}
if (isset($_SESSION['admin_last_signin_time']) && (time() - $_SESSION['admin_last_signin_time']) > 10000) {
  header("Location:signout.php");
  exit();
}

$_SESSION['admin_last_signin_time'] = time();
$userEmail = $_SESSION['admin_email'];

$stmt = $conn->prepare("SELECT username, user_img FROM admin_sign_in WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$stmt->bind_result($userName, $userImage);
$stmt->fetch();
$stmt->close();

?>

<?php include 'main-header.php' ?>
<div id="show-toast" class="toast-container bg-white position-fixed bottom-0 end-0" style="z-index: 1100;"></div>

<div
  class="d-flex justify-content-between justify-content-md-end position-fixed top-0 right-0 p-3 bg-white w-100 shadow-sm"
  style="z-index: 10;">
  <button class="btn d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvas-sidebar"
    aria-controls="offcanvas-sidebar">
    <i class="fa-solid fa-bars"></i>
  </button>

  <div class="offcanvas offcanvas-start bg-black  d-md-none" tabindex="-1" id="offcanvas-sidebar"
    aria-labelledby="sidebar">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title text-white" id="sidebar">sideBar</h5>
      <button type="button" class="btn-close bg-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <div class="sidebar-header p-3">
        <img src="<?php echo $Base_Url; ?>/assets/images/icons/vite.svg" alt="Logo" class="img-fluid">
      </div>
      <ul class="nav flex-column">
        <li class="nav-item">
          <a href="./dashboard.php" class="nav-link text-white">
            <i class="fas fa-tachometer-alt"></i> Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a href="./users-page.php" class="nav-link text-white">
            <i class="fas fa-users"></i> Users
          </a>
        </li>
        <li class="nav-item">
          <a href="./add-product.php" class="nav-link text-white">
            <i class="fa-solid fa-cube"></i> Products
          </a>
        </li>
        <li class="nav-item">
          <a href="javascript:vod(0)" class="nav-link text-white">
            <i class="fa-solid fa-table-cells-large"></i> Order Listing
          </a>
        </li>
        <li class="nav-item">
          <a href="./coupons.php" class="nav-link text-white">
            <i class="fa-solid fa-tags"></i> Coupons
          </a>
        </li>
      </ul>
    </div>
  </div>
  <div class="dropdown" id="profileDropdown">
    <a href="#" class="d-flex align-items-center text-decoration-none" id="profileToggle">
      <img src="<?php echo $Admin_Base_Url; ?>/assets/images/admin-sign-up-uploads/<?php echo $userImage; ?>"
        alt="<?php echo $userName; ?>_image" width="40" height="40" class="rounded-circle">
    </a>
    <ul class="dropdown-menu shadow mt-2">
      <li class="px-3 py-2">
        <div class="d-flex align-items-center">
          <img src="<?php echo $Admin_Base_Url; ?>/assets/images/admin-sign-up-uploads/<?php echo $userImage; ?>"
            alt="<?php echo $userName; ?>_image" width="50" height="50" class="rounded-circle me-2">
          <div class="d-flex flex-column">
            <strong><?php echo $userName; ?></strong>
            <small class="text-muted"><?php echo $userEmail ?></small>
          </div>
        </div>
      </li>
      <li>
        <hr class="dropdown-divider">
      </li>
      <li><a class="dropdown-item" href="#">My Profile</a></li>
      <li>
        <hr class="dropdown-divider">
      </li>
      <li><a class="dropdown-item text-danger" href="./signout.php">Sign Out</a></li>
    </ul>
  </div>
</div>
<section class="dashboard">
  <div class="container-fluid">
    <div class="row">
      <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark text-white vh-100 collapse sidebar sticky-top">
        <div class="sidebar-header p-3">
          <img src="<?php echo $Base_Url; ?>/assets/images/icons/vite.svg" alt="Logo" class="img-fluid">
        </div>
        <ul class="nav flex-column">
          <li class="nav-item">
            <a href="./dashboard.php" class="nav-link text-white">
              <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a href="./users-page.php" class="nav-link text-white">
              <i class="fas fa-users"></i> Users
            </a>
          </li>
          <li class="nav-item">
            <a href="./add-product.php" class="nav-link text-white">
              <i class="fa-solid fa-cube"></i> Products
            </a>
          </li>
          <li class="nav-item">
            <a href="javascript:void(0)" class="nav-link text-white">
              <i class="fa-solid fa-table-cells-large"></i> Order Listing
            </a>
          </li>

          <li class="nav-item">
            <a href="./coupons.php" class="nav-link text-white">
              <i class="fa-solid fa-tags"></i> Coupons
            </a>
          </li>
        </ul>
      </nav>

      <main class="col-12 col-md-9 ms-sm-auto col-lg-10 px-md-4 my-5 pt-5">
        <div class="pt-4 pb-2">
          <h3>Order List</h3>
          <p class="text-muted">Home > eCommerce > Order List</p>
        </div>

        <div class="bg-white p-3 rounded shadow-sm">
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th scope="col">Order_id</th>
                  <th scope="col">Customer</th>
                  <th scope="col">Order Date</th>
                  <th scope="col">Payment Type</th>
                  <th scope="col">Status</th>
                  <th scope="col">Action</th>
                </tr>
              </thead>
              <tbody id="product-data">
              </tbody>
            </table>
          </div>
          <div id="paginationContainer" class="mt-3 flex-wrap"></div>
        </div>
      </main>
    </div>
  </div>
</section>
<script type="module" src="<?php echo $Admin_Base_Url; ?>/assets/js/adminFilterProductListing.js"></script>
<?php include 'main-footer.php' ?>