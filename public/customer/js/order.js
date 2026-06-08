$(document).ready(function () {
  // Search + Pagination
  let searchTimer;
  
  $("#customer-search-keyword").on("input", function () {
    clearTimeout(searchTimer);
    const keyword = $(this).val().trim();

    // Hiện/ẩn nút clear
    if (keyword !== "") {
      $(this).siblings("#customer-clear-search").removeClass("d-none");
    } else {
      $(this).siblings("#customer-clear-search").addClass("d-none");
    }
    
    // Debounce 500ms
    searchTimer = setTimeout(function () {
      customerApp.loadOrders({ keyword: keyword }, "order-history-table-container");
    }, 500);
  });
  
  // Bind pagination links
  customerApp.bindPaginationLinks("order-history-table-container");
  
  // Clear search
  $("#customer-clear-search").on("click", function () {
    $("#customer-search-keyword").val("");
    $(this).addClass("d-none");
    customerApp.loadOrders({}, "order-history-table-container");
  });
});

// Tạo object customerApp (thêm vào public/customer/js/customer.js)
window.customerApp = {
  loadOrders: function (params = {}, displayId) {
    const urlParams = new URLSearchParams();
    for (const key in params) {
      if (params[key] != null && params[key] !== "") 
        urlParams.set(key, params[key]);
    }
    const queryString = urlParams.toString();
    history.pushState({}, "", queryString ? "?" + queryString : location.pathname);

    // Hiện loading shimmer
    const shimmerRow = `<div class="order-item bg-light rounded-3 p-3 mt-3">
      <div class="skeleton-line" style="height: 80px;"></div>
    </div>`;
    $(`#${displayId}`).html(shimmerRow.repeat(3));

    $.ajax({
      url: $(`#${displayId}`).data("url"),
      data: params,
      headers: { "X-Requested-With": "XMLHttpRequest" },
      success: function (html) {
        $(`#${displayId}`).html(html);
        customerApp.bindPaginationLinks(displayId);
      },
      error: function () {
        $(`#${displayId}`).html('<div class="alert alert-danger">Có lỗi xảy ra. Vui lòng thử lại.</div>');
      }
    });
  },
  
  bindPaginationLinks: function (displayId) {
    $(`#${displayId}`)
      .find(".pagination a")
      .on("click", function (e) {
        e.preventDefault();
        const url = new URL($(this).attr("href"));
        const params = Object.fromEntries(url.searchParams.entries());
        $("html, body").animate(
          {
            scrollTop: $(`#${displayId}`).offset().top - 140,
          },
          200,
        );
        customerApp.loadOrders(params, displayId);
      });
  }
};