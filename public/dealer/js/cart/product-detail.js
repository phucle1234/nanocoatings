/**
 *
 * =============================
 * Product Page
 * =============================
 *
 **/
$(document).ready(function () {
  // Add to cart
  $(document).on("click", ".btn-add-to-cart", function (event) {
    event.preventDefault();
    const btnAddToCart = $(event.currentTarget);
    const buyElement = btnAddToCart.closest(".product-detail");
    const productId = parseInt(buyElement.attr("data-product-id"), 10);
    const quantity = parseInt(buyElement.find(".qty-input").val(), 10) || 1;
    const url = buyElement.data("url");
    dealerApp.order.addProductToCart(productId, quantity, url);
    if (cartContainer.length > 0) {
      dealerApp.order.loadCart(cartContainer.data("url"), function (result) {
        cartContainer.html(result.html);
      });
    }
  });
});
