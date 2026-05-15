import {clearImage} from "./uploadImg.js";

async function editProduct() {
  try {
    const params = new URLSearchParams(window.location.search);
    const productId = params.get("q");
    console.log(productId);
    const orderResponse = await fetch("./assets/api/fetchProduct.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `id=${encodeURIComponent(productId)}`,
    });

    if (!orderResponse.ok) {
      throw new Error(`HTTP error! status: ${orderResponse.status}`);
    }

    const p = await orderResponse.json();

    $("#productName").val(p.name);
    $("#color").val(p.color.toLowerCase());
    $("#brand").val(p.brand);
    $("#category").val(p.category);
    $("#productPrice").val(p.price);
    $("#stock").val(p.stock);
    $("#description").val(p.description);

    const imagePath = `./../assets/images/uploads/${p.image}`;
    const dropBox = document.getElementById("dropBox");
    dropBox.style.backgroundImage = `url(${imagePath})`;
    dropBox.innerHTML = "";
    document.getElementById("upload-img").value = "";

    console.log(p);
    $.validator.addMethod(
      "productField",
      function (value, element) {
        return (
          this.optional(element) ||
          /^[a-zA-Z0-9 .,_-]{3,200}$/.test(value.trim())
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

    $("#updateProduct").on("click", (e) => {
      e.preventDefault();
      let existingInput = $("#updateProductForm #productId");
      if (existingInput.length) {
        existingInput.val(productId);
      } else {
        $("<input>")
          .attr({
            type: "hidden",
            id: "productId",
            name: "productId",
            value: productId,
          })
          .appendTo("#updateProductForm");
      }
      $("#updateProductForm").submit();
    });

    $("#updateProductForm").validate({
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
          url: "./assets/api/updateProduct.php",
          data: formData,
          contentType: false,
          processData: false,
          dataType: "json",
          success: function (res) {
            if (res.success) {
              Toast.success("Product updated successfully!");
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
        var fileType = this.files[0].type;
        if (!/^image\/(jpeg|png|gif|bmp|webp)$/.test(fileType)) {
          $("#uploadError").text("Only image files are allowed.");
          this.value = ""; // Clear invalid file
          dropBox.style.backgroundImage = ""; // Optional: clear background if invalid
        } else {
          const reader = new FileReader();
          reader.onload = function (e) {
            dropBox.style.backgroundImage = `url(${e.target.result})`;
            dropBox.innerHTML = ""; // Clear any text inside dropBox if needed
          };
          reader.readAsDataURL(this.files[0]);
        }
      } else {
        $("#uploadError").text("No Image Uploaded!!");
        dropBox.style.backgroundImage = ""; // Clear background if no file
      }
    });
  } catch (error) {
    console.error("Failed to fetch product data:", error);
  }
}
editProduct();
const dropBox = document.getElementById("dropBox");

// if (dropBox) {
//   dropBox.addEventListener("click", () => {
//     clearImage();
//   });
// }
