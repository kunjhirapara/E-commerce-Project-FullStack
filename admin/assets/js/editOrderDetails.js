async function viewOrder() {
  const params = new URLSearchParams(window.location.search);
  const productId = params.get("q");

  if (!productId) {
    container.textContent = "No product specified.";
    return;
  }
  try {
    const orderResponse = await fetch("./assets/api/viewOrder.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `id=${productId}`,
    });
    const userResponse = await fetch("./assets/api/viewUserOrder.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `id=${productId}`,
    });

    const orderData = await orderResponse.json();
    const userData = await userResponse.json();
    if (userData == "") {
      Toast.warning("No data found for this order.");
      setTimeout(() => { window.location.href = "orders-listing.php"; }, 1200);
    }
    if (orderData.length === 0 || !orderData) {
      window.location.href = "orders-listing.php";
    }
    let subTotal = 0;
    for (const item of orderData) {
      subTotal += Number(item.price) * Number(item.quantity);
    }

    const coupon = orderData[0];
    let couponDiscountAmount = 0;

    if (coupon.discount_type === "percentage") {
      couponDiscountAmount =
        (subTotal * parseFloat(coupon.discount_value)) / 100;
    } else if (coupon.discount_type === "fixed") {
      couponDiscountAmount = parseFloat(coupon.discount_value);
    }

    couponDiscountAmount = Math.min(couponDiscountAmount, subTotal);

    const priceAfterDiscount = subTotal - couponDiscountAmount;

    const shippingRate = 5.0;
    const gstTax = Number(((priceAfterDiscount * 18) / 100).toFixed(2));
    const grandTotal = Number(
      (priceAfterDiscount + gstTax + shippingRate).toFixed(2)
    );
    const dateObj = new Date(orderData[0].order_date);
    const dateOnly = dateObj.toISOString().split("T")[0];

    showOrderDetails.innerHTML = `
<form method="post" id="editOrderForm">
  <div class="row row-gap-5">
    <div class="col-12 col-md-6 col-lg-6">
      <div class="order-card shadow-sm bg-white rounded-3 border h-100">
        <h5 class="mb-4">Order Details (#${orderData[0].order_id})</h5>
        <input type="hidden" value="${
          orderData[0].order_id
        }" id="orderId" name="orderId">
        <ul class="ps-0 mb-0 row">
          <li class=" pb-3">
            <p class="fs-6 ">Order date: <strong>${dateOnly}</strong></p>
          </li>
          <li class=" pb-3">
            <label for="paymentType" class="form-label fw-semibold text-capitalize">Payment Type*</label>
            <select class="form-select" name="paymentType" id="paymentType">
              <option value="cod" ${
                userData.payment_type === "cod" ? "selected" : "disabled"
              }>Cash On Delivery</option>
              <option value="paypal" ${
                userData.payment_type === "paypal" ? "selected" : "disabled"
              }>PayPal</option>
              <option value="cards" ${
                userData.payment_type === "cards" ? "selected" : "disabled"
              }>Debit card OR Credit
                card</option>
            </select>
          </li>
          <li class="col-6 pb-3">
            <label for="orderStatus" class="form-label fw-semibold text-capitalize">Order Status*</label>
            <select class="form-select" name="orderStatus" id="orderStatus">
              <option value="Pending" ${
                userData.order_status === "Pending" ? "selected" : ""
              }>Pending</option>
              <option value="In-progress" ${
                userData.order_status === "In-Progress" ? "selected" : ""
              }>In-Progress</option>
              <option value="Dispatched" ${
                userData.order_status === "Dispatched" ? "selected" : ""
              }>Dispatched</option>
              <option value="Failed" ${
                userData.order_status === "Failed" ? "selected" : ""
              }>Failed</option>
              <option value="Completed" ${
                userData.order_status === "Completed" ? "selected" : ""
              }>Completed</option>
            </select>
          </li>
          <li class="col-6">
            <label for="ShippingMethod" class="form-label fw-semibold text-capitalize">Delivery Type*</label>
            <select class="form-select" name="ShippingMethod" id="ShippingMethod">
              <option value="regular">Regular</option>
              <option value="speed">Speed Delivey</option>
              <option value="nextDay">Next Day</option>
            </select>
          </li>
        </ul>
      </div>
    </div>
    <div class="col-12 col-md-6 col-lg-6">
      <div class="order-card shadow-sm bg-white rounded-3 border h-100">
        <h5 class="mb-4">Customer Details</h5>
        <ul class="ps-0 mb-0">
          <li class=" pb-3">
            <label for="username" class="form-label fw-semibold text-capitalize">Username*</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="abc..." required
              autocomplete="off" value="${userData.username}">
          </li>
          <li class=" pb-3">
            <label for="email" class="form-label fw-semibold text-capitalize">User Email*</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="abc@gmail.com" required
              autocomplete="off" value="${userData.email}">
          </li>
          <li class="">
            <label for="contactNumber" class="form-label fw-semibold text-capitalize">Contact Number*</label>
            <input type="text" class="form-control" id="contactNumber" name="contactNumber" placeholder="5632579562"
              required autocomplete="off" value="${userData.contact_number}">
          </li>
        </ul>
      </div>
    </div>
    <div class="col-12 col-md-6">
      <div class="order-card shadow-sm bg-white rounded-3 border d-flex justify-content-between align-items-center">
        <div>
          <h5 class="mb-4">Billing Address</h5>
          <div class="row">
            <div class="col-12 mb-3">
              To, ${userData.username}
            </div>
            <div class="col-6 mb-3">
              <label for="deliveryAddress" class="form-label fw-semibold text-capitalize">Delivery Address*</label>
              <input type="text" class="form-control" id="deliveryAddress" name="deliveryAddress" required
                autocomplete="off" value="${userData.delivery_address}">
            </div>
            <div class="col-5 mb-3">
              <label for="city" class="form-label fw-semibold text-capitalize">City*</label>
              <input type="text" class="form-control" id="city" name="city" required autocomplete="off" value="${
                userData.city
              }">
            </div>
            <div class="col-md-6 mb-3">
              <label for="state" class="form-label fw-semibold text-capitalize">State*</label>
              <select id="state" name="state" class="form-select">
                <option value="Gujarat" ${
                  userData.state_name === "Gujarat" ? "selected" : "disabled"
                }>Gujarat</option>
                <option value="Delhi" ${
                  userData.state_name === "Delhi" ? "selected" : "disabled"
                }>Delhi</option>
                <option value="Punjab" ${
                  userData.state_name === "Punjab" ? "selected" : "disabled"
                }>Punjab</option>
              </select>
            </div>
            <div class="col-5">
              <label for="zipCode" class="form-label fw-semibold text-capitalize">ZipCode*</label>
              <input type="text" class="form-control" id="zipCode" name="zipCode" required autocomplete="off" value="${
                userData.zip_code
              }">
            </div>
          </div>
        </div>
        <i class="fa-solid fa-address-card address-icon"></i>
      </div>
    </div>

    <div class="col-12 col-md-6">
      <div
        class="order-card shadow-sm bg-white rounded-3 border d-flex justify-content-between align-items-center h-100">
        <div>
          <h5 class="mb-3">Delivery Address</h5>
          <div class="row">

            <div class="col-12 mb-3">
              To, ${userData.username}
            </div>
            <div class="col-12 mb-3">
              <label for="billingAddress" class="form-label fw-semibold text-capitalize">Billing Address*</label>
              <input type="text" class="form-control" id="billingAddress" name="billingAddress" required
                autocomplete="off" value="${userData.billing_address}">
            </div>
          </div>
        </div>
        <i class="fa-solid fa-truck-fast address-icon"></i>
      </div>
    </div>
    <div class="d-flex align-items-center justify-content-center mt-3">
      <button id="submitEditForm" type="submit" class="add-to-cart-button fs-5">Save User details</button>
    </div>
  </div>
</form>
<div class="mt-5">
  <div class="order-card shadow-sm bg-white rounded-3 border">
    <h5 class="mb-4">Order #${orderData[0].order_id}</h5>
    <div id="checkoutContainer">
      <div class="product-table">
        <div class="table-responsive w-100">
          <table class="table" id="cart-table">
            <thead class="">
              <tr>
                <th>Product</th>
                <th>Color</th>
                <th>QTY</th>
                <th>Action</th>
                <th>Unit Price</th>
                <th>Total Price</th>
              </tr>
            </thead>
            <tbody id="orderItemsTableBody">
              <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                  <p class="py-2">Sub Total:</p>
                </td>
                <td>
                  <p class="product-subtotal">$${subTotal}</p>
                </td>
              </tr>
          ${
            couponDiscountAmount > 0
              ? `<tr id="couponDiscountRow">
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td><p class="py-2">Discount:</p></td>
                  <td><p class="product-coupon text-success">- $${couponDiscountAmount.toFixed(
                    2
                  )} (${coupon.discount_value}${
                  coupon.discount_type === "percentage" ? "%" : "$"
                } OFF)</p></td>
                </tr>`
              : ""
          }
          <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                  <p class="py-2">GST(18%):</p>
                </td>
                <td>
                  <p class="product-tax">$${gstTax}</p>
                </td>
              </tr>
              <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                  <p class="py-2">Shipping Rate:</p>
                </td>
                <td>
                  <p class="shipping-cost">$${shippingRate}</p>
                </td>
              </tr>
              <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td>
                  <h5 class="py-2">Grand Total:</h5>
                </td>
                <td>
                  <h5 class="Total-cost">$${grandTotal}</h5>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>`;
    const tableBody = document.getElementById("orderItemsTableBody");
    orderData.forEach((productItem) => {
      const {id, name, image, price, color, stock} = productItem;
      let {quantity} = productItem;
      const tr = document.createElement("tr");
      tr.setAttribute("data-prod-id", id);
      tr.innerHTML = `
          <td class="text-start text-nowrap"><img src="../assets/images/uploads/${image}" alt="${name}" style="max-width: 80px; max-height: 80px;" /><a href="../show-product.php?name=${name}" class="product-name text-nowrap">${name}</a></td>
          <td class="${color}">${color}</td>
          <td>
          <div class="d-flex align-items-center justify-content-center">
            <input type="number" name="itemNameId-${id}" id="itemId-${id}" class="form-control text-center" style="width: 100px;" value="${quantity}" min="1" max="${stock}">
          </div>
          </td>
          <td class="text-center">
                <div class=" d-flex align-items-center justify-content-center gap-2">
                  <li>
                    <a href="javascript:void(0)" data-order-id="${
                      orderData[0].order_id
                    }" class="add-to-cart-button mb-0 save-btn">save</a>
                  </li>
                  <li>
                    <a href="javascript:void(0)" data-order-id="${
                      orderData[0].order_id
                    }" class="add-to-cart-button mb-0 delete-btn">Delete</a>
                  </li>
                </div> 
              </td>
          <td><strong>$${price}</strong></td>
          <td><strong>$${price * quantity}</strong></td>
        `;

      tableBody.prepend(tr);
      $(tr)
        .find(".delete-btn")
        .on("click", function (e) {
          e.preventDefault();

          const itemOrderId = $(this).data("order-id");

          $.ajax({
            url: "./assets/api/delete_order_item.php",
            type: "POST",
            data: {
              orderId: itemOrderId,
              itemId: id,
            },
            success: function () {
              $("#cart-table").load(location.href + " #cart-table");
              viewOrder();
            },
          });
        });
      $(`#itemId-${id}`).on("input", () => {
        quantity = $(`#itemId-${id}`).val();
      });
      $(tr)
        .find(".save-btn")
        .on("click", function (e) {
          e.preventDefault();

          const itemOrderId = $(this).data("order-id");
          if (!(quantity < 1 || quantity > stock)) {
            $.ajax({
              url: "./assets/api/save_order.php",
              type: "POST",
              data: {
                orderId: itemOrderId,
                itemId: id,
                itemQuantity: quantity,
              },
              dataType: "json",
              success: function (response) {
                console.log($("#cart-table").length);
                $("#cart-table").load(location.href + " #cart-table");
                viewOrder();
              },
              error: function (jqXHR, textStatus, errorThrown) {
                console.error("Error sending data:", textStatus, errorThrown);
              },
            });
          } else {
            Toast.warning(`Value must be between 1 and ${stock}.`);
          }
        });
    });
    $(document).ready(function () {
      $.validator.addMethod(
        "address",
        function (value, element) {
          return (
            this.optional(element) ||
            /^[a-zA-Z0-9\s.,'#\-\/()]+$/.test(value.trim())
          );
        },
        "Only alphabets, digits and some special characters are allowed."
      );
      $.validator.addMethod(
        "username",
        function (value, element) {
          return (
            this.optional(element) || /^[a-zA-Z\s.'-]+$/.test(value.trim())
          );
        },
        "Only alphabets, spaces, and valid characters (.'-) are allowed in Username."
      );
      $.validator.addMethod(
        "validEmail",
        function (value, element) {
          return (
            this.optional(element) || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)
          );
        },
        "Enter a valid email address"
      );
      $.validator.addMethod(
        "phone",
        function (value, element) {
          return this.optional(element) || /^\d{10}$/.test(value);
        },
        "Enter a valid 10-digit number"
      );
      $.validator.addMethod(
        "notDefault",
        function (value) {
          return value !== "Select one" && value !== "";
        },
        "Please select a valid option."
      );
      $("#submitEditForm").click(function () {
        $("#editOrderForm").submit();
      });

      $("#editOrderForm").validate({
        rules: {
          username: {
            required: true,
            username: true,
          },
          email: {
            required: true,
            validEmail: true,
          },
          contactNumber: {
            required: true,
            phone: true,
          },
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
          username: {
            required: "Username is required.",
          },
          email: {
            required: "Email is required.",
          },
          contactNumber: {
            required: "Contact number is required.",
            phone: "Contact Number Must be of ten digit.",
          },
          deliveryAddress: {
            required: "Delivery Address is required.",
          },
          billingAddress: {
            required: "Billing Address is required.",
          },
          city: {
            required: "City is required.",
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
        submitHandler: function (event) {
          $("#submitEditForm").prop("disabled", true);
          $.ajax({
            type: "POST",
            url: "./assets/api/update_order_data.php",
            data: $("#editOrderForm").serialize(),
            dataType: "json",
            success: function (response) {
              if (response.error) {
                $("#submitEditForm").prop("disabled", false);
                return;
              }
              if (response.orderId) {
                document.getElementById("editOrderForm").reset();
                window.location.href = "my-address.php";
              }

              $("#submitEditForm").prop("disabled", false);
            },
            error: function (jqXHR) {
              console.log(jqXHR);
              let errorMsg = "An error occurred, please try again.";
              if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
                errorMsg = jqXHR.responseJSON.error;
              }
              console.log(errorMsg);
              $("#submitEditForm").prop("disabled", false);
            },
          });
        },
      });
    });
  } catch (error) {
    console.error("Error fetching order/user data:", error);
  }
}

viewOrder();
