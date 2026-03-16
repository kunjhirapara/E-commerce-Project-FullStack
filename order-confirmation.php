<?php
require_once __DIR__ . '/includes/security.php';
Security::init_secure_session('USER_SESSION');
include './conn.php';
unset($_SESSION['applied_coupon']);
if (!isset($_SESSION['orderId']) && !isset($_SESSION['email'])) {
  header("Location: index.php");
  exit();
}
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], "checkout.php") === false) {
  header("Location: index.php");
  exit;
}
include "header.php";


$orderId = $_SESSION['orderId'];

$deliveryAddress = '';
$customerName = '';
$paymentType = '';
$orderTime = '';

$stmt = $conn->prepare("SELECT delivery_address, username, payment_type, created_at FROM orders WHERE id = ?");
$stmt->bind_param("s", $orderId);
$stmt->execute();
$stmt->bind_result($deliveryAddress, $customerName, $paymentType, $orderTime);
$stmt->fetch();
$stmt->close();
$conn->close();
$orderTime = (string)($orderTime ?? '');
$dateOnly = $orderTime !== '' ? date("d-m-Y", strtotime($orderTime)) : '';
$timeOnly = $orderTime !== '' ? date("H:i:s", strtotime($orderTime)) : '';
?>

<div class="container mt-5 pt-5">
  <div class="row">
    <div class="col-12 col-md-12 bg-white p-5">
      <div class="d-flex justify-content-between my-5 border-bottom flex-column flex-md-row row-gap-5">
        <h2 class="section-title-h2 fs-1">ORDER DETAILS</h2>
        <div class="">
          <h4 class="section-title-h3 text-uppercase text-start text-md-end">EcomStore</h4>
          <p class="text-start text-md-end fs-6 text-secondary fw-medium">Cecilia Chapman, 711-2880 Nulla St, Mankato
            <br>Mississippi
            96522
          </p>
        </div>
      </div>
      <div class="row mb_60 ">
        <div class="col-md-7">
          <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex flex-column">
              <h5 class="section-title-h5">ORDER DATE</h5>
              <p class="text-secondary"><?php echo $dateOnly ?></p>
            </div>
            <div class="d-flex flex-column">
              <h5 class="section-title-h5">ORDER NO.</h5>
              <p class="text-secondary" id="orderNo">0</p>
            </div>
          </div>
        </div>
        <div class="col-md-5">
          <h4 class="section-title-h3 text-uppercase text-start text-md-end">DELEVERED TO.</h4>
          <p class="fs-6 text-secondary fw-medium text-start text-md-end" id="deliveryAddress">
            <?php
            echo "To: " . $customerName . ",";
            echo "<br>";
            echo "At, " . $deliveryAddress;
            ?>
          </p>
        </div>
      </div>
      <div id="checkoutContainer">
        <div class="product-table">
          <div class="table-responsive w-100">
            <table class="table" id="cart-table">
              <thead class="border-bottom">
                <tr>
                  <th>Product</th>
                  <th>Color</th>
                  <th>QTY</th>
                  <th>Unit Price</th>
                  <th>Total Price</th>
                </tr>
              </thead>
              <tbody>
                <!-- order data -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="col-12 col-xl-9  bg-white py-5">
      <div class="p-3 rounded-3 bg-gray table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th class="text-secondary fs-6 bg-gray">PAYMENT TYPE</th>
              <th class="text-secondary fs-6 bg-gray">PAYMENT STATUS</th>
              <th class="text-secondary fs-6 bg-gray">PAYMENT DATE</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td class="bg-gray"><strong><?php echo $paymentType ?></strong></td>
              <td class="bg-gray"><strong>Pending</strong></td>
              <td class="bg-gray"><strong><?php echo $dateOnly ?></strong></td>
            </tr>
          </tbody>
        </table>
      </div>

    </div>
    <div class="col-12 col-xl-3 bg-white py-5">
      <section class="cartTotalElement justify-content-end">
        <div class="productCartTotalElement shadow-none bg-gray rounded-2">
          <div class="productOrderTotal">
            <p class="">Billing Details</p>
            <div class="productOrderTotal">
              <div id="cartTotalContainer" class="flex-column">
                <div class="d-flex justify-content-between" id="Subtotal">
                  <p>Sub Total:</p>
                  <p class="product-subtotal">$0</p>
                </div>
                <div class="d-flex justify-content-between">
                  <p>GST(18%):</p>
                  <p class="product-tax">$0</p>
                </div>
                <div class="d-flex justify-content-between">
                  <p>Shipping Cost:</p>
                  <p class="product-shipping">+ $5</p>
                </div>
              </div>
              <hr />
              <div class="d-flex justify-content-between">
                <p>Final Total:</p>
                <p class="product-total" id="finalTotal">$0</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</div>

<script type="module" src="assets/js/showConfirmation.js"></script>

<?php include 'footer.php';
include 'main-footer.php';
// unset($_SESSION['orderId']);
?>