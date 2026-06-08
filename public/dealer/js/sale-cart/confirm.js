/**
 *
 * =============================
 * Ecommerce Detail Page
 * =============================
 *
 **/
$(document).ready(function () {
  const confirmSale = $("#confirm-sale");
  const warrantyModal = $("#warrantyModal");
  const btnOpenCamera = warrantyModal.find("#btn-open-camera");
  const btnStopCamera = warrantyModal.find("#btn-stop-camera");
  const qrReader = warrantyModal.find("#qr-reader");
  const modalOrderCode = warrantyModal.find("#modal_order_code");
  const modalItemId = warrantyModal.find("#modal_item_id");
  const certificationUrl = warrantyModal.find(".modal-content").data("url");
  let html5QrCode = null;
  let isCameraStarted = false;

  function warrantyQrCode(orderCode, itemId, qrcode) {
    $.ajax({
      method: "POST",
      dataType: "json",
      url: certificationUrl,
      data: {
        orderCode: orderCode,
        itemId: itemId,
        qrcode: qrcode,
      },
      success: function (result) {
        if (result.status === 1) {
          dealerApp.showToast("success", result.message, "Thông báo", function () {
            location.reload();
          });
          // confirmSale.find("#warranty-" + itemId).html(`
          //    <div class="d-flex align-items-center justify-content-end gap-2">
          //       <span><i class="bi bi-check2 fs-20 text-success"></i></span>
          //       <span class="fs-14 text-secondary">${qrcode}</span>
          //   </div>
          //   `);
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
  // Mở modal kích hoạt bảo hành
  // =====================
  confirmSale.on("click", ".open-modal", function () {
    const orderCode = $(this).data("order-code");
    const itemId = $(this).data("item-id");

    modalOrderCode.val(orderCode).attr("value", orderCode);
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
    warrantyQrCode(modalOrderCode.val(), modalItemId.val(), qrcode);
  });

  // =====================
  // Option 2: Quét QR bằng camera
  // =====================
  // btnOpenCamera.on("click", function () {
  //   qrReader.removeClass("d-none");
  //   btnOpenCamera.addClass("d-none");
  //   btnStopCamera.removeClass("d-none");

  //   html5QrCode = new Html5Qrcode("qr-reader");

  //   Html5Qrcode.getCameras()
  //     .then(function (devices) {
  //       const cameraId = devices[devices.length - 1].id;

  //       html5QrCode
  //         .start(
  //           cameraId,
  //           { fps: 10, qrbox: { width: 200, height: 200 } },
  //           function onScanSuccess(decodedText) {
  //             warrantyQrCode(modalOrderCode.val(), modalItemId.val(), decodedText);
  //             stopCamera();
  //           },
  //           function onScanFailure() {},
  //         )
  //         .then(function () {
  //           isCameraStarted = true;
  //         })
  //         .catch(function (err) {
  //           console.error("Không thể khởi động camera:", err);
  //           dealerApp.showToast("error", "Không thể mở camera. Vui lòng kiểm tra quyền truy cập.");
  //           stopCamera();
  //         });
  //     })
  //     .catch(function (err) {
  //       console.error(err.code);
  //       dealerApp.showToast("error", "Không thể truy cập camera");
  //       stopCamera();
  //     });
  // });
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
          warrantyQrCode(modalOrderCode.val(), modalItemId.val(), decodedText);
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
    // Html5Qrcode.getCameras()
    //   .then(function (devices) {
    //     const cameraId = devices[devices.length - 1].id;

    //     html5QrCode
    //       .start(
    //         cameraId,
    //         { fps: 10, qrbox: { width: 200, height: 200 } },
    //         function onScanSuccess(decodedText) {
    //           warrantyQrCode(modalOrderCode.val(), modalItemId.val(), decodedText);
    //           stopCamera();
    //         },
    //         function onScanFailure() {},
    //       )
    //       .then(function () {
    //         isCameraStarted = true;
    //       })
    //       .catch(function (err) {
    //         console.error("Không thể khởi động camera:", err);
    //         dealerApp.showToast("error", "Không thể mở camera. Vui lòng kiểm tra quyền truy cập.");
    //         stopCamera();
    //       });
    //   })
    //   .catch(function (err) {
    //     console.error(err.code);
    //     dealerApp.showToast("error", "Không thể truy cập camera");
    //     stopCamera();
    //   });
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
});
