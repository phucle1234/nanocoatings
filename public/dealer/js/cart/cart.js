/**
 *
 * =============================
 * Cart Page
 * =============================
 *
 **/
$(document).ready(function () {
  // Search + Pagination
  let searchTimer;
  $("#search-keyword").on("input", function () {
    clearTimeout(searchTimer);
    const keyword = $(this).val().trim();
    if (keyword !== "") {
      $(this).siblings("#clear-search").removeClass("d-none");
    } else {
      $(this).siblings("#clear-search").addClass("d-none");
    }
    searchTimer = setTimeout(function () {
      dealerApp.loadItems({ keyword: keyword }, "product-table-container");
    }, 500);
  });
  dealerApp.bindPaginationLinks("product-table-container");
  $("#clear-search").on("click", function () {
    $("#search-keyword").val("");
    $(this).addClass("d-none");
    dealerApp.loadItems({}, "product-table-container");
  });

  const cartContainer = $("#cart-container");
  // Load cart
  dealerApp.order.loadCart(cartContainer.data("url"), function (result) {
    cartContainer.html(result.html);
  });

  // Add to cart
  $(document).on("click", ".btn-add-to-cart", function (event) {
    event.preventDefault();
    const btnAddToCart = $(event.currentTarget);
    const buyElement = btnAddToCart.closest("tr");
    const productId = parseInt(buyElement.attr("data-product-id"), 10);
    const quantity = parseInt(buyElement.find(".qty-input").val(), 10) || 1;
    const url = buyElement.data("url");
    dealerApp.order.addProductToCart(productId, quantity, url, function (result) {
      if (cartContainer.length > 0) {
        dealerApp.order.loadCart(cartContainer.data("url"), function (result) {
          cartContainer.html(result.html);
        });
      }
    });
  });

  // Update quantity change
  $(document).on("qty:changed", ".qty-input", function () {
    const parentCart = $(this).closest("#cart-container");
    if (parentCart.length > 0) {
      const qty = parseInt($(this).val()) || 1;
      const url = cartContainer.data("url-update");
      const itemId = $(this).closest("tr").data("item-id");
      dealerApp.order.updateProductInCart(itemId, qty, url, function (result) {
        dealerApp.order.loadCart(cartContainer.data("url"), function (result) {
          cartContainer.html(result.html);
        });
      });
    }
  });

  // Delete item in cart
  cartContainer.on("click", ".btn-delete-item", function (event) {
    event.preventDefault();
    const btnDelete = $(event.currentTarget);
    const itemId = parseInt(btnDelete.closest("tr").attr("data-item-id"), 10);
    const url = cartContainer.data("url-delete");
    dealerApp.order.deleteProductInCart(itemId, url, function (result) {
      dealerApp.order.loadCart(cartContainer.data("url"), function (result) {
        cartContainer.html(result.html);
      });
    });
  });
});
