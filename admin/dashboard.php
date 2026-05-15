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
          <a href="javascript:void(0)" class="nav-link text-white">
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
          <a href="./orders-listing.php" class="nav-link text-white">
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
      <li><a class="dropdown-item" href="./my-profile.php">My Profile</a></li>
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
            <a href="javascript:void(0)" class="nav-link text-white">
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
            <a href="./orders-listing.php" class="nav-link text-white">
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
      <div class="col-md-9 col-lg-10 px-3 px-md-5 my-5">
        <div class="row mt-5 pt-5 g-4">
          <div class="col-12">
            <h2 class="h4 mb-4">Dashboard Overview</h2>
          </div>

          <div class="col-12 col-sm-6 col-lg-3">
            <div class="bg-primary text-white rounded-3 shadow-sm p-3 h-100">
              <h3 class="h6 mb-2">Total Sales</h3>
              <div class="d-flex align-items-center">
                <span class="h3 mb-0" id="totalSales">0</span>
              </div>
              <p class="small mb-0 mt-2">Lifetime earnings</p>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-lg-3">
            <div class="bg-success text-white rounded-3 shadow-sm p-3 h-100">
              <h3 class="h6 mb-2">Weekly Sales</h3>
              <div class="d-flex align-items-center">
                <i class="fas fa-chart-line fs-4 me-2"></i>
                <span class="h3 mb-0" id="salesThisWeek">0</span>
              </div>
              <p class="small mb-0 mt-2">Last 7 days</p>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-lg-3">
            <div class="bg-info text-white rounded-3 shadow-sm p-3 h-100">
              <h3 class="h6 mb-2">Pending Orders</h3>
              <div class="d-flex align-items-center">
                <i class="fas fa-clock fs-4 me-2"></i>
                <span class="h3 mb-0" id="pendingOrders">0</span>
              </div>
              <p class="small mb-0 mt-2">Awaiting processing</p>
            </div>
          </div>

          <div class="col-12 col-sm-6 col-lg-3">
            <div class="bg-warning text-white rounded-3 shadow-sm p-3 h-100">
              <h3 class="h6 mb-2">Total Products</h3>
              <div class="d-flex align-items-center">
                <i class="fas fa-box fs-4 me-2"></i>
                <span class="h3 mb-0" id="totalProducts">0</span>
              </div>
              <p class="small mb-0 mt-2">Active listings</p>
            </div>
          </div>

          <div class="col-12 col-lg-8">
            <div class="bg-white rounded-3 shadow-sm p-3">
              <h3 class="h5 mb-3">Sales Overview</h3>
              <canvas id="salesChart" style="width: 100%; height: 400px;"></canvas>
            </div>
          </div>

          <div class="col-12 col-lg-4">
            <div class="bg-white rounded-3 shadow-sm p-3 h-100">
              <h3>Recent Orders</h3>
              <hr class="mb-4">
              <div id="recentOrders">
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script src="./assets/js/chart.umd.min.js" type="module" defer></script>
<script src="./assets/js/adminDashboard.js" type="module" defer></script>
<?php include '../main-footer.php' ?>