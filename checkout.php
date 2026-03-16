<?php
require_once __DIR__ . '/includes/security.php';
Security::init_secure_session('USER_SESSION');
include './conn.php';
if (!isset($_SESSION['email'])) {
  header("Location: signin.php");
  exit();
}
$userEmail = $_SESSION['email'];
if (isset($_SESSION['last_signin_time']) && (time() - $_SESSION['last_signin_time']) > 10000) {
  header("Location:signout.php");
  exit();
}

include 'header.php';

$stmt = $conn->prepare("SELECT username, contact_number, delivery_address, billing_address, city, state_name, zip_code, payment_type
 FROM user_sign_in WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$stmt->bind_result($userName, $contactNumber, $deliveryAddress, $billingAddress, $city, $stateName, $zipCode, $paymentType);
$stmt->fetch();
$stmt->close();
?>


<div id="show-toast" class="toast-container position-fixed bottom-0 end-0" style="z-index: 1100;"></div>

<section class="addToCartElement">
  <div class="container">
    <section>
      <div class="row flex-column-reverse flex-lg-row">
        <div id="formError" style="display:none; color:red;"></div>

        <div class="col-lg-6 mb-5">
          <form method="post" id="form" class="row sticky-top">

            <div class="mb-4 col-md-6">
              <label class="ms-2" for="UserName">User Name*</label>
              <input type="text" name="UserName" class="form-control" id="UserName" placeholder="eg. Aman"
                value="<?php echo $userName; ?>" autocomplete="<?php echo $userName; ?>">
            </div>
            <div class="mb-4 col-md-6">
              <label class="ms-2" for="contactNumber">Contact Number*</label>
              <input type="tel" name="contactNumber" class="form-control" id="contactNumber"
                placeholder="eg. 9925463211" value="<?php echo $contactNumber; ?>">
            </div>
            <div class="mb-4">
              <label class="ms-2" for="deliveryAddress">Delivery Address*</label>
              <input type="text" name="deliveryAddress" class="form-control" id="deliveryAddress"
                placeholder="Delivery Address" value="<?php echo $deliveryAddress; ?>">
            </div>
            <div class="mb-4 form-check ms-3 col-6">
              <label class="form-check-label" for="sameAddress">Same as Above</label>
              <input type="checkbox" class="form-check-input" id="sameAddress">
            </div>

            <div class="mb-4">
              <label class="ms-2" for="billingAddress">Billing Address*</label>
              <input type="text" name="billingAddress" class="form-control" id="billingAddress"
                placeholder="Billing Address" value="<?php echo $billingAddress; ?>">
            </div>
            <div class="mb-4 col-md-6">
              <label class="ms-2" for="city">City*</label>
              <input type="text" name="city" class="form-control" id="city" placeholder="city"
                value="<?php echo $city; ?>">
            </div>
            <div class="col-md-6 mb-4">
              <label for="state" class="ms-2">State*</label>
              <select id="state" name="state" class="form-select">
                <option value="" <?= $stateName == "" ? 'selected' : "" ?>>Choose...</option>
                <option value="Gujarat" <?= $stateName == "Gujarat" ? 'selected' : "" ?>>Gujarat</option>
                <option value="Delhi" <?= $stateName == "Delhi" ? 'selected' : "" ?>>Delhi</option>
                <option value="Punjab" <?= $stateName == "Punjab" ? 'selected' : "" ?>>Punjab</option>
              </select>

            </div>
            <div class="mb-4 col-md-6">
              <label class="ms-2" for="zipCode">Zip Code*</label>
              <input type="text" name="zipCode" class="form-control" id="zipCode" placeholder="zipCode"
                value="<?php echo $zipCode; ?>">
            </div>
            <div class="col-md-6">
              <label for="paymentType" class="ms-2">Payment Type*</label>
              <select class="form-select" name="paymentType" id="paymentType">
                <option value="cod" <?= $paymentType == "cod" ? 'selected' : "" ?>>Cash On Delivery</option>
                <option value="paypal" <?= $paymentType == "paypal" ? 'selected' : "disabled" ?>>PayPal</option>
                <option value="cards" <?= $paymentType == "cards" ? 'selected' : "disabled" ?>>Debit card OR Credit card
                </option>
              </select>
            </div>
            <div class="mb-4 col-md-12" id="couponField">
              <div class="row">
                <div class="col-6">
                  <label class="ms-2" for="couponCode">Coupon Code</label>
                  <input type="text" name="couponCode" class="form-control" id="couponCode"
                    placeholder="Enter coupon code">
                </div>
                <div class="col-6">
                  <div class="mt-2">
                    <button type="button" id="applyBtn" class="add-to-cart-button">Apply</button>
                    <button type="button" id="removeCouponBtn" class="add-to-cart-button">Remove Coupon</button>

                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex align-items-center justify-content-center mt-3">
              <button id="submitUserDetails" type="submit" class="add-to-cart-button fs-5">Confirm Order</button>
            </div>
          </form>
        </div>
        <div class="col-lg-6 mb-5">
          <div id="productCartContainer"></div>
          <section class="cartTotalElement mt-5 justify-content-center">
            <div class="productCartTotalElement">
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
          </section>
        </div>
      </div>
    </section>

  </div>

</section>


<template id="productCartTemplate">
  <div class="add-to-cart-card" id="cart-card-value">
    <article class="information-card p-3">
      <div class="imageContainer">
        <img class="product-img" src="" alt="" />
      </div>
      <div class="d-flex flex-column">
        <h2><a href="#" class="product-name"></a></h2>
        <p class="brand">Brand: <span class="product-brand"></span></p>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <p class="mb-0 text-nowrap">Price: <strong class="product-price"></strong></p>

        <p class="d-flex mb-0"><span class="text-nowrap">Item Quantity: </span><strong
            class="product-quantity"></strong>
        </p>
      </div>
      <button class="add-to-cart-button remove-to-cart-button mt-0">
        Remove
      </button>
  </div>
  </article>
  </div>
</template>


<script type="module" src="<?php echo $Base_Url; ?>/assets/js/showAddToCart.js"></script>
<script defer type="module" src="<?php echo $Base_Url; ?>/assets/js/formValidation.js"></script>
<?php include 'footer.php';
include 'main-footer.php'; ?>