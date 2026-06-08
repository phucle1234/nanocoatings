/**
 *
 * =============================
 * Sale Cart Page
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
      dealerApp.loadItems({ keyword: keyword }, "product-table-container", { rows: 7, cols: 8 });
    }, 500);
  });
  dealerApp.bindPaginationLinks("product-table-container", { rows: 7, cols: 8 });
  $("#clear-search").on("click", function () {
    $("#search-keyword").val("");
    $(this).addClass("d-none");
    dealerApp.loadItems({}, "product-table-container", { rows: 7, cols: 8 });
  });

  // Handle create new customer
  $(document).on("change", "#check-create-new", function () {
    $(".new-customer-info").toggleClass("d-none", !this.checked);
    $('input[type="checkbox"][name="customer_code"]').prop("checked", false);
  });

  const saleCartContainer = $("#sale-cart-container");
  // Load cart
  dealerApp.orderSale.loadCart(saleCartContainer.data("url"), "checkout", function (result) {
    saleCartContainer.html(result.html);
    if (result.isEmpty) {
      dealerApp.showToast("info", "Giỏ hàng của bạn đang trống.");
      window.location.href = result.redirect;
    } else if (result.hasErrorItem > 0) {
      dealerApp.showToast("info", "Giỏ hàng chưa được xử lý những sản phẩm lỗi.");
      window.location.href = result.redirect;
    }
  });

  // Update quantity change
  $(document).on("qty:changed", ".qty-input", function () {
    const qty = parseInt($(this).val()) || 1;
    const url = saleCartContainer.data("url-update");
    const itemId = $(this).closest("tr").data("item-id");
    dealerApp.orderSale.updateProductInCart(itemId, qty, url, function (result) {
      dealerApp.orderSale.loadCart(saleCartContainer.data("url"), "checkout", function (result) {
        saleCartContainer.html(result.html);
        if (result.isEmpty) {
          dealerApp.showToast("info", "Giỏ hàng của bạn đang trống.");
          window.location.href = result.redirect;
        }
      });
    });
  });

  // Delete item in cart
  saleCartContainer.on("click", ".btn-delete-item", function (event) {
    event.preventDefault();
    const btnDelete = $(event.currentTarget);
    const itemId = parseInt(btnDelete.closest("tr").attr("data-item-id"), 10);
    const url = saleCartContainer.data("url-delete");
    dealerApp.orderSale.deleteProductInCart(itemId, url, function (result) {
      dealerApp.orderSale.loadCart(saleCartContainer.data("url"), "checkout", function (result) {
        saleCartContainer.html(result.html);
        if (result.isEmpty) {
          dealerApp.showToast("info", "Giỏ hàng của bạn đang trống.");
          window.location.href = result.redirect;
        }
      });
    });
  });

  // Checkout handle
  $("#sale-checkout-form .btn-checkout-success").click(function (e) {
    e.preventDefault();
    const form = $("#sale-checkout-form");
    form.find(".form-control").removeClass("is-invalid");
    form.find(".form-select").removeClass("is-invalid");
    form.find(".invalid-feedback").remove();
    $.ajax({
      type: form.attr("method"),
      dataType: "json",
      url: form.attr("action"),
      data: form.serialize(),
      success: function (result) {
        if (result.status == 1) {
          dealerApp.showToast("success", result.message);
          setTimeout(function () {
            window.location.href = result.url;
          }, 1000);
        } else if (result.status == 99) {
          $.each(result.errors, function (key, error) {
            const input = form.find(`[name="${key}"]`);
            input.addClass("is-invalid");
            input.after(`<div class="invalid-feedback">${error[0]}</div>`);
          });
          form.find(".is-invalid").first().focus();
        } else {
          dealerApp.showToast("error", result.message);
        }
      },
    });
  });
});
