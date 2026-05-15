import { clearCartElements, showCartProduct } from "./showAddToCart.js";

$(document).ready(function () {
  $("#sameAddress").on("change", function () {
    if ($(this).is(":checked")) {
      let valueToCopy = $("#deliveryAddress").val();
      $("#billingAddress").val(valueToCopy);
    } else {
      $("#billingAddress").val("");
    }
  });

  $.validator.addMethod(
    "address",
    function (value, element) {
      return (
        this.optional(element) ||
        /^[a-zA-Z0-9\s.,'#\-\/()]+$/.test(value.trim())
      );
    },
    "Only alphabets, digits and some special characters are allowed.",
  );

  $.validator.addMethod(
    "notDefault",
    function (value) {
      return value !== "Select one" && value !== "";
    },
    "Please select a valid option.",
  );

  $("#submitUserDetails").click(function () {
    $("#form").submit();
  });

  $("#form").on("submit", function (e) {
    $(this).find('input[name="cart"]').remove();

    const cartProducts = JSON.parse(
      localStorage.getItem("cartProducts") || "[]",
    );

    $("<input>")
      .attr({
        type: "hidden",
        name: "cart",
        value: JSON.stringify(cartProducts),
      })
      .appendTo(this);
  });

  $("#form").validate({
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
      paymentType: {
        required: true,
        notDefault: true,
      },
    },
    messages: {
      UserName: {
        required: "Username is required.",
        address:
          "User name must be alphabets, digits and some allowed special characters",
      },
      contactNumber: {
        required: "Phone number is required",
        maxlength: "Phone number length must be 10",
        minlength: "Phone number length must be 10",
      },
      deliveryAddress: {
        required: "Delivery Address is required.",
        address:
          "Address must be alphabets, digits and some allowed special characters",
      },
      billingAddress: {
        required: "Billing Address is required.",
        address:
          "Address must be alphabets, digits and some allowed special characters",
      },
      city: {
        required: "City is required.",
        address:
          "City name must be alphabets, digits and some allowed special characters",
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
      paymentType: {
        required: "Please select a payment type.",
        notDefault: "Please select a valid payment option.",
      },
    },
    submitHandler: function (event) {
      $("#submitUserDetails").prop("disabled", true);
      $("#formError").hide().text("");
      $.ajax({
        type: "POST",
        url: "./assets/api/update_data.php",
        data: $("#form").serialize(),
        dataType: "json",
        success: function (response) {
          if (response.error) {
            $("#formError")
              .text("Error: " + response.error)
              .show();

            $("#submitUserDetails").prop("disabled", false);
            return;
          }
          if (response.orderId) {
            localStorage.removeItem("cartProducts");
            clearCartElements();
            showCartProduct();
            form.reset();

            const appBasePath = window.location.pathname.includes("/admin/")
              ? window.location.pathname.split("/admin/")[0]
              : "";

            $.post(`${appBasePath}/assets/api/getCheckoutProductId.php`, {
              orderId: response.orderId,
            })
              .done(function () {
                window.location.href = "order-confirmation.php";
              })
              .fail(function () {
                Toast.error("Failed to send order info");
              });
          }

          $("#submitUserDetails").prop("disabled", false);
        },
        error: function (jqXHR) {
          console.log(jqXHR);
          let errorMsg = "An error occurred, please try again.";
          if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
            errorMsg = jqXHR.responseJSON.error;
          }
          $("#formError")
            .text("Error: " + errorMsg)
            .show();

          $("#submitUserDetails").prop("disabled", false);
        },
      });
    },
  });
});
