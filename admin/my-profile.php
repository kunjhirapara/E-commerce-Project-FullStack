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

// Pull the full admin row + role name
$stmt = $conn->prepare(
  "SELECT a.id, a.username, a.email, a.user_img, r.role AS role_name
   FROM admin_sign_in a
   LEFT JOIN roles r ON a.role_id = r.id
   WHERE a.email = ?"
);
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$admin) {
  header("Location: signout.php");
  exit();
}

$userName = $admin['username'];
$userImage = $admin['user_img'];

// Platform-wide stats (simple SELECT COUNTs, scoped to non-deleted data)
$stats = ['users' => 0, 'products' => 0, 'orders' => 0, 'revenue' => 0.0];

if ($res = $conn->query("SELECT COUNT(*) AS c FROM user_sign_in")) {
  $stats['users'] = (int) $res->fetch_assoc()['c'];
}
if ($res = $conn->query("SELECT COUNT(*) AS c FROM total_products WHERE is_deleted IS NULL")) {
  $stats['products'] = (int) $res->fetch_assoc()['c'];
}
if ($res = $conn->query("SELECT COUNT(*) AS c FROM orders")) {
  $stats['orders'] = (int) $res->fetch_assoc()['c'];
}
if ($res = $conn->query("SELECT COALESCE(SUM(oi.price * oi.quantity), 0) AS r
                        FROM order_items oi
                        WHERE oi.deleted_at IS NULL")) {
  $stats['revenue'] = (float) $res->fetch_assoc()['r'];
}

$conn->close();

function h($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
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

  <div class="offcanvas offcanvas-start bg-black d-md-none" tabindex="-1" id="offcanvas-sidebar"
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
        <li class="nav-item"><a href="./dashboard.php" class="nav-link text-white"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li class="nav-item"><a href="./users-page.php" class="nav-link text-white"><i class="fas fa-users"></i> Users</a></li>
        <li class="nav-item"><a href="./add-product.php" class="nav-link text-white"><i class="fa-solid fa-cube"></i> Products</a></li>
        <li class="nav-item"><a href="./orders-listing.php" class="nav-link text-white"><i class="fa-solid fa-table-cells-large"></i> Order Listing</a></li>
        <li class="nav-item"><a href="./coupons.php" class="nav-link text-white"><i class="fa-solid fa-tags"></i> Coupons</a></li>
      </ul>
    </div>
  </div>

  <div class="dropdown" id="profileDropdown">
    <a href="#" class="d-flex align-items-center text-decoration-none" id="profileToggle">
      <img src="<?php echo $Admin_Base_Url; ?>/assets/images/admin-sign-up-uploads/<?php echo h($userImage); ?>"
        alt="<?php echo h($userName); ?>_image" width="40" height="40" class="rounded-circle">
    </a>
    <ul class="dropdown-menu shadow mt-2">
      <li class="px-3 py-2">
        <div class="d-flex align-items-center">
          <img src="<?php echo $Admin_Base_Url; ?>/assets/images/admin-sign-up-uploads/<?php echo h($userImage); ?>"
            alt="<?php echo h($userName); ?>_image" width="50" height="50" class="rounded-circle me-2">
          <div class="d-flex flex-column">
            <strong><?php echo h($userName); ?></strong>
            <small class="text-muted"><?php echo h($userEmail); ?></small>
          </div>
        </div>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item" href="./my-profile.php">My Profile</a></li>
      <li><hr class="dropdown-divider"></li>
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
          <li class="nav-item"><a href="./dashboard.php" class="nav-link text-white"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
          <li class="nav-item"><a href="./users-page.php" class="nav-link text-white"><i class="fas fa-users"></i> Users</a></li>
          <li class="nav-item"><a href="./add-product.php" class="nav-link text-white"><i class="fa-solid fa-cube"></i> Products</a></li>
          <li class="nav-item"><a href="./orders-listing.php" class="nav-link text-white"><i class="fa-solid fa-table-cells-large"></i> Order Listing</a></li>
          <li class="nav-item"><a href="./coupons.php" class="nav-link text-white"><i class="fa-solid fa-tags"></i> Coupons</a></li>
        </ul>
      </nav>

      <main class="col-12 col-md-9 ms-sm-auto col-lg-10 px-md-4 my-5 pt-5">
        <div class="py-5 my-5">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex flex-column align-items-start justify-content-center gap-0">
              <h3 class="products mb-0 lh-1">My Profile</h3>
              <p class="breadcrumb mb-0">Home &nbsp;&gt;&nbsp; Account</p>
            </div>
          </div>

          <!-- Identity card -->
          <div class="bg-white p-4 rounded shadow-sm mb-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
              <img src="<?php echo $Admin_Base_Url; ?>/assets/images/admin-sign-up-uploads/<?php echo h($userImage); ?>"
                alt="<?php echo h($userName); ?>"
                class="rounded-circle border"
                style="width:120px; height:120px; object-fit:cover;">
              <div class="flex-grow-1">
                <h4 class="mb-1"><?php echo h($userName); ?></h4>
                <p class="text-muted mb-3">
                  <i class="fa-solid fa-envelope me-2"></i><?php echo h($userEmail); ?>
                </p>
                <div class="row g-3">
                  <div class="col-sm-6 col-lg-4">
                    <div class="text-muted small">Role</div>
                    <div>
                      <span class="badge bg-primary text-uppercase"><?php echo h($admin['role_name'] ?: 'admin'); ?></span>
                    </div>
                  </div>
                  <div class="col-sm-6 col-lg-4">
                    <div class="text-muted small">Admin ID</div>
                    <div class="fw-semibold">#<?php echo (int) $admin['id']; ?></div>
                  </div>
                  <div class="col-sm-6 col-lg-4">
                    <div class="text-muted small">Status</div>
                    <div><span class="badge bg-success">Active</span></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Platform stats -->
          <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
              <a href="./users-page.php" class="text-decoration-none">
                <div class="bg-white p-4 rounded shadow-sm h-100">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <div class="text-muted small text-uppercase">Customers</div>
                      <div class="h3 mb-0 fw-bold"><?php echo number_format($stats['users']); ?></div>
                    </div>
                    <i class="fa-solid fa-users fa-2x text-primary opacity-50"></i>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-sm-6 col-lg-3">
              <a href="./add-product.php" class="text-decoration-none">
                <div class="bg-white p-4 rounded shadow-sm h-100">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <div class="text-muted small text-uppercase">Products</div>
                      <div class="h3 mb-0 fw-bold"><?php echo number_format($stats['products']); ?></div>
                    </div>
                    <i class="fa-solid fa-cube fa-2x text-success opacity-50"></i>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-sm-6 col-lg-3">
              <a href="./orders-listing.php" class="text-decoration-none">
                <div class="bg-white p-4 rounded shadow-sm h-100">
                  <div class="d-flex align-items-center justify-content-between">
                    <div>
                      <div class="text-muted small text-uppercase">Orders</div>
                      <div class="h3 mb-0 fw-bold"><?php echo number_format($stats['orders']); ?></div>
                    </div>
                    <i class="fa-solid fa-table-cells-large fa-2x text-info opacity-50"></i>
                  </div>
                </div>
              </a>
            </div>
            <div class="col-sm-6 col-lg-3">
              <div class="bg-white p-4 rounded shadow-sm h-100">
                <div class="d-flex align-items-center justify-content-between">
                  <div>
                    <div class="text-muted small text-uppercase">Revenue</div>
                    <div class="h3 mb-0 fw-bold">$<?php echo number_format($stats['revenue'], 2); ?></div>
                  </div>
                  <i class="fa-solid fa-sack-dollar fa-2x text-warning opacity-50"></i>
                </div>
              </div>
            </div>
          </div>

          <!-- Account actions -->
          <div class="bg-white p-4 rounded shadow-sm">
            <h5 class="mb-3">Account</h5>
            <p class="text-muted small mb-3">
              Manage your admin credentials. Forgot your password? Use the reset flow on the sign-in page.
            </p>
            <div class="d-flex flex-wrap gap-2">
              <a href="./forgot-password.php" class="btn btn-outline-primary">
                <i class="fa-solid fa-key me-2"></i> Reset password
              </a>
              <a href="./signout.php" class="btn btn-outline-danger">
                <i class="fa-solid fa-right-from-bracket me-2"></i> Sign out
              </a>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</section>

<?php include '../main-footer.php' ?>
