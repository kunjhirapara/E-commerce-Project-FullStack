const productCartTableBody = document.querySelector("#cart-table tbody");

const showCartProduct = async (cartProducts) => {
  let products = [];
  try {
    const res = await fetch("assets/api/checkoutFilteredProducts.php");
    products = await res.json();
  } catch (error) {
    console.error("Error fetching products:", error);
    products = [];
  }

  if (cartProducts.length === 0) {
    productCartTableBody.innerHTML = `
      <tr>
        <td colspan="5" class="text-center">Cart is Empty Please select an item...</td>
      </tr>`;
    showCartTotal([]);
    return;
  }
  let cartItemQuantity = [];
  cartItemQuantity = cartProducts.map((element) => {
    return element.quantity;
  });

  let orderNo = document.querySelector("#orderNo");
  orderNo.textContent = cartProducts[0].order_id;

  let filterProducts = products.filter((product) =>
    cartProducts.some((cartItem) => cartItem.product_id === product.id)
  );

  productCartTableBody.innerHTML = "";
  let i = 0;
  filterProducts.forEach((productItem) => {
    const { name, image, price, color } = productItem;

    const tr = document.createElement("tr");

    tr.innerHTML = `
      <td class="text-start d-flex flex-wrap align-items-center gap-2"><img class="product-img" src="${baseUrl}/assets/images/uploads/${image}" alt="${name}" style="max-width: 80px; max-height: 80px;" /> ${name}</td>
      <td class="${color}">${color}</td>
      <td>${cartItemQuantity[i]}</td>
      <td><strong>$${price}</span></strong></td>
      <td><strong>$${price * cartItemQuantity[i]}</span></strong></td>
    `;

    productCartTableBody.appendChild(tr);
    i++;
  });
};

async function main() {
  try {
    const response = await fetch(`${baseUrl}/assets/api/getCheckoutProductId.php`);
    if (!response.ok) throw new Error("Failed to fetch cart products");
    const cartProducts = await response.json();

    await showCartProduct(cartProducts);

    const coupon = cartProducts[0];
    console.log(coupon);

    const productSubTotal = document.querySelector(".product-subtotal");
    const productTotal = document.querySelector(".product-total");
    const productTax = document.querySelector(".product-tax");

    const totalPrice = cartProducts.reduce((accum, element) => {
      const productPrice = parseFloat(element.price) || 0;
      return accum + productPrice;
    }, 0);

    let couponDiscountAmount = 0;
    if (coupon.discount_type === "percentage") {
      couponDiscountAmount =
        (totalPrice * parseFloat(coupon.discount_value)) / 100;
    } else if (coupon.discount_type === "fixed") {
      couponDiscountAmount = parseFloat(coupon.discount_value);
    }

    couponDiscountAmount = Math.min(couponDiscountAmount, totalPrice);
    const priceAfterDiscount = totalPrice - couponDiscountAmount;
    const tax = Number(((priceAfterDiscount * 18) / 100).toFixed(2));
    const totalAfterDiscount = priceAfterDiscount + tax + 5;

    productSubTotal.innerText = `$${totalPrice.toFixed(2)}`;
    productTax.innerText = `+ $${tax.toFixed(2)}`;
    productTotal.innerText = `$${totalAfterDiscount.toFixed(2)}`;

    const CouponDiv = document.querySelector("#Subtotal");
    const oldCouponDiv = document.querySelector("#couponDiscount");
    if (oldCouponDiv) oldCouponDiv.remove();

    if (couponDiscountAmount > 0) {
      const createDivElem = document.createElement("div");
      createDivElem.setAttribute("id", "couponDiscount");
      createDivElem.innerHTML = `
      <div class="d-flex justify-content-between">
        <p>Discount:</p>
        <p class="product-coupon text-success">- $${couponDiscountAmount.toFixed(
          2
        )} (${coupon.discount_value} OFF)</p>
      </div>
    `;
      CouponDiv.after(createDivElem);
    }
  } catch (error) {
    console.error("Error loading cart data:", error);
  }
}

main();
