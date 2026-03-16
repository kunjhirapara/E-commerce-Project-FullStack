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

$stmt = $conn->prepare("SELECT id, username, user_img FROM user_sign_in WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$stmt->bind_result($userId, $userName, $userImage);
$stmt->fetch();
$stmt->close(); 


$stmt2 = $conn->prepare("SELECT delivery_address, billing_address, username FROM user_details WHERE email = ?");
$stmt2->bind_param("s", $userEmail);
$stmt2->execute();
$stmt2->bind_result($deliveryAddress, $billing_address, $customerName );
$stmt2->fetch();
$stmt2->close();
$conn->close();
$orderId = base64_decode($_GET['orderId']);
$itemId = json_decode(base64_decode($_GET['itemId']), true);
?>


<script type="module" src="./assets/js/cartValue.js"></script>

<section class="dashbord">
  <div class="container py-5 my-5">
    <div class="row">
      <div class="col-12">
        <div id="returnOrderContainer">
          <div class="row row-gap-5">
            <div class="col-12">
              <div class="order-card shadow-sm bg-white rounded-3 border">
                <div class="row" id="content">
                  <h4 class="mb-4">Order <strong>#<span id="orderNo"></span></strong></h4>
                  <div class="row">
                    <div class="col-6">
                      <div id="returnProductContainer"></div>
                    </div>
                    <div class="col-6">
                      <div id="returnFormContainer">
                      </div>
                    </div>
                  </div>
                </div>
                <div class="d-flex justify-content-center">
                  <button id="downloadBtn" type="button" class="d-none d- add-to-cart-button fs-5">Download</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
let orderId = "<?php echo $orderId; ?>";
let itemId = <?php echo json_encode($itemId); ?>;
let userId = "<?php echo $userId; ?>"
let userEmail = "<?php echo $userEmail; ?>"
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js">
</script>
<script type="module" src="<?php echo $Base_Url; ?>/assets/js/returnOrder.js"></script>
<?php include './footer.php'; 
include './main-footer.php' ?>