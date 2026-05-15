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

<script>
  (function () {
    const params = new URLSearchParams(window.location.search);
    const status = params.get("status");
    if (!status) return;

    const messages = {
      deleted:    { type: "success", text: "Product deleted successfully." },
      not_found:  { type: "warning", text: "Product not found or already deleted." },
      invalid_id: { type: "error",   text: "Invalid product ID." },
      db_error:   { type: "error",   text: "Database error. Please try again." },
    };
    const msg = messages[status];
    if (!msg) return;

    const showWhenReady = () => {
      if (window.Toast) {
        Toast[msg.type](msg.text);
        params.delete("status");
        const clean = window.location.pathname + (params.toString() ? "?" + params : "");
        window.history.replaceState({}, "", clean);
      } else {
        setTimeout(showWhenReady, 50);
      }
    };
    showWhenReady();
  })();
</script>

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
          <a href="javascript:void(0)" class="nav-link text-white">
            <i class="fa-solid fa-cube"></i> Products
          </a>
        </li>
        <li class="nav-item">
          <a href="./orders-listing.php" class="nav-link text-white">
            <i class="fa-solid fa-table-cells-large"></i> Order Listing
          </a>
        </li>
        <li class="nav-item">
          <a href="./coupons.php" class="nav-link text-white">
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
      <li><a class="dropdown-item" href="./my-profile.php">My Profile</a></li>
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
            <a href="javascript:void(0)" class="nav-link text-white">
              <i class="fa-solid fa-cube"></i> Products
            </a>
          <li class="nav-item">
            <a href="./orders-listing.php" class="nav-link text-white">
              <i class="fa-solid fa-table-cells-large"></i> Order Listing
            </a>
          </li>
          <li class="nav-item">
            <a href="./coupons.php" class="nav-link text-white">
              <i class="fa-solid fa-tags"></i> Coupons
            </a>
          </li>
        </ul>
      </nav>
      <main class="col-12 col-md-9 ms-sm-auto col-lg-10 px-md-4 my-5 pt-5">
        <div class="mt-4">
          <div class="table-container">
            <div
              class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between mb-5">
              <div class="d-flex flex-column align-items-start justify-content-center gap-0 mb-3">
                <h3 class="products mb-0 lh-1">Products</h3>
                <p class="breadcrumb mb-0">Home > eCommerce > Add Product</p>
              </div>
              <div class="btn-container">
                <button type="button" class="add-to-cart-button mt-0" data-bs-toggle="modal"
                  data-bs-target="#filterModal" id="filterProduct">
                  <i class="fa-solid fa-filter"></i>Filter
                </button>


                <!-- Modal -->
                <div class="modal fade" id="filterModal" data-bs-backdrop="static" data-bs-keyboard="false"
                  tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h1 class="modal-title fs-5" id="filterModalLabel">Choose The Filters</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                        <form class="form-control p-4" method="post" id="filterForm">
                          <div class="row">
                            <div class="col-6">
                              <h3 class="section-title-h3 mt-3">Select A category</h3>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Mobiles" id="Mobiles" />
                                <label class="form-check-label" for="Mobiles">Mobiles</label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Computers" id="Computers" />
                                <label class="form-check-label" for="Computers">Computers</label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Audio" id="Audio" />
                                <label class="form-check-label" for="Audio">Audio</label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Wearables" id="Wearables" />
                                <label class="form-check-label" for="Wearables">Wearables</label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Video" id="Video" />
                                <label class="form-check-label" for="Video">Video</label>
                              </div>
                            </div>
                            <div class="col-6">
                              <h3 class="section-title-h3 mt-3">Select A Color</h3>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Red" id="Red" />
                                <label class="form-check-label" for="Red">Red</label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Orange" id="Orange" />
                                <label class="form-check-label" for="Orange">Orange</label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Yellow" id="Yellow" />
                                <label class="form-check-label" for="Yellow">Yellow</label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Green" id="Green" />
                                <label class="form-check-label" for="Green">Green</label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Blue" id="Blue" />
                                <label class="form-check-label" for="Blue">Blue</label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Indigo" id="Indigo" />
                                <label class="form-check-label" for="Indigo">Indigo</label>
                              </div>
                            </div>
                            <div class="col-6">
                              <h3 class="section-title-h3 mt-3">Select A Brand</h3>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="TechGadget" id="TechGadget" />
                                <label class="form-check-label" for="TechGadget">
                                  TechGadget
                                </label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="ExampleBrand" id="ExampleBrand" />
                                <label class="form-check-label" for="ExampleBrand">
                                  ExampleBrand
                                </label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="FitTech" id="FitTech" />
                                <label class="form-check-label" for="FitTech"> FitTech </label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="SoundBeats" id="SoundBeats" />
                                <label class="form-check-label" for="SoundBeats">
                                  SoundBeats
                                </label>
                              </div>
                              <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="Samsung" id="Samsung" />
                                <label class="form-check-label" for="Samsung"> Samsung </label>
                              </div>
                            </div>
                            <div class="form">
                              <label for="price" class="form-label section-title-h3">Price:</label>
                              <p class="range-values">
                                <span class="min-price">$0</span>
                                <span class="fw-bold">–</span>
                                <span class="max-price">$100</span>
                              </p>
                              <input type="range" class="form-range" id="price" name="price" />
                            </div>
                            <div class="d-flex justify-content-evenly">
                              <button type="submit" data-bs-dismiss="modal" class="add-to-cart-button fs-5"
                                id="filterBtn">
                                Filter
                              </button>
                              <a class="add-to-cart-button fs-5 d-inline-flex" id="resetBtn">
                                reset
                              </a>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- Button trigger modal -->
                <button type="button" class="add-to-cart-button mt-0" data-bs-toggle="modal"
                  data-bs-target="#centerModal" id="addProduct">
                  Add Product
                </button>

                <!-- Modal -->
                <div class="modal fade" id="centerModal" data-bs-backdrop="static" data-bs-keyboard="false"
                  tabindex="-1" aria-labelledby="centerModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h1 class="modal-title fs-5" id="centerModalLabel">Enter Product Details</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"
                          id="closeModalBtn"></button>
                      </div>
                      <div class="modal-body">
                        <div id="formError" style="display:none; color:red;"></div>

                        <form id="productForm" enctype="multipart/form-data">
                          <div class="row">
                            <div class="mb-3 col-6">
                              <label for="productName" class="form-label">Product Name*</label>
                              <input type="text" name="productName" id="productName" class="form-control"
                                placeholder="eg. Macbook air m1">
                            </div>

                            <div class="mb-3 col-6">
                              <label for="color" class="form-label">Color*</label>
                              <select name="color" id="color" class="form-select">
                                <option value="">Select one</option>
                                <option value="red">Red</option>
                                <option value="orange">Orange</option>
                                <option value="yellow">Yellow</option>
                                <option value="green">Green</option>
                                <option value="blue">Blue</option>
                                <option value="indigo">Indigo</option>
                                <option value="violet">Violet</option>
                              </select>
                            </div>
                            <div class="mb-3 col-6">
                              <label for="brand" class="form-label">Brand*</label>
                              <select name="brand" id="brand" class="form-select">
                                <option value="">Select one</option>
                                <option value="TechGadget">TechGadget</option>
                                <option value="ExampleBrand">ExampleBrand</option>
                                <option value="FitTech">FitTech</option>
                                <option value="SoundBeats">SoundBeats</option>
                                <option value="Samsung">Samsung</option>
                              </select>
                            </div>

                            <div class="mb-3 col-6">
                              <label for="category" class="form-label">Category*</label>
                              <select id="category" name="category" class="form-select" required>
                                <option value="">-- Select Category --</option>
                                <?php
                                $sql = "SELECT id, name FROM categories WHERE name != 'all' ORDER BY name";
                                $result = $conn->query($sql);
                                if ($result && $result->num_rows > 0) {
                                  while ($row = $result->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row["name"]) . '">' . htmlspecialchars(ucfirst($row["name"])) . '</option>';
                                  }
                                }
                                ?>
                              </select>
                            </div>

                            <!-- <div class="mb-3 col-6">
                          <label for="productPrice" class="form-label">Price*</label>
                          <input type="text" id="productPrice" name="productPrice" class="form-control"
                            placeholder="eg. 600$">
                        </div> -->
                            <div class="mb-3 col-6 position-relative">
                              <label for="productPrice" class="form-label">Price*</label>
                              <div class="input-group mb-3">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control" id="productPrice" name="productPrice"
                                  aria-label="Amount (to the nearest dollar)">
                              </div>
                            </div>

                            <div class="mb-3 col-6">
                              <label for="stock" class="form-label">Stock*</label>
                              <input type="number" id="stock" name="stock" class="form-control" />
                            </div>

                            <div class="mb-3">
                              <label for="description" class="form-label">Description*</label>
                              <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="d-flex flex-column justify-content-center align-items-center mt-5">
                              <div id="dropBox">
                                <i class="fa-solid fa-cloud-arrow-up fa-2x mb-2"></i>
                                <span>Click or drag file to upload</span>

                              </div>

                              <input type="file" name="Upload" id="upload-img" accept="image/*" hidden />
                              <div id="uploadError" class="error-message"></div>
                            </div>
                            <div class="mb-3 text-center">
                              <button type="submit" id="submitProduct" class="add-to-cart-button">
                                Add Product
                              </button>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="product-table py-3 px-4 bg-white">
              <div class="table-responsive">
                <table class="table">
                  <thead>
                    <tr>
                      <th scope="col">Product</th>
                      <th scope="col">Name</th>
                      <th scope="col">Brand</th>
                      <th scope="col">Category</th>
                      <th scope="col">Price</th>
                      <th scope="col">Stock</th>
                      <th scope="col">Color</th>
                      <th scope="col">State</th>
                      <th scope="col">Action</th>
                    </tr>
                  </thead>
                  <tbody id="product-data">
                  </tbody>
                </table>
              </div>

              <div id="paginationContainer" class="mt-3 flex-wrap">

              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</section>


<script type="module" src="<?php echo $Admin_Base_Url; ?>/assets/js/uploadImg.js"></script>
<script type="module" src="<?php echo $Admin_Base_Url; ?>/assets/js/adminFilterProducts.js"></script>
<script defer type="module" src="<?php echo $Admin_Base_Url; ?>/assets/js/adminAddProduct.js"></script>


<?php include './main-footer.php' ?>