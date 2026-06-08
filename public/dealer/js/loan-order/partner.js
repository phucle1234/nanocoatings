/**
 *
 * =============================
 * Loan Order Partner Page
 * =============================
 *
 **/
$(document).ready(function () {
  // Handle show list dealer partner by city code
  const citySelect = $("#city_code");
  const dealerPartnerSelect = $("#dealer_partner_code");
  const partnerItemWrapper = $("#partner-item-wrapper");
  const dealerPartnerCodeSelected = citySelect.data("dealer-partner-code-selected");

  function loadDealerPartnersByCityCode(url, cityCode) {
    if (!cityCode) return;
    $.ajax({
      method: "GET",
      dataType: "json",
      url: url,
      data: {
        city_code: cityCode,
      },
      success: function (result) {
        dealerPartnerSelect.empty();
        partnerItemWrapper.empty();
        dealerPartnerSelect.append(`<option value="">Chọn nhà phân phối</option>`);
        if (result.status === 1) {
          $.each(result.data, function (index, dealer) {
            const isSelected = String(dealer.code) === String(dealerPartnerCodeSelected) ? "selected" : "";
            dealerPartnerSelect.append(
              `<option value="${dealer.code}" ${isSelected} data-address="${dealer.address}" data-email="${dealer.email}" data-phone="${dealer.phone}">${dealer.name}</option>`,
            );
          });
          if (dealerPartnerCodeSelected) {
            dealerPartnerSelect.trigger("change");
          }
        }
      },
      error: function (xhr) {
        dealerApp.showToast("error", "Có lỗi xảy ra. Vui lòng thử lại.");
        console.log(xhr.responseText);
      },
    });
  }
  citySelect.on("change", function () {
    const cityCode = $(this).val();
    const url = $(this).data("url");
    loadDealerPartnersByCityCode(url, cityCode);
  });

  dealerPartnerSelect.on("change", function () {
    const dealerPartnerCode = $(this).val();
    const selectedOption = $(this).find(`option[value="${dealerPartnerCode}"]`);
    if (dealerPartnerCode) {
      const name = selectedOption.text() || "";
      const address = selectedOption.data("address") || "";
      const email = selectedOption.data("email") || "";
      const phone = selectedOption.data("phone") || "";
      const partnerItemWrapper = $("#partner-item-wrapper");
      const html = `<div class="partner-item border rounded p-3">
                        <div class="text-red fs-20 font-hanzel">${name}</div>
                        <div class="d-flex align-items-center gap-2 mt-3">
                            <i class="bi bi-geo-alt fs-18"></i>
                            <span class="fw-500">${address}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <i class="bi bi-envelope fs-18"></i>
                            <span class="fw-500">${email}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-2">
                            <i class="bi bi-telephone-inbound fs-18"></i>
                            <span class="fw-500">${phone}</span>
                        </div>
                    </div>`;
      partnerItemWrapper.html(html);
    } else {
      partnerItemWrapper.empty();
    }
  });
  if (citySelect.val()) {
    citySelect.trigger("change");
  }
  const loanOrderContainer = $("#loan-order-container");
  // Load cart
  dealerApp.orderSale.loadCart(loanOrderContainer.data("url"), "checkout", function (result) {
    loanOrderContainer.html(result.html);
    if (result.hasErrorItem > 0) {
      dealerApp.showToast("info", "Giỏ hàng chưa được xử lý những sản phẩm lỗi.");
      window.location.href = result.redirect;
    }
  });

  // Update quantity change
  $(document).on("qty:changed", ".qty-input", function () {
    const qty = parseInt($(this).val()) || 1;
    const url = loanOrderContainer.data("url-update");
    const itemId = $(this).closest("tr").data("item-id");
    dealerApp.orderSale.updateProductInCart(itemId, qty, url, function (result) {
      dealerApp.orderSale.loadCart(loanOrderContainer.data("url"), "checkout", function (result) {
        loanOrderContainer.html(result.html);
        if (result.isEmpty) {
          dealerApp.showToast("info", "Giỏ hàng của bạn đang trống.");
          window.location.href = result.redirect;
        }
      });
    });
  });

  // Delete item in cart
  loanOrderContainer.on("click", ".btn-delete-item", function (event) {
    event.preventDefault();
    const btnDelete = $(event.currentTarget);
    const itemId = parseInt(btnDelete.closest("tr").attr("data-item-id"), 10);
    const url = loanOrderContainer.data("url-delete");
    dealerApp.orderSale.deleteProductInCart(itemId, url, function (result) {
      dealerApp.orderSale.loadCart(loanOrderContainer.data("url"), "checkout", function (result) {
        loanOrderContainer.html(result.html);
        if (result.isEmpty) {
          dealerApp.showToast("info", "Giỏ hàng của bạn đang trống.");
          window.location.href = result.redirect;
        }
      });
    });
  });

  // Submit
  $("#loan-partner-form .btn-partner-success").click(function (e) {
    e.preventDefault();
    const form = $("#loan-partner-form");
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
