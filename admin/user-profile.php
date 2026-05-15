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

// ----- Target user lookup -----
$targetId = isset($_GET['q']) ? intval($_GET['q']) : 0;
if ($targetId <= 0) {
  header("Location: ./users-page.php?status=invalid_id");
  exit();
}

$stmt = $conn->prepare(
  "SELECT id, username, email, contact_number, user_img, time_stamp
   FROM user_sign_in WHERE id = ?"
);
$stmt->bind_param("i", $targetId);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$customer) {
  header("Location: ./users-page.php?status=not_found");
  exit();
}

// Addresses live in user_details (one row per customer in this schema)
$stmt = $conn->prepare(
  "SELECT delivery_address, billing_address FROM user_details WHERE email = ?"
);
$stmt->bind_param("s", $customer['email']);
$stmt->execute();
$details = $stmt->get_result()->fetch_assoc() ?: ['delivery_address' => '', 'billing_address' => ''];
$stmt->close();

// Order summary
$stmt = $conn->prepare(
  "SELECT
      o.id AS order_id,
      o.created_at,
      o.payment_type,
      o.order_status,
      COALESCE(SUM(oi.price * oi.quantity), 0) AS total_amount,
      COUNT(oi.id) AS item_count
   FROM orders o
   LEFT JOIN order_items oi ON oi.order_id = o.id AND oi.deleted_at IS NULL
   WHERE o.user_id = ?
   GROUP BY o.id
   ORDER BY o.created_at DESC"
);
$stmt->bind_param("i", $customer['id']);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$orderCount = count($orders);
$lifetimeSpend = array_sum(array_column($orders, 'total_amount'));

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
          <li class="nav-item"><a href="./users-page.php" class="nav-link text-white active"><i class="fas fa-users"></i> Users</a></li>
          <li class="nav-item"><a href="./add-product.php" class="nav-link text-white"><i class="fa-solid fa-cube"></i> Products</a></li>
          <li class="nav-item"><a href="./orders-listing.php" class="nav-link text-white"><i class="fa-solid fa-table-cells-large"></i> Order Listing</a></li>
          <li class="nav-item"><a href="./coupons.php" class="nav-link text-white"><i class="fa-solid fa-tags"></i> Coupons</a></li>
        </ul>
      </nav>

      <main class="col-12 col-md-9 ms-sm-auto col-lg-10 px-md-4 my-5 pt-5">
        <div class="py-5 my-5">
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="d-flex flex-column align-items-start justify-content-center gap-0">
              <h3 class="products mb-0 lh-1">User Profile</h3>
              <p class="breadcrumb mb-0">
                <a href="./users-page.php" class="text-decoration-none text-muted">Users</a>
                &nbsp;&gt;&nbsp;
                <?php echo h($customer['username']); ?>
              </p>
            </div>
            <a href="./users-page.php" class="btn btn-outline-secondary btn-sm">
              <i class="fa-solid fa-arrow-left me-1"></i> Back to Users
            </a>
          </div>

          <!-- Profile header card -->
          <div class="bg-white p-4 rounded shadow-sm mb-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
              <img src="<?php echo $Base_Url; ?>/assets/images/user-sign-up-uploads/<?php echo h($customer['user_img']); ?>"
                alt="<?php echo h($customer['username']); ?>"
                class="rounded-circle border"
                style="width:120px; height:120px; object-fit:cover;">
              <div class="flex-grow-1">
                <h4 class="mb-1"><?php echo h($customer['username']); ?></h4>
                <p class="text-muted mb-3"><i class="fa-solid fa-envelope me-2"></i><?php echo h($customer['email']); ?></p>
                <div class="row g-3">
                  <div class="col-sm-6 col-lg-3">
                    <div class="text-muted small">Contact</div>
                    <div class="fw-semibold"><?php echo h($customer['contact_number'] ?: '—'); ?></div>
                  </div>
                  <div class="col-sm-6 col-lg-3">
                    <div class="text-muted small">Member since</div>
                    <div class="fw-semibold">
                      <?php echo $customer['time_stamp'] ? h(date('d M Y', strtotime($customer['time_stamp']))) : '—'; ?>
                    </div>
                  </div>
                  <div class="col-sm-6 col-lg-3">
                    <div class="text-muted small">Total orders</div>
                    <div class="fw-semibold"><?php echo $orderCount; ?></div>
                  </div>
                  <div class="col-sm-6 col-lg-3">
                    <div class="text-muted small">Lifetime spend</div>
                    <div class="fw-semibold">$<?php echo number_format((float) $lifetimeSpend, 2); ?></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Addresses -->
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <div class="bg-white p-4 rounded shadow-sm h-100">
                <h6 class="text-uppercase text-muted small mb-2"><i class="fa-solid fa-truck me-2"></i>Delivery address</h6>
                <p class="mb-0"><?php echo $details['delivery_address'] ? nl2br(h($details['delivery_address'])) : '<span class="text-muted">No delivery address on file.</span>'; ?></p>
              </div>
            </div>
            <div class="col-md-6">
              <div class="bg-white p-4 rounded shadow-sm h-100">
                <h6 class="text-uppercase text-muted small mb-2"><i class="fa-solid fa-file-invoice-dollar me-2"></i>Billing address</h6>
                <p class="mb-0"><?php echo $details['billing_address'] ? nl2br(h($details['billing_address'])) : '<span class="text-muted">No billing address on file.</span>'; ?></p>
              </div>
            </div>
          </div>

          <!-- Order history -->
          <div class="bg-white p-4 rounded shadow-sm">
            <h5 class="mb-3">Order history</h5>
            <?php if ($orderCount === 0): ?>
              <p class="text-muted mb-0">This customer hasn't placed any orders yet.</p>
            <?php else: ?>
              <div class="table-responsive">
                <table class="table align-middle">
                  <thead>
                    <tr>
                      <th class="text-start">Order ID</th>
                      <th class="text-start">Date</th>
                      <th class="text-start">Items</th>
                      <th class="text-start">Payment</th>
                      <th class="text-start">Status</th>
                      <th class="text-end">Total</th>
                      <th class="text-end">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($orders as $o): ?>
                      <tr>
                        <td class="text-start">#<?php echo h($o['order_id']); ?></td>
                        <td class="text-start text-nowrap"><?php echo h(date('d M Y', strtotime($o['created_at']))); ?></td>
                        <td class="text-start"><?php echo (int) $o['item_count']; ?></td>
                        <td class="text-start text-capitalize"><?php echo h($o['payment_type']); ?></td>
                        <td class="text-start">
                          <?php
                            $status = strtolower($o['order_status'] ?? 'pending');
                            $badgeMap = [
                              'pending'    => 'bg-warning text-dark',
                              'processing' => 'bg-info text-dark',
                              'shipped'    => 'bg-primary',
                              'delivered'  => 'bg-success',
                              'completed'  => 'bg-success',
                              'cancelled'  => 'bg-danger',
                              'returned'   => 'bg-secondary',
                            ];
                            $badgeClass = $badgeMap[$status] ?? 'bg-secondary';
                          ?>
                          <span class="badge <?php echo $badgeClass; ?>"><?php echo h(ucfirst($status)); ?></span>
                        </td>
                        <td class="text-end fw-semibold">$<?php echo number_format((float) $o['total_amount'], 2); ?></td>
                        <td class="text-end">
                          <a href="./view-order.php?q=<?php echo (int) $o['order_id']; ?>"
                            class="btn btn-sm btn-outline-primary">View</a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </main>
    </div>
  </div>
</section>

<?php include '../main-footer.php' ?>
