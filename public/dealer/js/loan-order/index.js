/**
 *
 * =============================
 * Loan Order Page
 * =============================
 *
 **/
$(document).ready(function () {
  const loanOrderContainer = $("#loan-order-container");
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

  // Load cart
  dealerApp.orderLoan.loadCart(loanOrderContainer.data("url"), "index", function (result) {
    loanOrderContainer.html(result.html);
  });

  // Add to cart
  $(document).on("click", ".btn-add-to-cart", function (event) {
    event.preventDefault();
    const btnAddToCart = $(event.currentTarget);
    const buyElement = btnAddToCart.closest("tr");
    const productId = parseInt(buyElement.attr("data-product-id"), 10);
    const quantity = parseInt(buyElement.find(".qty-input").val(), 10) || 1;
    const url = buyElement.data("url");
    dealerApp.orderLoan.addProductToCart(productId, quantity, url, function (result) {
      dealerApp.orderLoan.loadCart(loanOrderContainer.data("url"), "index", function (result) {
        loanOrderContainer.html(result.html);
      });
    });
  });

  // Update quantity change
  $(document).on("qty:changed", ".qty-input", function () {
    const parentCart = $(this).closest("#loan-order-container");
    if (parentCart.length > 0) {
      const qty = parseInt($(this).val()) || 1;
      const url = loanOrderContainer.data("url-update");
      const itemId = $(this).closest("tr").data("item-id");
      dealerApp.orderLoan.updateProductInCart(itemId, qty, url, function (result) {
        dealerApp.orderLoan.loadCart(loanOrderContainer.data("url"), "index", function (result) {
          loanOrderContainer.html(result.html);
        });
      });
    }
  });

  // Delete item in cart
  loanOrderContainer.on("click", ".btn-delete-item", function (event) {
    event.preventDefault();
    const btnDelete = $(event.currentTarget);
    const itemId = parseInt(btnDelete.closest("tr").attr("data-item-id"), 10);
    const url = loanOrderContainer.data("url-delete");
    dealerApp.orderLoan.deleteProductInCart(itemId, url, function (result) {
      dealerApp.orderLoan.loadCart(loanOrderContainer.data("url"), "index", function (result) {
        loanOrderContainer.html(result.html);
      });
    });
  });
});
