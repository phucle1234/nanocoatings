/**
 *
 * =============================
 * Traceability & Contact Pages
 * =============================
 * Handle captcha refresh for all forms
 *
 **/
$(document).ready(function () {
  const traceabilityCheck = $(".traceability-check");
  const traceabilityForm = traceabilityCheck.find("#traceability-form");

  // Traceability Form Submission Handler
  if (traceabilityForm.length) {
    traceabilityForm.on("submit", function (e) {
      e.preventDefault();

      const form = $(this);

      form.find(".form-control").removeClass("is-invalid");
      form.find(".invalid-feedback").remove();

      $.ajax({
        type: form.attr("method"),
        dataType: "json",
        url: form.attr("action"),
        data: form.serialize(),

        success: function (result) {
          if (result.status === 1) {
            traceabilityCheck.html(result.html);

            // Render QR code
            if (window.QRCode) {
              traceabilityCheck.find(".warranty-qr-canvas").each(function () {
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
          } else if (result.status == 99) {
            $.each(result.errors, function (key, error) {
              const input = form.find(`[name="${key}"]`);

              input.addClass("is-invalid");

              if (key == "captcha") {
                input
                  .closest(".wrap-captcha")
                  .after(`<div class="invalid-feedback d-block">${error[0]}</div>`);
              } else {
                input.after(`<div class="invalid-feedback">${error[0]}</div>`);
              }
            });

            form.find(".is-invalid").first().focus();
          } else {
            if (typeof dealerApp !== "undefined" && dealerApp.showToast) {
              dealerApp.showToast("error", result.message);
            }
          }

          if (result.captchaImage) {
            traceabilityCheck.find("#captchaImage").attr("src", result.captchaImage);
          }
        },

        error: function (xhr) {
          if (typeof dealerApp !== "undefined" && dealerApp.showToast) {
            dealerApp.showToast("error", xhr.responseText);
          } else {
            console.error("Traceability submit error:", xhr);
          }
        },
      });
    });
  }

  // Global Captcha Refresh Handler - Works for all forms (Traceability, Contact, etc)
  $(document).on("click", "#refreshCaptcha", function (e) {
    e.preventDefault();

    const refreshLink = $(this);
    const url = refreshLink.data("url");
    const captchaImageElement = refreshLink.closest("form").find("#captchaImage");

    $.ajax({
      type: "GET",
      url: url,
      dataType: "json",

      success: function (result) {
        if (captchaImageElement.length) {
          captchaImageElement.attr("src", result.captchaImage);
        }
      },

      error: function (xhr) {
        if (typeof dealerApp !== "undefined" && dealerApp.showToast) {
          dealerApp.showToast("error", "Error refreshing captcha");
        } else {
          console.error("Error refreshing captcha:", xhr);
        }
      },
    });
  });
});

/* global Html5Qrcode */

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
    // Mobile / tablet: ưu tiên camera sau chính của Samsung/Android
    // Theo debug của bạn:
    // 2. camera 2, facing back
    // 3. camera 0, facing back
    // => nên ưu tiên camera 0, facing back
    const mainBackCamera = devices.find(function (device) {
      const label = String(device.label || "").toLowerCase();

      return (
        label.includes("camera 0") &&
        label.includes("back")
      );
    });

    if (mainBackCamera) {
      return mainBackCamera.id;
    }

    // Nếu không có camera 0, chọn camera nào có facing back
    const facingBackCamera = devices.find(function (device) {
      const label = String(device.label || "").toLowerCase();

      return (
        label.includes("facing back") ||
        label.includes("back") ||
        label.includes("rear") ||
        label.includes("environment") ||
        label.includes("world") ||
        label.includes("sau") ||
        label.includes("phía sau") ||
        label.includes("mặt sau")
      );
    });

    if (facingBackCamera) {
      return facingBackCamera.id;
    }

    // Fallback cuối cùng: lấy camera cuối
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

  return devices[0].id;
}

function showCameraDebugOnScreen(devices, selectedCameraId) {
  let box = document.getElementById("camera-debug-box");

  if (!box) {
    box = document.createElement("div");
    box.id = "camera-debug-box";
    box.style.position = "fixed";
    box.style.left = "10px";
    box.style.right = "10px";
    box.style.bottom = "10px";
    box.style.zIndex = "99999";
    box.style.background = "rgba(0,0,0,0.85)";
    box.style.color = "#fff";
    box.style.fontSize = "12px";
    box.style.padding = "10px";
    box.style.borderRadius = "8px";
    box.style.maxHeight = "180px";
    box.style.overflowY = "auto";
    box.style.lineHeight = "1.4";
    document.body.appendChild(box);
  }

  let html = `<strong>Camera đang chọn:</strong><br>${selectedCameraId}<hr style="border-color:#555;">`;

  devices.forEach(function (device, index) {
    const isSelected = device.id === selectedCameraId;

    html += `
      <div style="margin-bottom:6px; ${isSelected ? "color:#00ff99;font-weight:bold;" : ""}">
        ${index}. ${device.label || "Không có tên camera"}<br>
        <small>${device.id}</small>
      </div>
    `;
  });

  html += `
    <button 
      type="button" 
      onclick="document.getElementById('camera-debug-box').remove()" 
      style="margin-top:6px;padding:4px 8px;border:0;border-radius:4px;"
    >
      Đóng
    </button>
  `;

  box.innerHTML = html;
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
// Hiện trực tiếp trên màn hình điện thoại, không cần console
    //showCameraDebugOnScreen(devices, cameraId);
    return cameraId;
  });
}

/**
 * Traceability QR scan
 */
$(document).ready(function () {
  const traceForm = $("#traceability-form");

  if (!traceForm.length) {
    return;
  }

  const traceInput = traceForm.find("#trace_code");
  const captchaInput = traceForm.find("#captcha");
  const btnOpenCamera = $("#btn-trace-open-camera");
  const btnStopCamera = $("#btn-trace-stop-camera");
  const qrReader = $("#trace-qr-reader");
  const qrPlaceholder = $("#trace-qr-scan-placeholder");

  let html5QrCode = null;
  let isCameraStarted = false;
  let isProcessingScan = false;

  function showTraceMessage(type, message) {
    if (window.dealerApp && typeof dealerApp.showToast === "function") {
      dealerApp.showToast(type, message);
      return;
    }

    if (window.app && typeof app.showToast === "function") {
      app.showToast(type, message);
      return;
    }

    alert(message);
  }

  function extractTraceCode(decodedText) {
    const rawValue = String(decodedText || "").trim();

    if (!rawValue) {
      return "";
    }

    try {
      const url = new URL(rawValue);

      return (
        url.searchParams.get("trace_code") ||
        url.searchParams.get("qr") ||
        url.searchParams.get("qrcode") ||
        url.pathname.split("/").filter(Boolean).pop() ||
        rawValue
      );
    } catch (e) {
      return rawValue;
    }
  }

  function setCameraUi(isOpen) {
    if (isOpen) {
      qrReader.removeClass("d-none");
      qrPlaceholder.addClass("d-none");
      btnOpenCamera.addClass("d-none");
      btnStopCamera.removeClass("d-none");
    } else {
      qrReader.addClass("d-none");
      qrPlaceholder.removeClass("d-none");
      btnOpenCamera.removeClass("d-none");
      btnStopCamera.addClass("d-none");
    }
  }

  function stopCamera(done) {
    const finish = function () {
      if (html5QrCode) {
        try {
          html5QrCode.clear();
        } catch (e) {
          console.warn("Không thể clear QR reader:", e);
        }
      }

      html5QrCode = null;
      isCameraStarted = false;
      isProcessingScan = false;
      setCameraUi(false);

      if (typeof done === "function") {
        done();
      }
    };

    if (html5QrCode && isCameraStarted) {
      html5QrCode
        .stop()
        .catch(function (e) {
          console.warn("Không thể stop camera:", e);
        })
        .then(finish);
    } else {
      finish();
    }
  }

  btnOpenCamera.on("click", function () {
    if (typeof Html5Qrcode === "undefined") {
      showTraceMessage("error", "Thư viện quét QR chưa được tải.");
      return;
    }

    setCameraUi(true);
    isProcessingScan = false;
    html5QrCode = new Html5Qrcode("trace-qr-reader");

    const config = {
      fps: 10,
      qrbox: { width: 250, height: 250 },
    };

    getPreferredCameraId()
      .then(function (cameraId) {
        console.log("Camera đang dùng để quét lần đầu:", cameraId);

        return html5QrCode.start(
          cameraId,
          config,

          function onScanSuccess(decodedText) {
            if (isProcessingScan) {
              return;
            }

            isProcessingScan = true;

            const traceCode = extractTraceCode(decodedText);

            stopCamera(function () {
              if (!traceCode) {
                showTraceMessage("error", "Không đọc được mã QR.");
                return;
              }

              traceInput.val(traceCode);

              if (captchaInput.length && !captchaInput.val().trim()) {
                showTraceMessage(
                  "success",
                  "Đã quét mã. Vui lòng nhập captcha rồi bấm kiểm tra."
                );
                captchaInput.focus();
                return;
              }

              traceForm.trigger("submit");
            });
          },

          function onScanFailure() {
            // Không cần báo lỗi liên tục khi chưa nhận diện được QR
          }
        );
      })
      .then(function () {
        isCameraStarted = true;
      })
      .catch(function (err) {
        console.error("Lỗi khởi động camera:", err);

        if (String(err).includes("NotReadableError")) {
          showTraceMessage(
            "error",
            "Camera đang bị ứng dụng khác chiếm dụng hoặc lỗi driver."
          );
        } else {
          showTraceMessage(
            "error",
            "Không thể mở camera. Vui lòng kiểm tra quyền truy cập camera."
          );
        }

        stopCamera();
      });
  });

  btnStopCamera.on("click", function () {
    stopCamera();
  });
});

/* global Html5Qrcode */

$(document).ready(function () {
  let scanAgainQrCode = null;
  let isScanAgainStarted = false;
  let isScanAgainProcessing = false;
  let traceReturnUrl = null;

  function showTraceMessage(type, message) {
    if (window.dealerApp && typeof dealerApp.showToast === "function") {
      dealerApp.showToast(type, message);
      return;
    }

    if (window.app && typeof app.showToast === "function") {
      app.showToast(type, message);
      return;
    }

    alert(message);
  }

  function extractTraceCode(decodedText) {
    const rawValue = String(decodedText || "").trim();

    if (!rawValue) {
      return "";
    }

    try {
      const url = new URL(rawValue);

      return (
        url.searchParams.get("trace_code") ||
        url.searchParams.get("qr") ||
        url.searchParams.get("qrcode") ||
        url.pathname.split("/").filter(Boolean).pop() ||
        rawValue
      );
    } catch (e) {
      return rawValue;
    }
  }

  function stopScanAgain(done) {
    const finish = function () {
      if (scanAgainQrCode) {
        try {
          scanAgainQrCode.clear();
        } catch (e) {
          console.warn("Không thể clear scan again reader:", e);
        }
      }

      scanAgainQrCode = null;
      isScanAgainStarted = false;
      isScanAgainProcessing = false;

      if (typeof done === "function") {
        done();
      }
    };

    if (scanAgainQrCode && isScanAgainStarted) {
      scanAgainQrCode
        .stop()
        .catch(function (e) {
          console.warn("Không thể stop scan again camera:", e);
        })
        .then(finish);
    } else {
      finish();
    }
  }

  $(document).on("click", ".btn-trace-scan-again", function () {
    if (typeof Html5Qrcode === "undefined") {
      showTraceMessage("error", "Thư viện quét QR chưa được tải.");
      return;
    }

    $(".hidden-again").addClass("d-none");

    // Ẩn nút quét mã bên dưới khi giao diện quét mã mới đang hiển thị
    $(".btn-trace-scan-again").addClass("d-none");

    traceReturnUrl =
      $(this).data("trace-url") || window.location.origin + "/traceability";

    const modalEl = document.getElementById("traceScanAgainModal");
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    modal.show();

    setTimeout(function () {
      scanAgainQrCode = new Html5Qrcode("trace-scan-again-reader");
      isScanAgainProcessing = false;

      getPreferredCameraId()
        .then(function (cameraId) {
          console.log("Camera đang dùng để quét lại:", cameraId);

          return scanAgainQrCode.start(
            cameraId,

            {
              fps: 10,
              qrbox: { width: 250, height: 250 },
            },

            function onScanSuccess(decodedText) {
              if (isScanAgainProcessing) {
                return;
              }

              isScanAgainProcessing = true;

              const traceCode = extractTraceCode(decodedText);

              if (!traceCode) {
                showTraceMessage("error", "Không đọc được mã QR.");
                isScanAgainProcessing = false;
                return;
              }

              stopScanAgain(function () {
                modal.hide();

                const redirectUrl = new URL(traceReturnUrl, window.location.origin);

                redirectUrl.searchParams.set("trace_code", traceCode);
                redirectUrl.searchParams.set("from_scan", "1");

                window.location.href = redirectUrl.toString();
              });
            },

            function onScanFailure() {
              // Không cần báo lỗi liên tục khi chưa nhận diện được QR
            }
          );
        })
        .then(function () {
          isScanAgainStarted = true;
        })
        .catch(function (err) {
          console.error("Lỗi mở camera:", err);

          if (String(err).includes("NotReadableError")) {
            showTraceMessage(
              "error",
              "Camera đang bị ứng dụng khác chiếm dụng hoặc lỗi driver."
            );
          } else {
            showTraceMessage(
              "error",
              "Không thể mở camera. Vui lòng kiểm tra quyền truy cập camera."
            );
          }

          stopScanAgain(function () {
            modal.hide();
            $(".btn-trace-scan-again").removeClass("d-none");
          });
        });
    }, 300);
  });

  $(document).on("click", ".btn-trace-stop-scan-again", function () {
    stopScanAgain(function () {
      $(".btn-trace-scan-again").removeClass("d-none");
    });
  });

  if ($("#traceability-form").length) {
    const urlParams = new URLSearchParams(window.location.search);
    const fromScan = urlParams.get("from_scan");
    const traceCode = urlParams.get("trace_code") || urlParams.get("qr");

    if (fromScan === "1" && traceCode) {
      $("#trace_code").val(traceCode);

      if ($("#captcha").length) {
        $("#captcha").focus();
      }
    }
  }
});