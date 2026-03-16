<?php
require_once __DIR__ . '/includes/security.php';
Security::init_secure_session('USER_SESSION');
include './conn.php'; 

if (!isset($_SESSION['email'])) {
    header("Location: signin.php");
    exit();
}
if (isset($_SESSION['last_signin_time']) && (time() - $_SESSION['last_signin_time']) > 10000) {
    header("Location:signout.php");
    exit();
}

$_SESSION['last_signin_time'] = time();
include 'header.php';
$userEmail = $_SESSION['email'];

$stmt = $conn->prepare("SELECT username, user_img FROM user_sign_in WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$stmt->bind_result($userName, $userImage);
$stmt->fetch();
$stmt->close();
?>
<script type="module" src="./assets/js/cartValue.js"></script>

<section class="page-header">
  <h1 class="page-header-title">My orders</h1>
</section>
<section class="dashbord">
  <div class="container py-5 my-5">
    <div class="row">
      <div class="col-md-3">
        <ul class="dashboard-link-list dashboard-item-border">
          <li><a href="./my-dashboard.php">Dashboard</a></li>
          <li><a href="#" class="active">Orders</a></li>
          <li><a href="./my-address.php">Addresses</a></li>
          <li><a href="./my-account-details.php">Account Details</a></li>
          <li><a href="./signout.php">Signout</a></li>
        </ul>
      </div>
      <div class="col-md-9">
        <div class="dashboard-item-border p-4 user-detail-box" style="height: 550px; overflow: auto;">
          <div id="show-toast" class="toast-container bg-white position-fixed bottom-0 end-0"
            style="z-index: 1100;"></div>

          <div class="d-flex align-items-center justify-content-between mb-5">
            <div class="d-flex flex-column align-items-start justify-content-center gap-0">
              <h3 class="products mb-0 lh-1">Order List</h3>
            </div>
          </div>

          <div class="product-table py-3 px-4 bg-white">
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
            <div id="paginationContainer" class="mt-3">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
let newEmail = `<?php echo $userEmail?>`;
</script>
<script src="<?php echo $Base_Url; ?>/assets/js/filterProductListing.js"></script>
<?php include 'footer.php';
include 'main-footer.php'; ?>