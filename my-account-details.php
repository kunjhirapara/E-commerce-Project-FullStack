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

$stmt = $conn->prepare("SELECT username, contact_number, email , user_img, payment_type FROM user_sign_in WHERE email = ?");
$stmt->bind_param("s", $userEmail);
$stmt->execute();
$stmt->bind_result($userName, $contactNumber, $email, $userImage, $paymentType);
$stmt->fetch();
$stmt->close();
?>
<script type="module" src="./assets/js/cartValue.js"></script>

<section class="page-header">
  <h1 class="page-header-title">My Account</h1>
</section>
<section class="dashbord">
  <div class="container py-5 my-5">
    <div class="row">
      <div class="col-md-3">
        <ul class="dashboard-link-list dashboard-item-border">
          <li><a href="./my-dashboard.php">Dashboard</a></li>
          <li><a href="./my-orders.php">Orders</a></li>
          <li><a href="./my-address.php">Addresses</a></li>
          <li><a href="#" class="active">Account Details</a></li>
          <li><a href="./signout.php">Signout</a></li>
        </ul>
      </div>
      <div class="col-md-9">
        <div class="dashboard-item-border py-4 px-3 px-lg-5 user-detail-box ">
          <div class="row">
            <div class="col-12">
              <div class="delivery-address">
                <?php
                echo '

              <form method="post" id="userDetailsForm" class="row">
                <div class="mb-4 col-md-6">
                  <label class="ms-2" for="UserName">User Name*</label>
                  <input type="text" name="UserName" class="form-control" id="UserName" placeholder="eg. Aman"
                    value="' . $userName . '">
              </div>
              <div class="mb-4 col-md-6">
                <label class="ms-2" for="contactNumber">Contact Number*</label>
                <input type="tel" name="contactNumber" class="form-control" id="contactNumber"
                  placeholder="eg. 9925463211" value="' . $contactNumber . '">
              </div>
              <div class="col-md-6">
                <label class="ms-2" for="email">Email*</label>
                <input type="email" name="email" class="form-control" id="email"
                  placeholder="example@malinator.com" value="' . $email . '">
              </div>
              <div class="d-flex align-items-center justify-content-center mt-3">
                <button id="submitUserDetails" type="submit" class="add-to-cart-button fs-5">Save</button>
              </div>
              </form>';
                ?>
              </div>
            </div>
          </div>
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
      $("#userDetailsForm").submit();
    });

    $("#userDetailsForm").validate({
      rules: {
        UserName: {
          required: true,
          address: true,
        },
        contactNumber: {
          required: true,
          digits: true,
          maxlength: 10,
          minlength: 10,
        },
        paymentType: {
          required: true,
          notDefault: true,
        },
      },
      messages: {
        UserName: {
          required: "Username is required.",
          address: "User name must be alphabets, digits and some allowed special characters",
        },
        contactNumber: {
          required: "Phone number is required",
          maxlength: "Phone number length must be 10",
          minlength: "Phone number length must be 10",
        },
        paymentType: {
          required: "Please select a payment type.",
          notDefault: "Please select a valid payment option.",
        },

      },
      submitHandler: function(event) {
        $("#submitUserAddress").prop("disabled", true);
        $.ajax({
          type: "POST",
          url: "./assets/api/update_user_details.php",
          data: $("#userDetailsForm").serialize(),
          dataType: "json",
          success: function(response) {
            if (response.success) window.location.href = "./my-account-details.php"
            if (response.error) {
              $("#submitUserAddress").prop("disabled", false);
              return;
            }
            if (response.orderId) {
              document.getElementById('userDetailsForm').reset();
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
  });
</script>
<?php include 'main-footer.php';
include 'footer.php'; ?>