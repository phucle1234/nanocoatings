/**
 *
 * =============================
 * Sale Cart Page
 * =============================
 *
 **/
$(document).ready(function () {
  const saleCartContainer = $("#sale-cart-container");
  const getProductByQRCode = $("#get-product-by-qrcode");
  const btnOpenCamera = getProductByQRCode.find("#btn-open-camera");
  const btnStopCamera = getProductByQRCode.find("#btn-stop-camera");
  const qrReader = getProductByQRCode.find("#qr-reader");
  const getProductInfoUrl = getProductByQRCode.data("url");
  const addToCartUrl = getProductByQRCode.data("add-cart");
  let html5QrCode = null;
  let isCameraStarted = false;

  // =====================
  // Option 1: Quét QR bằng camera
  // =====================

  function getInfoProductQr(qrcode) {
    $.ajax({
      method: "POST",
      dataType: "json",
      url: getProductInfoUrl,
      data: {
        qrcode: qrcode,
      },
      success: function (result) {
        if (result.status === 1) {
          dealerApp.orderSale.addProductToCart(result.productId, 1, qrcode, addToCartUrl, function (result) {
            dealerApp.orderSale.loadCart(saleCartContainer.data("url"), "index", function (callback) {
              saleCartContainer.html(callback.html);
            });
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

  // btnOpenCamera.on("click", function () {
  //   qrReader.removeClass("d-none");
  //   btnOpenCamera.addClass("d-none");
  //   btnStopCamera.removeClass("d-none");

  //   html5QrCode = new Html5Qrcode("qr-reader");
  //   dealerApp.loadingShow();
  //   Html5Qrcode.getCameras()
  //     .then(function (devices) {
  //       const cameraId = devices[devices.length - 1].id;

  //       html5QrCode
  //         .start(
  //           cameraId,
  //           { fps: 10, qrbox: { width: 250, height: 250 } },
  //           function onScanSuccess(decodedText) {
  //             stopCamera();
  //             getInfoProductQr(decodedText);
  //           },
  //           function onScanFailure() {},
  //         )
  //         .then(function () {
  //           dealerApp.loadingHide();
  //           isCameraStarted = true;
  //         })
  //         .catch(function (err) {
  //           dealerApp.loadingHide();
  //           console.error("Không thể khởi động camera:", err);
  //           dealerApp.showToast("error", "Không thể mở camera. Vui lòng kiểm tra quyền truy cập.");
  //           stopCamera();
  //         });
  //     })
  //     .catch(function (err) {
  //       dealerApp.loadingHide();
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
          getInfoProductQr(decodedText);
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
  // =====================
  // Option 2: Tìm bằng từ khoá
  // =====================
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
      dealerApp.loadItems({ keyword: keyword }, "product-table-container");
    }, 500);
  });
  dealerApp.bindPaginationLinks("product-table-container");
  $("#clear-search").on("click", function () {
    $("#search-keyword").val("");
    $(this).addClass("d-none");
    dealerApp.loadItems({}, "product-table-container");
  });
  // Load cart
  dealerApp.orderSale.loadCart(saleCartContainer.data("url"), "index", function (result) {
    saleCartContainer.html(result.html);
  });

  // Add to cart
  $(document).on("click", ".btn-add-to-cart", function (event) {
    event.preventDefault();
    const btnAddToCart = $(event.currentTarget);
    const buyElement = btnAddToCart.closest("tr");
    const productId = parseInt(buyElement.attr("data-product-id"), 10);
    const quantity = parseInt(buyElement.find(".qty-input").val(), 10) || 1;
    const qrcode = null;
    dealerApp.orderSale.addProductToCart(productId, quantity, qrcode, addToCartUrl, function (result) {
      dealerApp.orderSale.loadCart(saleCartContainer.data("url"), "index", function (result) {
        saleCartContainer.html(result.html);
      });
    });
  });

  // Update quantity change
  $(document).on("qty:changed", ".qty-input", function () {
    const parentCart = $(this).closest("#sale-cart-container");
    if (parentCart.length > 0) {
      const qty = parseInt($(this).val()) || 1;
      const url = saleCartContainer.data("url-update");
      const itemId = $(this).closest("tr").data("item-id");
      dealerApp.orderSale.updateProductInCart(itemId, qty, url, function (result) {
        dealerApp.orderSale.loadCart(saleCartContainer.data("url"), "index", function (result) {
          saleCartContainer.html(result.html);
        });
      });
    }
  });

  // Delete item in cart
  saleCartContainer.on("click", ".btn-delete-item", function (event) {
    event.preventDefault();
    const btnDelete = $(event.currentTarget);
    const itemId = parseInt(btnDelete.closest("tr").attr("data-item-id"), 10);
    const url = saleCartContainer.data("url-delete");
    dealerApp.orderSale.deleteProductInCart(itemId, url, function (result) {
      dealerApp.orderSale.loadCart(saleCartContainer.data("url"), "index", function (result) {
        saleCartContainer.html(result.html);
      });
    });
  });
});
