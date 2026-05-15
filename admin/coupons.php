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
          <a href="./dashboard.php" class="nav-link text-white">
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
          <a href="javascript:void(0)" class="nav-link text-white">
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
      <li><a class="dropdown-item" href="#">My Profile</a></li>
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
            <a href="./dashboard.php" class="nav-link text-white">
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
            <a href="javascript:void(0)" class="nav-link text-white active">
              <i class="fa-solid fa-tags"></i> Coupons
            </a>
          </li>
        </ul>
      </nav>

      <main class="col-12 col-md-9 ms-sm-auto col-lg-10 px-md-4 my-5 pt-5">
        <div class="row">
          <div class="container my-5 col-12 col-xl-6">
            <h2>Create Coupon</h2>
            <form id="couponForm" method="post">
              <div id="formError"></div>
              <div class="row">
                <div class="col-6 mb-3">
                  <label for="coupon_code" class="form-label">Coupon Code *</label>
                  <input type="text" class="form-control" id="coupon_code" name="coupon_code" maxlength="50" required
                    autocomplete="off" />
                </div>

                <div class="col-6 mb-3">
                  <label for="discount_type" class="form-label">Discount Type *</label>
                  <select class="form-select" id="discount_type" name="discount_type" required>
                    <option value="">-- Select Type --</option>
                    <option value="percentage">Percentage</option>
                    <option value="fixed">Fixed Amount</option>
                  </select>
                </div>

                <div class="col-6 mb-3">
                  <label for="discount_value" class="form-label">Discount Value *</label>
                  <input type="number" step="0.01" class="form-control" id="discount_value" name="discount_value"
                    min="0.01" required />
                </div>

                <div class="col-6 mb-3">
                  <label for="minimum_purchase" class="form-label">Minimum Purchase</label>
                  <input type="number" step="0.01" class="form-control" id="minimum_purchase" name="minimum_purchase"
                    min="10" value="10" />
                </div>

                <div class="col-6 mb-5">
                  <label for="expiry_date" class="form-label">Expiry Date *</label>
                  <input type="date" class="form-control" id="expiry_date" name="expiry_date" required />
                </div>

                <div class="col-6 mb-5">
                  <label for="usage_limit" class="form-label">Usage Limit</label>
                  <input type="number" class="form-control" id="usage_limit" name="usage_limit" min="1" value="1" />
                </div>

                <div class="col-12 mb-5">
                  <label for="applicable_category" class="form-label">Applicable Categories *</label>
                  <div class="custom-select mx-auto">
                    <div class="select-box">
                      <!-- Hidden input to store selected values -->
                      <input type="hidden" class="selected-values" id="applicable_category"
                        name="applicable_category" />

                      <div class="selected-options">
                        <span class="placeholder">Select the tags</span>
                      </div>
                      <div class="arrow">
                        <i class="fa fa-angle-down"></i>
                      </div>
                    </div>
                    <div class="options">
                      <div class="option-search-tags">
                        <input type="text" id="searchField" name="searchField" class="search-tags"
                          placeholder="Search tags..." />
                      </div>
                      <?php
                      $sql = "SELECT id, name FROM categories ORDER BY name";
                      $result = $conn->query($sql);
                      if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                          if ($row["name"] === "all") {
                            echo '<div class="option all-tags" data-value="' . $row["name"] . '">Select All</div>';
                          } else {
                            echo '<div class="option" data-value="' . $row["name"] . '">' . $row["name"] . '</div>';
                          }
                        }
                      }
                      ?>
                      <div class="no-result-message" style="display: none;">No Result Found...</div>
                    </div>
                    <span class="tag-error-message dropdown-error"></span>
                  </div>

                </div>
                <div class="col-3 mx-auto"> <button type="submit" class="add-to-cart-button w-100"
                    id="couponFormSubmit">Submit</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </main>
    </div>
  </div>
</section>
<?php include '../main-footer.php' ?>
<script type="module" src="./assets/js/script.js"></script>
<script type="module" defer>
$(document).ready(function() {
  $.validator.addMethod("futureDate", function(value, element) {
    let now = new Date();
    let inputDate = new Date(value);
    return this.optional(element) || inputDate >= now.setHours(0, 0, 0, 0);
  }, "Please enter today or a future date.");

  $("#couponForm").validate({
    rules: {
      coupon_code: {
        required: true,
        maxlength: 50
      },
      discount_type: {
        required: true
      },
      discount_value: {
        required: true,
        number: true,
        min: 1
      },
      minimum_purchase: {
        number: true,
        min: 0.00,
        required: true,
      },
      expiry_date: {
        required: true,
        date: true,
        futureDate: true
      },
      usage_limit: {
        required: true,
        number: true,
        min: 1
      },
      applicable_category: {
        maxlength: 255,
        required: true,

      }
    },
    messages: {
      coupon_code: {
        required: "Please enter a coupon code.",
        maxlength: "Coupon code cannot exceed 50 characters."
      },
      discount_type: {
        required: "Please select a discount type."
      },
      discount_value: {
        required: "Please enter a discount value.",
        number: "Please enter a valid number.",
        min: "Discount value must be at least 0.01."
      },
      minimum_purchase: {
        number: "Please enter a valid number.",
        min: "Minimum purchase cannot be negative."
      },
      expiry_date: {
        required: "Please select an expiry date.",
        date: "Please enter a valid date.",
        futureDate: "Expiry date must be today or in the future."
      },
      usage_limit: {
        number: "Please enter a valid number.",
        min: "Usage limit must be at least 1."
      },
      applicable_category: {
        maxlength: "Applicable items cannot exceed 255 characters."
      }
    },
    submitHandler: function(form) {
      let couponInput = $("#coupon_code");
      let lowerCoupon = couponInput.val().toLowerCase();
      couponInput.val(lowerCoupon);

      // Validate that at least one category is selected
      const selectedCategories = $("#applicable_category").val();
      if (!selectedCategories || selectedCategories.trim() === "") {
        $(".tag-error-message").text("Please select at least one category.").show();
        return false;
      }

      $("#couponFormSubmit").prop("disabled", true);
      $("#formError").text("");

      $.ajax({
        type: "POST",
        url: "assets/api/coupon_data.php",
        data: $("#couponForm").serialize(),
        dataType: "json",
        success: function(response) {
          console.log($("#couponForm").serialize());
          console.log(response);
          $("#couponFormSubmit").prop("disabled", false);
          if (response.success) {
            $("#formError").text("");
            form.reset();

            // Reset the custom select dropdown
            const customSelect = document.querySelector(".custom-select");
            if (customSelect) {
              const options = customSelect.querySelectorAll(".option.active");
              options.forEach(function(option) {
                option.classList.remove("active");
              });
              customSelect.querySelector(".option.all-tags")?.classList.remove("active");
              customSelect.querySelector(".selected-options").innerHTML =
                '<span class="placeholder">Select the tags</span>';
              $("#applicable_category").val("");
            }

            Toast.success("Coupon created successfully.");
          }
          if (response.error) {
            $("#formError").text("Error: " + response.error);
            Toast.error(response.error);
          }
        },
        error: function(jqXHR) {
          console.log(jqXHR);
          let errorMsg = "An error occurred, please try again.";
          if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
            errorMsg = jqXHR.responseJSON.error;
          }
          $("#couponFormSubmit").prop("disabled", false);
          Toast.error(errorMsg);
        },
      });
    }
  });
});
</script>