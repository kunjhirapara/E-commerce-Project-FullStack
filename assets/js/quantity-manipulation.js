import { getFromLocal } from "./getFromLocal.js";
const showCartTotal = async () => {
  let cartProducts = await getFromLocal();
  let productSubTotal = document.querySelector(".product-subtotal");
  let productTotal = document.querySelector(".product-total");
  let productTax = document.querySelector(".product-tax");
  if (!Array.isArray(cartProducts)) {
    cartProducts = [];
  }
  let totalPrice = cartProducts.reduce((accum, element) => {
    let productPrice = parseFloat(element.productPrice) || 0;
    return accum + productPrice;
  }, 0);

  let tax = Number(((totalPrice * 18) / 100).toFixed(2));

  productSubTotal.innerText = `$${totalPrice.toFixed(2)}`;
  productTax.innerText = `+ $${tax}`;
  productTotal.innerText = `$${(totalPrice + tax + 5).toFixed(2)}`;
};

export const quantityToggle = (event, id, stock) => {
  const currentElem = document.querySelector(`#card-${id}`);
  const productQuantity = currentElem.querySelector(".product-quantity");

  let quantity = parseInt(productQuantity.getAttribute("data-quantity")) || 1;

  if (event.target.className === "cart-increment") {
    if (quantity < stock) {
      quantity++;
    } else if (quantity === stock) {
      quantity = stock;
    }
  }
  if (event.target.className === "cart-decrement") {
    if (quantity > 1) {
      quantity--;
    }
  }
  productQuantity.innerText = quantity;
  productQuantity.setAttribute("data-quantity", quantity);
  return quantity;
};

const setToLocal = (cart) => {
  if (!IS_LOGGED_IN) {
    localStorage.setItem("cartProducts", JSON.stringify(cart));
  }
};

export const quantityChange = async (event, id, stock, price) => {
  const currentElem = document.querySelector(`#card-${id}`);
  const productQuantityElem = currentElem.querySelector(".product-quantity");
  const productPriceElem = currentElem.querySelector(".product-price");

  let cartProducts = await getFromLocal();
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
      url: "./assets/api/add_to_cart.php",
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

  const updatedProduct = {
    id: Number(id),
    productQuantity: Number(quantity),
    productPrice: newPrice,
  };
  const updatedCart = cartProducts.map((product) =>
    product.id === id ? updatedProduct : product
  );
  setToLocal(updatedCart);
  showCartTotal();

  return quantity;
};
