/**
 *
 * =============================
 * Order Diary Page
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
      dealerApp.loadItems({ keyword: keyword }, "order-diary-table-container");
    }, 500);
  });
  dealerApp.bindPaginationLinks("order-diary-table-container");
  $("#clear-search").on("click", function () {
    $("#search-keyword").val("");
    $(this).addClass("d-none");
    dealerApp.loadItems({}, "order-diary-table-container");
  });
});
