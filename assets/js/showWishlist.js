import { getWishlist } from "./getWishlist.js";
import { showToast } from "./showToast.js";
console.log(userId);
let cartProducts = await getWishlist();

const productCartContainer = document.querySelector("#productCartContainer");
const productCartTemplate = document.querySelector("#productCartTemplate");
const addToCartElement = document.querySelector("#addToCartElem .row");

// Function to show empty wishlist message
function showEmptyWishlist() {
  addToCartElement.innerHTML = "";
  const emptyWishlist = document.createElement("div");
  emptyWishlist.className = "col-12";
  emptyWishlist.innerHTML = `
    <div class="text-center py-5">
      <i class="fa-regular fa-heart" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
      <h2 class="mb-3">Your Wishlist is Empty</h2>
      <p class="text-muted mb-4">Browse our products and add your favorites to the wishlist!</p>
      <div class="d-flex align-items-center justify-content-center">
        <a href="./product.php" class="add-to-cart-button fs-5">
          <i class="fa-solid fa-bag-shopping me-2"></i>Browse Products
        </a>
      </div>
    </div>`;
  addToCartElement.append(emptyWishlist);
}

const quantityChange = async (event, id, stock, price) => {
  const currentElem = document.querySelector(`#card-${id}`);
  const productQuantityElem = currentElem.querySelector(".product-quantity");
  const productPriceElem = currentElem.querySelector(".product-price");

  let cartProducts = await getWishlist();
  console.log(cartProducts);
  let existingProduct = cartProducts.find((item) => item.id === id);
  let quantity = existingProduct.productQuantity;
  if (IS_LOGGED_IN) {
    existingProduct = cartProducts.find((item) => item.id === Number(id));
    quantity = parseInt(existingProduct.productQuantity);
  }
  if (event.target.classList.contains("cart-increment")) {
    if (quantity < stock) quantity++;
  } else if (event.target.classList.contains("cart-decrement")) {
    if (quantity > 1) quantity--;
  }
  const newPrice = String(price * quantity);
  if (IS_LOGGED_IN) {
    $.ajax({
      url: "./assets/api/add_to_wishlist.php",
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify({
        product_id: id,
        quantity: quantity,
      }),
      success: function (data) {},
      error: function (xhr, status, error) {
        console.error("AJAX Error:", status, error);
        Toast.error("Failed to update cart. Please try again.");
      },
    });
  }

  productQuantityElem.innerText = quantity;
  productQuantityElem.setAttribute("data-quantity", quantity);
  productPriceElem.innerText = `$${newPrice}.00`;
  return quantity;
};
async function showCartProduct() {
  let products = [];
  const res = await fetch("./assets/api/api.php");
  products = await res.json();

  if (!cartProducts || cartProducts.length === 0) {
    showEmptyWishlist();
    return;
  }

  let filterProducts = products.filter((product) => {
    return cartProducts.some((cartItem) => String(cartItem.id) === product.id);
  });
  productCartContainer.innerHTML = "";

  filterProducts.forEach((productItems) => {
    let { id, name, category, brand, price, image, stock } = productItems;
    id = Number(id);
    const filterProductClone = document.importNode(
      productCartTemplate.content,
      true
    );

    const cartProductItems = cartProducts.find((elem) => elem.id === id);
    const filterProductQuantity =
      filterProductClone.querySelector(".product-quantity");
    const filterProductCategory = filterProductClone.querySelector(".category");
    const filterQuantitySelector =
      filterProductClone.querySelector(".quantity-selector");
    const cloneRemoveBtn = filterProductClone.querySelector(
      ".remove-to-cart-button"
    );
    const MoveToCartBtn = filterProductClone.querySelector(
      ".move-to-cart-button"
    );
    filterProductClone
      .querySelector("#cart-card-value")
      .setAttribute("id", `card-${id}`);
    filterProductClone.querySelector(".product-name").textContent = name;

    filterProductClone.querySelector(".product-brand").textContent = brand;
    filterProductClone.querySelector(".product-price").textContent =
      "$" + price * cartProductItems.productQuantity + ".00";
    if (filterProductCategory) {
      filterProductCategory.textContent = category;
    }
    if (filterProductQuantity) {
      filterProductQuantity.textContent = cartProductItems.productQuantity;
    }
    filterProductClone.querySelector(
      ".product-img"
    ).src = `${baseUrl}/assets/images/uploads/${image}`;

    if (filterQuantitySelector) {
      filterQuantitySelector.addEventListener("click", (event) => {
        quantityChange(event, Number(id), stock, price);
      });
    }

    filterProductClone
      .querySelector(".product-name")
      .addEventListener("click", () => {
        window.location.href = `show-product.php?name=${name}`;
      });
    if (MoveToCartBtn) {
      MoveToCartBtn.addEventListener("click", async () => {
        cartProducts = await getWishlist();
        let removeDiv = document.querySelector(`#card-${id}`);
        console.log(removeDiv);
        if (removeDiv) {
          showToast("delete", id);
          removeDiv.remove();
        }
        $.ajax({
          url: "./assets/api/move_to_cart.php",
          method: "POST",
          contentType: "application/json",
          data: JSON.stringify({
            product_id: id,
          }),
          success: function (data) {
            // Check if wishlist is now empty
            cartProducts = cartProducts.filter(
              (item) => Number(item.id) !== id
            );
            if (!cartProducts || cartProducts.length === 0) {
              showEmptyWishlist();
            }
          },
        });
      });
    }

    if (cloneRemoveBtn) {
      cloneRemoveBtn.addEventListener("click", async () => {
        cartProducts = await getWishlist();
        if (!cartProducts || cartProducts.length === 1) {
          showEmptyWishlist();
        }
        let removeDiv = document.querySelector(`#card-${id}`);
        if (removeDiv) {
          showToast("delete", id);
          removeDiv.remove();
        }
        console.log(cartProducts);
        if (IS_LOGGED_IN) {
          $.ajax({
            url: "./assets/api/remove_wishlist_product.php",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({
              product_id: id,
            }),
            success: function (data) {
              // Check if wishlist is now empty
              cartProducts = cartProducts.filter(
                (item) => Number(item.id) !== id
              );
              console.log(cartProducts);
              if (!cartProducts || cartProducts.length === 0) {
                showEmptyWishlist();
              }
            },
          });
        }
      });
    }

    productCartContainer.append(filterProductClone);
  });
}

showCartProduct();
