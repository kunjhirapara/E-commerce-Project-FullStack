import { getFromLocal } from "./getFromLocal.js";
import { quantityChange } from "./quantity-manipulation.js";
import { showToast } from "./showToast.js";
import { updateCart } from "./updateCartValue.js";

let cartProducts = await getFromLocal();
const showCartTotal = async (cartProducts) => {
  let productSubTotal = document.querySelector(".product-subtotal");
  let productTotal = document.querySelector(".product-total");
  let productTax = document.querySelector(".product-tax");
  if (!Array.isArray(cartProducts)) {
    cartProducts = [];
  }
  let totalPrice = cartProducts.reduce((accum, element) => {
    let productPrice = parseFloat(element.productPrice);
    return accum + productPrice;
  }, 0);

  let tax = Number(((totalPrice * 18) / 100).toFixed(2));

  productSubTotal.innerText = `$${totalPrice.toFixed(2)}`;
  productTax.innerText = `+ $${tax}`;
  productTotal.innerText = `$${(totalPrice + tax + 5).toFixed(2)}`;
};

const productCartContainer = document.querySelector("#productCartContainer");
const productCartTemplate = document.querySelector("#productCartTemplate");
const addToCartElement = document.querySelector("#addToCartElem .row");
export function clearCartElements() {
  productCartContainer.innerHTML = "";
}

export async function showCartProduct() {
  let products = [];
  const res = await fetch(`${baseUrl}/assets/api/api.php`);
  products = await res.json();

  if (cartProducts.length === 0 && addToCartElement) {
    addToCartElement.innerHTML = "";
    const emptyCart = document.createElement("div");
    emptyCart.innerHTML = `<h2 class="text-center">Cart is Empty Please select an item...</h2>
    <div class="d-flex align-items-center justify-content-center mt-5">
    <a href="./product.php" class="add-to-cart-button fs-5"><i class="fa-solid fa-cart-shopping me-2">
            </i><span>Go to Shop</span></a>
  </div>`;
    addToCartElement.append(emptyCart);
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
      true,
    );

    const cartProductItems = cartProducts.find((elem) => elem.id === id);
    const filterProductQuantity =
      filterProductClone.querySelector(".product-quantity");
    const filterProductCategory = filterProductClone.querySelector(".category");
    const filterQuantitySelector =
      filterProductClone.querySelector(".quantity-selector");
    const cloneRemoveBtn = filterProductClone.querySelector(
      ".remove-to-cart-button",
    );
    filterProductClone
      .querySelector("#cart-card-value")
      .setAttribute("id", `card-${id}`);
    filterProductClone.querySelector(".product-name").textContent = name;

    filterProductClone.querySelector(".product-brand").textContent = brand;
    filterProductClone.querySelector(".product-price").textContent =
      "$" + cartProductItems.productPrice + ".00";
    if (filterProductCategory) {
      filterProductCategory.textContent = category;
    }
    if (filterProductQuantity) {
      filterProductQuantity.textContent = cartProductItems.productQuantity;
    }
    filterProductClone.querySelector(".product-img").src =
      `${baseUrl}/assets/images/uploads/${image}`;

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

    if (cloneRemoveBtn) {
      cloneRemoveBtn.addEventListener("click", async () => {
        cartProducts = await getFromLocal();
        console.log(cartProducts);
        if (!cartProducts || cartProducts.length === 1) {
          window.location.href = "product.php";
        }
        const updatedCart = cartProducts.filter(
          (currentElem) => currentElem.id !== id,
        );
        let removeDiv = document.querySelector(`#card-${id}`);
        console.log(removeDiv);
        if (removeDiv) {
          showToast("delete", id);
          removeDiv.remove();
        }
        if (IS_LOGGED_IN) {
          $.ajax({
            url: "./assets/api/remove_product.php",
            method: "POST",
            contentType: "application/json",
            data: JSON.stringify({
              product_id: id,
            }),
            success: function (data) {},
          });
        } else {
          localStorage.setItem("cartProducts", JSON.stringify(updatedCart));
        }
        updateCart();
        showCartTotal(updatedCart);
      });
    }

    productCartContainer.append(filterProductClone);
  });

  showCartTotal(cartProducts);
}
showCartProduct();
