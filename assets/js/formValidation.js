import {getFromLocal} from "./getFromLocal.js";
import {clearCartElements, showCartProduct} from "./showAddToCart.js";
let cartProductIds = [];
(async function () {
  const $couponInput = $("#couponCode");
  const $applyBtn = $("#applyBtn");
  const $removeCouponBtn = $("#removeCouponBtn").hide();
  const $couponMessageContainer = $(
    "<div id='couponMessage'></div>"
  ).insertAfter($couponInput);
  const $form = $("#form");
  const $submitBtn = $("#submitUserDetails");
  const form = document.querySelector("#form");

  const cartProducts = await getFromLocal();
  if (!cartProducts || cartProducts.length === 0) {
    window.location.href = "product.php";
    return;
  }

  let categories = [];
  try {
    const res = await fetch("assets/api/api.php");

    if (!res.ok) {
      throw new Error(`API error: ${res.statusText}`);
    }

    const data = await res.json();
    cartProductIds = cartProducts.map((product) => product.id);

    categories = data.filter((product) => {
      return cartProductIds.includes(Number(product.id));
    });
    console.log(categories);
  } catch (err) {
    console.error("Error fetching categories:", err);
  }

  function showCouponMessage(message, isError = false) {
    $couponMessageContainer
      .text(message)
      .removeClass("text-danger text-success")
      .addClass(isError ? "text-danger" : "text-success")
      .show();
  }

  function clearCouponMessage() {
    $couponMessageContainer.hide().text("");
  }

  function updateTotals(coupon) {
    const $productSubTotal = $(".product-subtotal");
    const $productTotal = $(".product-total");
    const $productTax = $(".product-tax");
    const $SubtotalDiv = $("#Subtotal");

    let fullSubtotal = 0;
    cartProducts.forEach((product) => {
      const price = Number(product.productPrice) || 0;
      fullSubtotal += price;
    });

    // let categorySubtotal = 0;
    // console.log(coupon);
    // categories.forEach((product) => {
    //   cartProducts.forEach((item) => {
    //     if (
    //       Number(product.category_id) === Number(coupon.category_id) &&
    //       Number(product.id) === item.id
    //     ) {
    //       const price = Number(item.productPrice) || 0;
    //       categorySubtotal += price;
    //     }
    //   });
    // });
    let categorySubtotal = 0;
    if (coupon.category[0] === "all") {
      cartProducts.forEach((item) => {
        const price = isNaN(Number(item.productPrice))
          ? 0
          : Number(item.productPrice);
        categorySubtotal += price;
      });
    } else {
      categories.forEach((product) => {
        cartProducts.forEach((item) => {
          if (
            coupon.category.includes(product.category) &&
            Number(product.id) === item.id
          ) {
            const price = isNaN(Number(item.productPrice))
              ? 0
              : Number(item.productPrice);
            categorySubtotal += price;
          }
        });
      });
    }

    let couponDiscountAmount = 0;
    const categoryInCart = categories.find((cat) =>
      coupon.category.includes(cat.category)
    );

    if (categoryInCart || coupon.category[0] === "all") {
      if (coupon.discount_type === "percentage") {
        couponDiscountAmount =
          (categorySubtotal * parseFloat(coupon.discount_value)) / 100;
      } else {
        couponDiscountAmount = parseFloat(coupon.discount_value) || 0;
      }

      couponDiscountAmount = Math.min(couponDiscountAmount, categorySubtotal);

      const newCategorySubtotal = fullSubtotal - couponDiscountAmount;
      const tax = parseFloat(((newCategorySubtotal * 18) / 100).toFixed(2));
      const shipping = 5;

      const newSubtotal = fullSubtotal - couponDiscountAmount;

      $("#couponDiscount").remove();
      const discountHtml = `
      <div id="couponDiscount" class="d-flex justify-content-between">
        <p>Discount:</p>
        <p class="product-coupon text-success">- $${couponDiscountAmount.toFixed(2)} (${
          coupon.discount_type === "percentage"
            ? coupon.discount_value + "%"
            : "$" + coupon.discount_value
        } OFF)</p>
      </div>
    `;

      $SubtotalDiv.after(discountHtml);
      $productSubTotal.text(`$${fullSubtotal.toFixed(2)}`);
      $productTax.text(`+ $${tax.toFixed(2)}`);
      $productTotal.text(`$${(newSubtotal + tax + shipping).toFixed(2)}`);
    } else {
      showCouponMessage(
        "The coupon applies only to eligible products that are not in the cart.",
        true
      );
    }
  }

  function restoreCouponFromSession() {
    const savedCoupon = sessionStorage.getItem("appliedCoupon");
    const savedCouponCode = sessionStorage.getItem("appliedCouponCode");

    if (savedCoupon && savedCouponCode) {
      try {
        const coupon = JSON.parse(savedCoupon);

        showCouponMessage("Coupon Applied Successfully!");
        updateTotals(coupon);
        $("<input>")
          .attr({
            type: "hidden",
            id: "couponCodeValue",
            name: "couponCodeValue",
            value: coupon.coupon_code,
          })
          .appendTo("#form");

        $couponInput.val(savedCouponCode).prop("disabled", true);
        $applyBtn.hide();
        $removeCouponBtn.show();
      } catch (err) {
        console.error("Failed to parse saved coupon:", err);
        sessionStorage.removeItem("appliedCoupon");
        sessionStorage.removeItem("appliedCouponCode");
      }
    }
  }

  function initEventHandlers() {
    restoreCouponFromSession();

    $("#sameAddress").on("change", function () {
      if ($(this).is(":checked")) {
        $("#billingAddress").val($("#deliveryAddress").val());
      } else {
        $("#billingAddress").val("");
      }
    });

    $.validator.addMethod(
      "address",
      function (value, element) {
        return (
          this.optional(element) || /^[a-zA-Z0-9\s.,'-]+$/.test(value.trim())
        );
      },
      "Only alphabets, digits and some special characters (. , ' -) are allowed."
    );

    $.validator.addMethod(
      "notDefault",
      function (value) {
        return value !== "Select one" && value !== "";
      },
      "Please select a valid option."
    );

    $form.on("submit", function () {
      $(this).find('input[name="cart"]').remove();
      $(this).find('input[name="totalPrice"]').remove();

      let total = parseFloat($("#finalTotal").text().replace(/\$/g, ""));
      if (cartProducts) {
        $("<input>")
          .attr({
            type: "hidden",
            name: "cart",
            value: JSON.stringify(cartProducts),
          })
          .appendTo(this);
      }
      if (total) {
        $("<input>")
          .attr({type: "hidden", name: "totalPrice", value: total})
          .appendTo(this);
      }
    });

    $form.validate({
      rules: {
        UserName: {
          required: true,
          address: true,
        },
        contactNumber: {
          required: true,
          digits: true,
          minlength: 10,
          maxlength: 10,
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
          minlength: 6,
          maxlength: 6,
        },
        paymentType: {
          required: true,
          notDefault: true,
        },
      },
      messages: {
        UserName: {
          required: "Username is required.",
          address:
            "User name must contain alphabets, digits, or special characters (. , ' -)",
        },
        contactNumber: {
          required: "Phone number is required.",
          minlength: "Phone number must be exactly 10 digits.",
          maxlength: "Phone number must be exactly 10 digits.",
        },
        deliveryAddress: {
          required: "Delivery Address is required.",
          address: "Address contains invalid characters.",
        },
        billingAddress: {
          required: "Billing Address is required.",
          address: "Address contains invalid characters.",
        },
        city: {
          required: "City is required.",
          address: "City contains invalid characters.",
        },
        state: {
          required: "Please select an option.",
          notDefault: "Please select a valid option.",
        },
        zipCode: {
          required: "Zip code is required.",
          minlength: "Zip code must be exactly 6 digits.",
          maxlength: "Zip code must be exactly 6 digits.",
        },
        paymentType: {
          required: "Please select a payment type.",
          notDefault: "Please select a valid payment option.",
        },
      },
      submitHandler: function () {
        $submitBtn.prop("disabled", true);
        $("#formError").hide().text("");

        $.ajax({
          type: "POST",
          url: "./assets/api/update_data.php",
          data: $form.serialize(),
          dataType: "json",
          success: function (response) {
            console.log($form.serialize());
            if (response.error) {
              $("#formError")
                .text("Error: " + response.error)
                .show();
              $submitBtn.prop("disabled", false);
              return;
            }
            if (response.orderId) {
              localStorage.removeItem("cartProducts");
              sessionStorage.removeItem("appliedCoupon");
              sessionStorage.removeItem("appliedCouponCode");
              clearCartElements();
              showCartProduct();
              form.reset();

              $.post("assets/api/getCheckoutProductId.php", {
                orderId: response.orderId,
              })
                .done(() => {
                  window.location.href = "order-confirmation.php";
                })
                .fail(() => {
                  Toast.error("Failed to send order info");
                });
            }
            $submitBtn.prop("disabled", false);
          },
          error: function (jqXHR) {
            let errorMsg = "An error occurred, please try again.";
            if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
              errorMsg = jqXHR.responseJSON.error;
            }
            $("#formError")
              .text("Error: " + errorMsg)
              .show();
            $submitBtn.prop("disabled", false);
          },
        });
      },
    });

    $couponInput.on("keydown", function (e) {
      if (e.key === "Enter") {
        e.preventDefault();
        $applyBtn.trigger("click");
      }
    });

    $applyBtn.on("click", function (e) {
      e.preventDefault();
      clearCouponMessage();

      const couponValue = $couponInput.val().trim();
      const pattern = /^[A-Za-z0-9\- ]+$/;

      if (!couponValue) {
        showCouponMessage("Please enter a coupon code", true);
        return;
      }
      if (!pattern.test(couponValue)) {
        showCouponMessage("Invalid Coupon Format", true);
        return;
      }
      if (couponValue) {
        $("<input>")
          .attr({
            type: "hidden",
            id: "couponCodeValue",
            name: "couponCodeValue",
            value: couponValue,
          })
          .appendTo("#form");
      }
      $("#applyBtn").prop("disabled", true);

      $.ajax({
        type: "POST",
        url: "assets/api/coupon_data.php",
        data: {coupon: couponValue, categories: JSON.stringify(cartProductIds)},
        dataType: "json",
        success: function (response) {
          console.log(cartProductIds);
          if (response.error) {
            showCouponMessage(response.error, true);
            $applyBtn.prop("disabled", false);
            return;
          }

          const coupon = response.coupon;

          const subtotal = parseFloat(
            $(".product-subtotal").text().replace("$", "")
          );
          if (coupon.minimum_purchase && subtotal < coupon.minimum_purchase) {
            showCouponMessage(
              `Minimum Cart Value to avail this coupon is $${coupon.minimum_purchase}.`,
              true
            );
            $applyBtn.prop("disabled", false);
            return;
          }
          updateTotals(coupon);
          showCouponMessage("Coupon Applied Successfully!");
          sessionStorage.setItem("appliedCoupon", JSON.stringify(coupon));
          sessionStorage.setItem("appliedCouponCode", couponValue);

          $couponInput.prop("disabled", true);
          $applyBtn.hide();
          $removeCouponBtn.show();
        },
        error: function (jqXHR) {
          let errorMsg = "An error occurred, please try again.";
          if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
            errorMsg = jqXHR.responseJSON.error;
          }
          showCouponMessage(errorMsg, true);
          $applyBtn.prop("disabled", false);
        },
      });
    });

    $removeCouponBtn.on("click", function () {
      $.post("assets/api/remove_coupon.php", function (response) {
        if (response.success) {
          $("#couponCodeValue").remove();
          $couponInput.prop("disabled", false).val("");
          showCouponMessage(response.success, true);
          $applyBtn.prop("disabled", false).show();
          $removeCouponBtn.hide();

          sessionStorage.removeItem("appliedCoupon");
          sessionStorage.removeItem("appliedCouponCode");

          const $productSubTotal = $(".product-subtotal");
          const $productTax = $(".product-tax");
          const $productTotal = $(".product-total");

          let originalSubtotal = 0;
          cartProducts.forEach((product) => {
            const price = Number(product.productPrice) || 0;
            originalSubtotal += price;
          });

          const tax = parseFloat(((originalSubtotal * 18) / 100).toFixed(2));
          const shipping = 5;

          $productSubTotal.text(`$${originalSubtotal.toFixed(2)}`);
          $productTax.text(`+ $${tax.toFixed(2)}`);
          $productTotal.text(
            `$${(originalSubtotal + tax + shipping).toFixed(2)}`
          );

          $("#couponDiscount").remove();
        } else {
          showCouponMessage("Failed to remove coupon. Please try again.", true);
        }
      });
    });
  }

  $(document).ready(initEventHandlers);
})();
