/**
 *
 * =============================
 * Product List Page
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

  // Add to cart
  $(document).on("click", ".btn-add-to-cart", function (event) {
    event.preventDefault();
    const btnAddToCart = $(event.currentTarget);
    const buyElement = btnAddToCart.closest("tr");
    const productId = parseInt(buyElement.attr("data-product-id"), 10);
    const quantity = parseInt(buyElement.find(".qty-input").val(), 10) || 1;
    const url = buyElement.data("url");
    dealerApp.order.addProductToCart(productId, quantity, url);
  });
});
