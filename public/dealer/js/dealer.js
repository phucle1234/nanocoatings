window.dealerApp = {
  showToast: function (type, message = "Nội dung thông báo", title = "Thông báo", callbackSuccess) {
    const id = "toast-" + Date.now();
    const bgClass = type === "success" ? "bg-success" : "bg-danger";
    const toastHtml = $(`
    <div id="${id}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header ${bgClass} text-white border-0">
        <strong class="me-auto">${title}</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body bg-white border-0">${message}</div>
    </div>
  `);

    if (!$("#toast-container").length) {
      $("body").append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999"></div>');
    }

    $("#toast-container").append(toastHtml);

    const toast = new bootstrap.Toast(toastHtml[0], { autohide: true, delay: 3000 });
    toast.show();

    toastHtml[0].addEventListener("hidden.bs.toast", function () {
      toastHtml.remove();
      if (typeof callbackSuccess === "function") callbackSuccess();
    });
  },
  loadItems: function (params = {}, displayId, shimmer = { rows: 7, cols: 5 }) {
    const urlParams = new URLSearchParams();
    for (const key in params) {
      if (params[key] != null && params[key] !== "") urlParams.set(key, params[key]);
    }
    const queryString = urlParams.toString();
    history.pushState({}, "", queryString ? "?" + queryString : location.pathname);

    const shimmerRow = `<tr>
            ${Array(shimmer.cols).fill('<td><span class="loading-shimmer"></span></td>').join("")}
        </tr>`;
    $(`#${displayId}`).find("tbody").html(shimmerRow.repeat(shimmer.rows));

    $.ajax({
      url: $(`#${displayId}`).data("url"),
      data: params,
      headers: { "X-Requested-With": "XMLHttpRequest" },
      success: function (html) {
        $(`#${displayId}`).html(html);
        dealerApp.bindPaginationLinks(displayId);
      },
    });
  },
  bindPaginationLinks: function (displayId, shimmer = { rows: 7, cols: 5 }) {
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
        dealerApp.loadItems(params, displayId, shimmer);
      });
  },
  loadingShow: function () {
    if (!$("#preloader").length) {
      $("body").append('<div id="preloader"><div class="loader"></div></div>');
    }
  },
  loadingHide: function () {
    $("body").find("#preloader").remove();
  },
  initQty: function () {
    $(document).on("click", ".quantity .qty-minus", function () {
      const qtyInput = $(this).siblings(".qty-input");
      let currentValue = parseInt(qtyInput.val()) || 1;
      let minValue = parseInt(qtyInput.attr("min")) || 1;
      if (currentValue > minValue) {
        qtyInput.val(currentValue - 1).trigger("qty:changed");
      }
    });

    $(document).on("click", ".quantity .qty-plus", function () {
      const qtyInput = $(this).siblings(".qty-input");
      let currentValue = parseInt(qtyInput.val()) || 1;
      let maxValue = parseInt(qtyInput.attr("max")) || 999;
      if (currentValue < maxValue) {
        qtyInput.val(currentValue + 1).trigger("qty:changed");
      }
    });

    $(document).on("input", ".quantity .qty-input", function () {
      let value = parseInt($(this).val());
      let minValue = parseInt($(this).attr("min")) || 1;
      let maxValue = parseInt($(this).attr("max")) || 999;

      if (isNaN(value) || value < minValue) value = minValue;
      if (value > maxValue) value = maxValue;

      $(this).val(value).trigger("qty:changed");
    });
  },
  order: {
    loadCart: function (url, callbackSuccess) {
      // type: index or checkout
      $.ajax({
        type: "POST",
        dataType: "json",
        url: url,
        data: {},
        success: function (result) {
          if (result.status == 1) {
            if (typeof callbackSuccess === "function") callbackSuccess(result);
          } else {
            dealerApp.showToast("error", result.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          dealerApp.showToast("error", "Đã có lỗi xảy ra. Vui lòng thử lại.");
        },
      });
    },
    addProductToCart: function (productId, quantity, url, callbackSuccess) {
      $.ajax({
        type: "POST",
        dataType: "json",
        url: url,
        data: { productId: productId, quantity: quantity },
        success: function (result) {
          if (result.status == 1) {
            if (typeof callbackSuccess === "function") callbackSuccess(result);
            dealerApp.showToast("success", result.message);
          } else {
            dealerApp.showToast("error", result.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          dealerApp.showToast("error", "Đã có lỗi xảy ra. Vui lòng thử lại.");
        },
      });
    },
    updateProductInCart: function (itemId, quantity, url, callbackSuccess) {
      $.ajax({
        type: "POST",
        dataType: "json",
        url: url,
        data: { itemId: itemId, quantity: quantity },
        success: function (result) {
          if (result.status == 1) {
            if (typeof callbackSuccess === "function") callbackSuccess(result);
            dealerApp.showToast("success", result.message);
          } else {
            dealerApp.showToast("error", result.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          dealerApp.showToast("error", "Đã có lỗi xảy ra. Vui lòng thử lại.");
        },
      });
    },
    deleteProductInCart: function (itemId, url, callbackSuccess) {
      $.ajax({
        type: "POST",
        dataType: "json",
        url: url,
        data: { itemId: itemId },
        success: function (result) {
          if (result.status == 1) {
            if (typeof callbackSuccess === "function") callbackSuccess(result);
            dealerApp.showToast("success", result.message);
          } else {
            dealerApp.showToast("error", result.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          dealerApp.showToast("error", "Đã có lỗi xảy ra. Vui lòng thử lại.");
        },
      });
    },
  },
  orderSale: {
    loadCart: function (url, type, callbackSuccess) {
      // type: index or checkout
      $.ajax({
        type: "POST",
        dataType: "json",
        url: url,
        data: { type: type },
        success: function (result) {
          if (result.status == 1) {
            if (typeof callbackSuccess === "function") callbackSuccess(result);
          } else {
            dealerApp.showToast("error", result.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          dealerApp.showToast("error", "Đã có lỗi xảy ra. Vui lòng thử lại.");
        },
      });
    },
    addProductToCart: function (productId, quantity, qrcode, url, callbackSuccess) {
      $.ajax({
        type: "POST",
        dataType: "json",
        url: url,
        data: { productId: productId, quantity: quantity, qrcode: qrcode },
        success: function (result) {
          if (result.status == 1) {
            if (typeof callbackSuccess === "function") callbackSuccess(result);
            dealerApp.showToast("success", result.message);
          } else {
            dealerApp.showToast("error", result.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          dealerApp.showToast("error", "Đã có lỗi xảy ra. Vui lòng thử lại.");
        },
      });
    },
    updateProductInCart: function (itemId, quantity, url, callbackSuccess) {
      $.ajax({
        type: "POST",
        dataType: "json",
        url: url,
        data: { itemId: itemId, quantity: quantity },
        success: function (result) {
          if (result.status == 1) {
            if (typeof callbackSuccess === "function") callbackSuccess(result);
            dealerApp.showToast("success", result.message);
          } else {
            dealerApp.showToast("error", result.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          dealerApp.showToast("error", "Đã có lỗi xảy ra. Vui lòng thử lại.");
        },
      });
    },
    deleteProductInCart: function (itemId, url, callbackSuccess) {
      $.ajax({
        type: "POST",
        dataType: "json",
        url: url,
        data: { itemId: itemId },
        success: function (result) {
          if (result.status == 1) {
            if (typeof callbackSuccess === "function") callbackSuccess(result);
            dealerApp.showToast("success", result.message);
          } else {
            dealerApp.showToast("error", result.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          dealerApp.showToast("error", "Đã có lỗi xảy ra. Vui lòng thử lại.");
        },
      });
    },
  },
  orderLoan: {
    loadCart: function (url, type, callbackSuccess) {
      // type: index or checkout
      $.ajax({
        type: "POST",
        dataType: "json",
        url: url,
        data: { type: type },
        success: function (result) {
          if (result.status == 1) {
            if (typeof callbackSuccess === "function") callbackSuccess(result);
          } else {
            dealerApp.showToast("error", result.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          dealerApp.showToast("error", "Đã có lỗi xảy ra. Vui lòng thử lại.");
        },
      });
    },
    addProductToCart: function (productId, quantity, url, callbackSuccess) {
      $.ajax({
        type: "POST",
        dataType: "json",
        url: url,
        data: { productId: productId, quantity: quantity },
        success: function (result) {
          if (result.status == 1) {
            if (typeof callbackSuccess === "function") callbackSuccess(result);
            dealerApp.showToast("success", result.message);
          } else {
            dealerApp.showToast("error", result.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          dealerApp.showToast("error", "Đã có lỗi xảy ra. Vui lòng thử lại.");
        },
      });
    },
    updateProductInCart: function (itemId, quantity, url, callbackSuccess) {
      $.ajax({
        type: "POST",
        dataType: "json",
        url: url,
        data: { itemId: itemId, quantity: quantity },
        success: function (result) {
          if (result.status == 1) {
            if (typeof callbackSuccess === "function") callbackSuccess(result);
            dealerApp.showToast("success", result.message);
          } else {
            dealerApp.showToast("error", result.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          dealerApp.showToast("error", "Đã có lỗi xảy ra. Vui lòng thử lại.");
        },
      });
    },
    deleteProductInCart: function (itemId, url, callbackSuccess) {
      $.ajax({
        type: "POST",
        dataType: "json",
        url: url,
        data: { itemId: itemId },
        success: function (result) {
          if (result.status == 1) {
            if (typeof callbackSuccess === "function") callbackSuccess(result);
            dealerApp.showToast("success", result.message);
          } else {
            dealerApp.showToast("error", result.message);
          }
        },
        error: function (xhr, status, error) {
          console.error("AJAX Error:", status, error);
          dealerApp.showToast("error", "Đã có lỗi xảy ra. Vui lòng thử lại.");
        },
      });
    },
  },
};

$(document).ready(function () {
  dealerApp.initQty();
  $.ajaxSetup({
    headers: {
      "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
    },
    beforeSend: function () {
      dealerApp.loadingShow();
    },
    complete: function () {
      dealerApp.loadingHide();
    },
  });

  /**
   *
   * Sidebar handle
   *
   */
  const sidebar = $(".sidebar-list");
  const submenus = sidebar.find("> ul > li > ul");
  // Ẩn tất cả submenu ban đầu
  submenus.hide();
  sidebar.find(".bi-caret-down").removeClass("rotate-180");
  // Định nghĩa hàm kiểm tra Desktop
  const isDesktop = () => $(window).width() >= 1200;

  function initSidebarState() {
    if (isDesktop()) {
      // Trên Desktop: Chỉ mở submenu của mục đang active
      sidebar.find("> ul > li.active-parent").each(function () {
        const submenu = $(this).find("> ul");
        if (submenu.length > 0) {
          submenu.show();
          $(this).find("> a .bi-caret-down").addClass("rotate-180");
        }
      });
    } else {
      // Trên Mobile: Luôn ẩn hết submenu theo thiết kế flex-space-between
      submenus.hide();
      sidebar.find(".bi-caret-down").removeClass("rotate-180");
    }
  }

  // Khởi tạo lần đầu khi load trang
  initSidebarState();

  // Lắng nghe sự kiện Resize hoặc Xoay màn hình
  let resizeTimer;
  $(window).on("resize", function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function () {
      initSidebarState();
    }, 250); // Debounce để tối ưu hiệu năng
  });
  // Click vào parent link có submenu
  sidebar.find("> ul > li > a:has(.bi-caret-down)").on("click", function (e) {
    e.preventDefault();

    const submenu = $(this).next("ul");
    if (submenu.length === 0) return;

    const isOpen = submenu.is(":visible");

    // Đóng tất cả submenu khác
    submenus.slideUp(200);
    sidebar.find(".bi-caret-down").removeClass("rotate-180");

    // Toggle submenu hiện tại
    if (!isOpen) {
      submenu.slideDown(200);
      $(this).find(".bi-caret-down").addClass("rotate-180");
    }
  });
});
