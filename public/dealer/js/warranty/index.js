/* global Html5Qrcode */
/**
 *
 * =============================
 * Warranty Page
 * =============================
 *
 **/
$(document).ready(function () {
  const warrantySearch = $("#warranty-search");
  const searchUrl = warrantySearch.data("url");
  const btnOpenCamera = warrantySearch.find("#btn-open-camera");
  const btnStopCamera = warrantySearch.find("#btn-stop-camera");
  const qrReader = warrantySearch.find("#qr-reader");

  let html5QrCode = null;
  let isCameraStarted = false;

  /**
   * =============================
   * Camera helper
   * =============================
   * Desktop: ưu tiên camera trước / webcam.
   * Mobile / tablet: ưu tiên camera sau.
   */
  function isMobileOrTabletDevice() {
    const ua = navigator.userAgent || navigator.vendor || window.opera || "";

    const isMobileOrTabletUA =
      /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(ua);

    // Bắt iPadOS đời mới vì có thể giả dạng Macintosh
    const isIPadOS =
      /Macintosh/i.test(ua) &&
      navigator.maxTouchPoints &&
      navigator.maxTouchPoints > 1;

    return isMobileOrTabletUA || isIPadOS;
  }

  function pickPreferredCameraId(devices) {
    if (!devices || !devices.length) {
      return null;
    }

    const isMobileOrTablet = isMobileOrTabletDevice();

    if (isMobileOrTablet) {
      // Ưu tiên camera sau chính trên Samsung/Android
      // Ví dụ Samsung debug:
      // camera 0, facing back thường là camera sau chính
      const mainBackCamera = devices.find(function (device) {
        const label = String(device.label || "").toLowerCase();

        return label.includes("camera 0") && label.includes("back");
      });

      if (mainBackCamera) {
        return mainBackCamera.id;
      }

      // Camera sau thông thường: Android, Samsung, iPhone, iPad
      const normalBackCamera = devices.find(function (device) {
        const label = String(device.label || "").toLowerCase();

        return (
          label.includes("back camera") ||
          label.includes("camera back") ||
          label.includes("facing back") ||
          label.includes("back") ||
          label.includes("rear") ||
          label.includes("environment") ||
          label.includes("world") ||
          label.includes("sau") ||
          label.includes("mặt sau") ||
          label.includes("phía sau")
        );
      });

      if (normalBackCamera) {
        return normalBackCamera.id;
      }

      // Fallback cuối cùng cho mobile/tablet
      return devices[devices.length - 1].id;
    }

    // Desktop: ưu tiên camera trước / webcam
    const frontCamera = devices.find(function (device) {
      const label = String(device.label || "").toLowerCase();

      return (
        label.includes("front") ||
        label.includes("facing front") ||
        label.includes("user") ||
        label.includes("face") ||
        label.includes("webcam") ||
        label.includes("integrated") ||
        label.includes("trước") ||
        label.includes("phía trước") ||
        label.includes("mặt trước")
      );
    });

    if (frontCamera) {
      return frontCamera.id;
    }

    // Desktop fallback: camera đầu tiên
    return devices[0].id;
  }

  function getPreferredCameraId() {
    if (typeof Html5Qrcode === "undefined") {
      return Promise.reject(new Error("Thư viện Html5Qrcode chưa được tải."));
    }

    return Html5Qrcode.getCameras().then(function (devices) {
      const cameraId = pickPreferredCameraId(devices);

      if (!cameraId) {
        throw new Error("Không tìm thấy camera phù hợp.");
      }

      return cameraId;
    });
  }

  function sendWarrantySearch(qrcode) {
    $.ajax({
      method: "POST",
      dataType: "json",
      url: searchUrl,
      data: {
        qrcode: qrcode,
      },
      success: function (result) {
        if (result.status === 1) {
          warrantySearch.html(result.html);

          // Render QR code
          if (window.QRCode) {
            warrantySearch.find(".warranty-qr-canvas").each(function () {
              new QRCode(this, {
                text: this.dataset.qrcode,
                width: 120,
                height: 120,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.M,
              });
            });
          }
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
  // Option 1: Nhập mã thủ công
  // =====================
  warrantySearch.on("click", ".btn-search", function () {
    const qrcode = warrantySearch.find("#qrcode").val().trim();

    if (!qrcode) {
      dealerApp.showToast("error", "Chưa nhập Mã Qrcode.");
      return;
    }

    sendWarrantySearch(qrcode);
  });

  // =====================
  // Option 2: Quét QR bằng camera
  // =====================
  btnOpenCamera.on("click", function () {
    if (typeof Html5Qrcode === "undefined") {
      dealerApp.showToast("error", "Thư viện quét QR chưa được tải.");
      return;
    }

    qrReader.removeClass("d-none");

    btnOpenCamera.addClass("d-none");
    btnStopCamera.removeClass("d-none");

    html5QrCode = new Html5Qrcode("qr-reader");

    dealerApp.loadingShow();

    const config = {
      fps: 10,
      qrbox: { width: 250, height: 250 },
    };

    getPreferredCameraId()
      .then(function (cameraId) {
        return html5QrCode.start(
          cameraId,
          config,
          function onScanSuccess(decodedText) {
            stopCamera();
            sendWarrantySearch(decodedText);
          },
          function onScanFailure() {
            // Không cần báo lỗi liên tục khi chưa nhận diện được QR
          }
        );
      })
      .then(function () {
        dealerApp.loadingHide();
        isCameraStarted = true;
      })
      .catch(function (err) {
        dealerApp.loadingHide();

        console.error("Lỗi khởi động:", err);

        if (String(err).includes("NotReadableError")) {
          dealerApp.showToast(
            "error",
            "Camera đang bị ứng dụng khác chiếm dụng hoặc lỗi Driver."
          );
        } else {
          dealerApp.showToast(
            "error",
            "Không thể mở camera. Vui lòng kiểm tra quyền."
          );
        }

        stopCamera();
      });
  });

  btnStopCamera.on("click", function () {
    stopCamera();
  });

  function stopCamera() {
    if (html5QrCode && isCameraStarted) {
      html5QrCode
        .stop()
        .catch(function () {})
        .finally(function () {
          try {
            html5QrCode.clear();
          } catch (e) {}

          html5QrCode = null;
          isCameraStarted = false;

          qrReader.addClass("d-none");
          btnOpenCamera.removeClass("d-none");
          btnStopCamera.addClass("d-none");
        });
    } else {
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

  // =====================
  // Certification
  // =====================
  warrantySearch.on("click", ".btn-certification", function () {
    const url = $(this).data("url");
    const orderCode = $(this).data("order-code");
    const qrcode = $(this).data("qrcode");

    $.ajax({
      url: url,
      method: "POST",
      data: {
        orderCode: orderCode,
        qrcode: qrcode,
      },
      success: function (result) {
        if (result.status === 1) {
          sendWarrantySearch(qrcode);
          dealerApp.showToast("success", result.message);
        } else {
          dealerApp.showToast("error", result.message);
        }
      },
      error: function (xhr) {
        dealerApp.showToast("error", "Có lỗi xảy ra. Vui lòng thử lại.");
      },
    });
  });

  // =====================
  // Request warranty
  // =====================
  $(document).on("click", "#requestWarrantyModal .btn-request-warranty", function (e) {
    e.preventDefault();

    const form = $(this).closest("form");

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
          $("#requestWarrantyModal").modal("hide");
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
      error: function () {
        dealerApp.showToast("error", "Có lỗi xảy ra. Vui lòng thử lại.");
      },
    });
  });
});