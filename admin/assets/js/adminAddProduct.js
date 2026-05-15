import { clearImage } from "./uploadImg.js";

$(document).ready(function () {
  $.validator.addMethod(
    "productField",
    function (value, element) {
      return (
        this.optional(element) || /^[a-zA-Z0-9 .,_-]{3,200}$/.test(value.trim())
      );
    },
    "Only letters, numbers, and basic symbols are allowed."
  );

  $.validator.addMethod(
    "validPrice",
    function (value) {
      return /^\d+(\.\d{1,2})?$/.test(value);
    },
    "Enter a valid price (e.g. 10.00)"
  );

  $.validator.addMethod(
    "validStock",
    function (value) {
      return /^\d+$/.test(value);
    },
    "Enter a valid stock number"
  );

  $.validator.addMethod(
    "notDefault",
    function (value) {
      return value !== "" && value !== "Select one";
    },
    "Please select a valid option."
  );

  $.validator.addMethod(
    "fileRequired",
    function (value, element) {
      return element.files && element.files.length > 0;
    },
    "Product image is required."
  );

  $.validator.addMethod(
    "fileType",
    function (value, element) {
      if (element.files.length === 0) return true;
      var fileType = element.files[0].type;
      return /^image\/(jpeg|png|gif|bmp|webp)$/.test(fileType);
    },
    "Only image files are allowed."
  );

  $("#productForm").validate({
    rules: {
      productName: {
        required: true,
        productField: true,
      },
      color: {
        required: true,
        notDefault: true,
      },
      brand: {
        required: true,
        notDefault: true,
      },
      category: {
        required: true,
        productField: true,
      },
      productPrice: {
        required: true,
        validPrice: true,
      },
      stock: {
        required: true,
        validStock: true,
      },
      description: {
        required: true,
        productField: true,
      },
      Upload: {
        fileRequired: true,
        fileType: true,
      },
    },
    messages: {
      productName: {
        required: "Product name is required.",
      },
      color: {
        required: "Color is required.",
      },
      brand: {
        required: "Brand is required.",
      },
      category: {
        required: "Category is required.",
      },
      productPrice: {
        required: "Price is required.",
        validPrice: "Invalid price format.",
      },
      stock: {
        required: "Stock is required.",
        validStock: "Invalid stock number.",
      },
      description: {
        required: "Description is required.",
      },
      Upload: {
        fileRequired: "Product image is required.",
        fileType: "Only image files are allowed.",
      },
    },

    // errorPlacement: function (error, element) {
    //   error.insertAfter(element);
    //   let bgImg = $("#dropBox").css("background-image");
    //   if (bgImg === "none") {
    //     $("#uploadError").html("No Image Uploaded!!");
    //   } else {
    //     $("#uploadError").text(" ");
    //   }
    // },
    errorPlacement: function (error, element) {
      if (element.attr("name") === "Upload") {
        // Show validation errors for the hidden file input inside the visible #uploadError container
        $("#uploadError").text(error.text());
      } else {
        // Default behavior for other fields
        error.insertAfter(element);
      }
    },

    submitHandler: function (form) {
      $("#submitProduct").prop("disabled", true).text("Submitting...");
      $("#formError").hide().text("");

      let formData = new FormData(form);
      $.ajax({
        type: "POST",
        url: "./assets/api/productValidation.php",
        data: formData,
        contentType: false,
        processData: false,
        dataType: "json",
        success: function (res) {
          if (res.success) {
            Toast.success("Product added successfully!");
            form.reset();
            clearImage();
            $("#formError").hide();
            $("#closeModalBtn").click();
          } else {
            Toast.error(res.error || "An error occurred");
          }
          $("#submitProduct").prop("disabled", false).text("Add Product");
        },

        error: function (jqXHR, textStatus, errorThrown) {
          let errorMsg = "An error occurred, please try again.";
          if (jqXHR.responseJSON && jqXHR.responseJSON.error) {
            errorMsg = jqXHR.responseJSON.error;
          } else if (textStatus) {
            errorMsg = textStatus + ": " + errorThrown;
          }
          Toast.error(errorMsg);
          $("#submitProduct").prop("disabled", false).text("Add Product");
        },
      });
    },
  });

  $("#upload-img").on("change", function () {
    $("#uploadError").empty(); // Clear previous error message

    if (this.files && this.files.length > 0) {
      // Check if file type is valid immediately
      var fileType = this.files[0].type;
      if (!/^image\/(jpeg|png|gif|bmp|webp)$/.test(fileType)) {
        $("#uploadError").text("Only image files are allowed.");
        this.value = ""; // Clear invalid file
      }
    } else {
      $("#uploadError").text("No Image Uploaded!!");
    }
  });
});
