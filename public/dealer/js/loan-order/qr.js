/**
 *
 * =============================
 * Loan Order QR Page
 * =============================
 *
 **/
$(document).ready(function () {
  const loanOrderQr = $("#loan-order-qr");
  const warrantyModal = $("#warrantyModal");
  const btnOpenCamera = warrantyModal.find("#btn-open-camera");
  const btnStopCamera = warrantyModal.find("#btn-stop-camera");
  const qrReader = warrantyModal.find("#qr-reader");
  const modalItemId = warrantyModal.find("#modal_item_id");
  const certificationUrl = warrantyModal.find(".modal-content").data("url");
  let html5QrCode = null;
  let isCameraStarted = false;

  function warrantyQrCode(itemId, qrcode) {
    $.ajax({
      method: "POST",
      dataType: "json",
      url: certificationUrl,
      data: {
        itemId: itemId,
        qrcode: qrcode,
      },
      success: function (result) {
        if (result.status === 1) {
          dealerApp.showToast("success", result.message, "Thông báo", function () {
            location.reload();
          });
        } else {
          dealerApp.showToast("error", result.message);
        }
      },
      error: function (xhr) {
        dealerApp.showToast("error", "Có lỗi xảy ra. Vui lòng thử lại.");
        console.log(xhr.responseText);
      },
    });
  }
  // =====================
  // Mở modal quét mã qr
  // =====================
  loanOrderQr.on("click", ".open-modal", function () {
    const itemId = $(this).data("item-id");

    modalItemId.val(itemId).attr("value", itemId);
    warrantyModal.modal("show");
  });

  // =====================
  // Option 1: Nhập mã thủ công
  // =====================
  warrantyModal.on("click", ".btn-warranty-active", function () {
    const qrcode = warrantyModal.find("#qrcode").val().trim();
    if (!qrcode) {
      dealerApp.showToast("error", "Chưa nhập mã Qrcode.");
      return;
    }
    warrantyQrCode(modalItemId.val(), qrcode);
  });

  btnOpenCamera.on("click", function () {
    qrReader.removeClass("d-none");
    btnOpenCamera.addClass("d-none");
    btnStopCamera.removeClass("d-none");

    html5QrCode = new Html5Qrcode("qr-reader");
    dealerApp.loadingShow();
    const config = {
      fps: 10,
      qrbox: { width: 250, height: 250 },
      // Thêm cấu hình videoConstraints để tăng độ tương thích trên Windows
      videoConstraints: {
        width: { ideal: 640 },
        height: { ideal: 480 },
      },
    };
    html5QrCode
      .start(
        { facingMode: "environment" },
        config,
        function onScanSuccess(decodedText) {
          stopCamera();
          warrantyQrCode(modalItemId.val(), decodedText);
        },
        function onScanFailure() {},
      )
      .then(function () {
        dealerApp.loadingHide();
        isCameraStarted = true;
      })
      .catch(function (err) {
        dealerApp.loadingHide();
        console.error("Lỗi khởi động:", err);

        // Nếu lỗi NotReadableError, thử lại một lần nữa với cấu hình mặc định hoàn toàn
        if (err.includes("NotReadableError")) {
          dealerApp.showToast("error", "Camera đang bị ứng dụng khác chiếm dụng hoặc lỗi Driver.");
        } else {
          dealerApp.showToast("error", "Không thể mở camera. Vui lòng kiểm tra quyền.");
        }
        stopCamera();
      });
  });
  btnStopCamera.on("click", function () {
    stopCamera();
  });

  warrantyModal.on("hidden.bs.modal", function (event) {
    stopCamera();
  });

  function stopCamera() {
    if (html5QrCode && isCameraStarted) {
      // Chỉ gọi stop() khi đã start
      html5QrCode
        .stop()
        .catch(function () {})
        .finally(function () {
          html5QrCode.clear();
          html5QrCode = null;
          isCameraStarted = false;
          qrReader.addClass("d-none");
          btnOpenCamera.removeClass("d-none");
          btnStopCamera.addClass("d-none");
        });
    } else {
      // Chưa start → chỉ cần clear DOM
      if (html5QrCode) {
        try {
          html5QrCode.clear();
        } catch (e) {}
        html5QrCode = null;
      }
      isCameraStarted = false;
      qrReader.addClass("d-none");
      btnOpenCamera.removeClass("d-none");
      btnStopCamera.addClass("d-none");
    }
  }

  // Checkout handle
  $(".btn-checkout-success").click(function (e) {
    e.preventDefault();
    const url = $(this).data("url");
    $.ajax({
      type: "POST",
      dataType: "json",
      url: url,
      data: { success: true },
      success: function (result) {
        if (result.status == 1) {
          dealerApp.showToast("success", result.message);
          setTimeout(function () {
            window.location.href = result.url;
          }, 1000);
        } else {
          dealerApp.showToast("error", result.message);
        }
      },
    });
  });
});
