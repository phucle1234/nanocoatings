/**
 *
 * =============================
 * Checkout Page
 * =============================
 *
 **/
$(document).ready(function () {
  // Checkout handle
  $("#cart-checkout-form .btn-checkout-success").click(function (e) {
    e.preventDefault();
    const form = $("#cart-checkout-form");
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
            console.log(key, error);
          });
          form.find(".is-invalid").first().focus();
        } else {
          dealerApp.showToast("error", result.message);
        }
      },
    });
  });
});
