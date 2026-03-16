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
include './header.php';
$userEmail = $_SESSION['email'];

$stmt = $conn->prepare("SELECT username, user_img FROM user_sign_in WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$stmt->bind_result($userName, $userImage);
$stmt->fetch();
$stmt->close(); 


$stmt2 = $conn->prepare("SELECT delivery_address, billing_address, username FROM user_details WHERE email = ?");
$stmt2->bind_param("s", $userEmail);
$stmt2->execute();
$stmt2->bind_result($deliveryAddress, $billing_address, $customerName );
$stmt2->fetch();
$stmt2->close();
$conn->close();
?>
<script type="module" src="./assets/js/cartValue.js"></script>

<section class="dashbord">
  <div class="container py-5 my-5">
    <div class="row">
      <div class="col-12">
        <div id="returnOrderDetails"></div>
      </div>
    </div>
  </div>
</section>

<script type="module" src="<?php echo $Base_Url; ?>/assets/js/returnMyOrderDetails.js"></script>
<?php include './footer.php'; 
include './main-footer.php' ?>