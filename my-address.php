<?php
require_once __DIR__ . '/includes/security.php';
Security::init_secure_session('USER_SESSION');
include './conn.php';
if (!isset($_SESSION['email'])) {
  header("Location: signin.php");
  exit();
}
include 'header.php';
$userEmail = $_SESSION['email'];

$userName = '';
$deliveryAddress = '';
$billingAddress = '';
$city = '';
$stateName = '';
$zipCode = '';

$stmt = $conn->prepare("SELECT username, delivery_address, billing_address, city, state_name, zip_code
 FROM user_sign_in WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$stmt->bind_result($userName, $deliveryAddress, $billingAddress, $city, $stateName, $zipCode);
$stmt->fetch();
$stmt->close();

$deliveryAddress = (string)($deliveryAddress ?? '');
$billingAddress = (string)($billingAddress ?? '');
$city = (string)($city ?? '');
$stateName = (string)($stateName ?? '');
$zipCode = (string)($zipCode ?? '');
?>
<script type="module" src="./assets/js/cartValue.js"></script>

<section class="page-header">
  <h1 class="page-header-title">My address</h1>
</section>
<section class="dashbord">
  <div class="container py-5 my-5">
    <div class="row">
      <div class="col-md-3">
        <ul class="dashboard-link-list dashboard-item-border">
          <li><a href="./my-dashboard.php">Dashboard</a></li>
          <li><a href="./my-orders.php">Orders</a></li>
          <li><a href="#" class="active">Addresses</a></li>
          <li><a href="./my-account-details.php">Account Details</a></li>
          <li><a href="./signout.php">Signout</a></li>
        </ul>
      </div>
      <div class="col-md-9">
        <div class="dashboard-item-border py-4 px-3 px-lg-5 user-detail-box ">
          <?php
          $showForm = ($billingAddress === "" || $deliveryAddress === "" || $city === "" || $stateName === "" || $zipCode === "" || strlen($zipCode) !== 6);
          ?>
          <div id="addressDisplay" style="<?= $showForm ? 'display:none;' : '' ?>">
            <div class="row">
              <div class="col-md-6">
                <div class="d-flex align-items-center justify-content-center">
                  <div>
                    <h4>Delivery Address: </h4>
                    <p><span
                        class=""><?php echo 'Address: ' . $deliveryAddress . ',<br>City: ' . $city . ',<br>State: ' . $stateName . ',<br>Zip code: ' . $zipCode ?></span>
                    </p>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="d-flex justify-content-center align-items-center">
                  <div>
                    <h4>Billing Address: </h4>
                    <p><span
                        class=""><?php echo 'Address: ' . $billingAddress . ',<br>City: ' . $city . ',<br>State: ' . $stateName . ',<br>Zip code: ' . $zipCode ?></span>
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-12 text-center mt-5">
              <a href="#" id="editAddressBtn" class="add-to-cart-button">Edit Address</a>
            </div>
          </div>

          <form action="" method="post" id="addressForm" class="row" style="<?= $showForm ? '' : 'display:none;' ?>">
            <div class="mb-4">
              <label class="ms-2" for="deliveryAddress">Delivery Address*</label>
              <input type="text" name="deliveryAddress" class="form-control" id="deliveryAddress"
                placeholder="Delivery Address" value="<?= htmlspecialchars($deliveryAddress) ?>">
            </div>
            <div class="mb-4">
              <label class="ms-2" for="billingAddress">Billing Address*</label>
              <input type="text" name="billingAddress" class="form-control" id="billingAddress"
                placeholder="Billing Address" value="<?= htmlspecialchars($billingAddress) ?>">
            </div>
            <div class="mb-4 col-md-6">
              <label class="ms-2" for="city">City*</label>
              <input type="text" name="city" class="form-control" id="city" placeholder="city"
                value="<?= htmlspecialchars($city) ?>">
            </div>
            <div class="col-md-4 mb-4">
              <label for="state" class="ms-2">State*</label>
              <select id="state" name="state" class="form-select">
                <option value="">Choose...</option>
                <option value="Gujarat" <?= $stateName == "Gujarat" ? 'selected' : '' ?>>Gujarat</option>
                <option value="Delhi" <?= $stateName == "Delhi" ? 'selected' : '' ?>>Delhi</option>
                <option value="Punjab" <?= $stateName == "Punjab" ? 'selected' : '' ?>>Punjab</option>
              </select>
            </div>
            <div class="mb-4 col-md-2">
              <label class="ms-2" for="zipCode">Zip Code*</label>
              <input type="text" name="zipCode" class="form-control" id="zipCode" placeholder="zipCode"
                value="<?= htmlspecialchars($zipCode) ?>">
            </div>
            <div class="d-flex justify-content-center align-items-center gap-3">
              <div class="d-flex align-items-center justify-content-center mt-3">
                <button id="submitUserAddress" type="submit" class="add-to-cart-button fs-5">Save Address</button>
              </div>
              <div class="d-flex align-items-center justify-content-center mt-3">
                <a href="#" id="cancleAddressBtn" class="add-to-cart-button fs-5">Cancle</a>
              </div>
            </div>
          </form>

        </div>
      </div>
    </div>
  </div>
</section>
<script type="module">
  $(document).ready(function() {
    $.validator.addMethod(
      "address",
      function(value, element) {
        return (
          this.optional(element) ||
          /^[a-zA-Z0-9\s.,'#\-\/()]+$/.test(value.trim())
        );
      },
      "Only alphabets, digits and some special characters are allowed."
    );

    $.validator.addMethod(
      "notDefault",
      function(value) {
        return value !== "Select one" && value !== "";
      },
      "Please select a valid option."
    );
    $("#submitUserAddress").click(function() {
      $("#addressForm").submit();
    });

    $("#addressForm").validate({
      rules: {
        deliveryAddress: {
          required: true,
          address: true,
        },
        billingAddress: {
          required: true,
          address: true,
        },
        city: {
          required: true,
          address: true,
        },
        state: {
          required: true,
          notDefault: true,
        },
        zipCode: {
          required: true,
          digits: true,
          maxlength: 6,
          minlength: 6,
        },
      },
      messages: {
        deliveryAddress: {
          required: "Delivery Address is required.",
          address: "Address must be alphabets, digits and some allowed special characters",
        },
        billingAddress: {
          required: "Billing Address is required.",
          address: "Address must be alphabets, digits and some allowed special characters",
        },
        city: {
          required: "City is required.",
          address: "City name must be alphabets, digits and some allowed special characters",
        },
        state: {
          required: "Please select an option",
          notDefault: "Please select a valid option.",
        },
        zipCode: {
          required: "Zip code is required",
          maxlength: "Zip code length must be 6",
          minlength: "Zip code length must be 6",
        },
      },
      submitHandler: function(event) {
        $("#submitUserAddress").prop("disabled", true);
        $.ajax({
          type: "POST",
          url: "./assets/api/update_address.php",
          data: $("#addressForm").serialize(),
          dataType: "json",
          success: function(response) {
            if (response.success) window.location.href = "./my-address.php";

            if (response.error) {
              $("#submitUserAddress").prop("disabled", false);
              return;
            }
            if (response.orderId) {
              document.getElementById('addressForm').reset();
              window.location.href = 'my-address.php'
            }

            $("#submitUserAddress").prop("disabled", false);
          },
          error: function(jqXHR) {
            console.log(jqXHR);
            let errorMsg = "An error occurred, please try again.";
            if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
              errorMsg = jqXHR.responseJSON.error;
            }

            $("#submitUserAddress").prop("disabled", false);
          },
        });
      },
    });
    $("#editAddressBtn").click(function(e) {
      e.preventDefault();
      $("#addressDisplay").hide();
      $("#addressForm").show();
    });
    $("#cancleAddressBtn").click(function(e) {
      e.preventDefault();
      $("#addressDisplay").show();
      $("#addressForm").hide();
    });

  });
</script>
<?php include 'main-footer.php';
include 'footer.php'; ?>