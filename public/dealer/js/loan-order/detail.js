/**
 *
 * =============================
 * Product Detail Page
 * =============================
 *
 **/
$(document).ready(function () {
  // Update quantity change
  $(document).on("qty:changed", ".qty-input", function () {
    const qty = parseInt($(this).val()) || 1;
    const price = parseFloat($(this).closest(".quantity").data("price")) || 0;
    const total = qty * price;
    $(this).closest(".product-detail").find(".subtotal-price").text(total.toLocaleString("vi-VN"));
  });
  // Add to cart
  $(document).on("click", ".btn-add-to-cart", function (event) {
    event.preventDefault();
    const btnAddToCart = $(event.currentTarget);
    const buyElement = btnAddToCart.closest(".product-detail");
    const productId = parseInt(buyElement.attr("data-product-id"), 10);
    const quantity = parseInt(buyElement.find(".qty-input").val(), 10) || 1;
    const url = buyElement.data("url");
    dealerApp.orderLoan.addProductToCart(productId, quantity, url);
  });
});
