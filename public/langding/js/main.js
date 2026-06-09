window.dealerApp = {
  showToast: function (type, message = "Nội dung thông báo", title = "Thông báo", callbackSuccess) {
    const id = "toast-" + Date.now();
    const bgClass = type === "success" ? "bg-success" : "bg-danger";
    const toastHtml = $(`
    <div id="${id}" class="toast p-0 rounded-2" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header ${bgClass} text-white border-0">
        <strong class="me-auto">${title}</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body bg-white border-0 text-black">${message}</div>
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
  loadingShow: function () {
    if (!$("#preloader").length) {
      $("body").append('<div id="preloader"><div class="loader"></div></div>');
    }
  },
  loadingHide: function () {
    $("body").find("#preloader").remove();
  },
};
("use strict");
(function ($) {
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
  $(document).ready(function () {
    ///////////cr1 fix
    // Xử lý click vào nút "LOẠI XE" hoặc "KÍCH CỠ"
    $(".list-btn-text .btn").on("click", function (e) {
      e.preventDefault();
      e.stopPropagation(); // Ngăn event bubbling

      const value = $(this).data("value");

      // Toggle active class
      $(".list-btn-text .btn").removeClass("active");
      $(this).addClass("active");

      // Hiển thị popup tương ứng
      if (value === "productTypeCar") {
        $("#search-widget-type").modal("show");
        // Reset về step 1
        resetModalSteps("#search-widget-type");
      } else if (value === "productTypeSize") {
        $("#search-widget-size").modal("show");
        // Reset về step 1
        resetModalSteps("#search-widget-size");
      }
    });

    // Xử lý click vào ô input - CHỈ focus, KHÔNG hiện popup
    $(".search-widget-input").on("click", function (e) {
      e.stopPropagation(); // Ngăn event bubbling
      // Input đã không có readonly nên có thể nhập text bình thường
      $(this).focus();
    });

    // Ngăn không cho click vào box trigger popup
    $(".search-widget .box").on("click", function (e) {
      // Chỉ cho phép click vào button và input
      if (!$(e.target).closest("button, input").length) {
        e.preventDefault();
        e.stopPropagation();
      }
    });

    // Function reset modal về step 1
    function resetModalSteps(modalId) {
      $(modalId).find(".search-widget-popup-title").removeClass("active").first().addClass("active");
      $(modalId).find(".search-widget-list").addClass("d-none");
      $(modalId).find('[data-step-target="1"]').removeClass("d-none");
    }

    // Xử lý search button
    $(".btn-search-icon.search").on("click", function () {
      const searchValue = $(".search-widget-input").val();
      if (searchValue) {
        // Thực hiện tìm kiếm với text
        console.log("Tìm kiếm:", searchValue);
        // TODO: Implement search logic
      }
    });
    ///////////cr1 fix

    Fancybox.bind("[data-fancybox]", {
      // Your custom options
    });
    $(".select2-filter").select2({
      allowClear: true,
    });
    $("#select2-filter-range").select2({
      allowClear: false,
      minimumResultsForSearch: -1,
    });
    $("#select2-filter-property").select2({
      allowClear: false,
      minimumResultsForSearch: -1,
    });
    $("#select2-filter-sort").select2({
      allowClear: false,
      minimumResultsForSearch: -1,
    });
    $("#select2-filter-pagination").select2({
      allowClear: false,
      minimumResultsForSearch: -1,
    });
    // Advanced Scroll Animation
    function checkScroll() {
      $(".scroll-animate").each(function (index) {
        const element = $(this);
        const elementTop = element.offset().top;
        const elementBottom = elementTop + element.outerHeight();
        const viewportTop = $(window).scrollTop();
        const viewportBottom = viewportTop + $(window).height();

        // Trigger khi element xuất hiện 20% trong viewport
        const threshold = $(window).height() * 0.8;

        if (elementBottom > viewportTop + threshold && elementTop < viewportBottom) {
          if (!element.hasClass("animated")) {
            const animationType = element.data("animate") || "fadeInUp";
            const delay = element.data("delay") || 0;

            setTimeout(function () {
              element.addClass("animate__animated animate__" + animationType + " animated");
            }, delay);
          }
        }
      });
    }

    // Throttle scroll event
    let ticking = false;
    $(window).on("scroll", function () {
      if (!ticking) {
        requestAnimationFrame(function () {
          checkScroll();
          ticking = false;
        });
        ticking = true;
      }
    });

    $(".main-slider-items").slick({
      slidesToShow: 3,
      slidesToScroll: 1,
      centerMode: true,
      arrows: false,
      dots: true,
      speed: 300,
      centerPadding: "0px",
      infinite: true,
      autoplaySpeed: 5000,
      responsive: [
        {
          breakpoint: 576,
          settings: {
            slidesToShow: 1,
          },
        },
      ],
    });
    $(".box-slider-2-items").slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      dots: true,
      speed: 300,
      centerPadding: "0px",
      infinite: true,
      autoplaySpeed: 4000,
      autoplay: true,
    });
    $(".products-sales-slider").slick({
      slidesToShow: 3,
      arrows: false,
      dots: true,
      speed: 300,
      infinite: true,
      autoplaySpeed: 5000,
      responsive: [
        {
          breakpoint: 1400,
          settings: {
            slidesToShow: 3,
          },
        },
        {
          breakpoint: 768,
          settings: {
            slidesToShow: 2,
          },
        },
      ],
    });
    $(".category-avenza-product-highlight .highlight-items").slick({
      slidesToShow: 4,
      arrows: false,
      dots: true,
      speed: 300,
      infinite: true,
      autoplaySpeed: 5000,
      responsive: [
        {
          breakpoint: 1400,
          settings: {
            slidesToShow: 3,
          },
        },
        {
          breakpoint: 1200,
          settings: {
            slidesToShow: 2,
          },
        },
        {
          breakpoint: 575,
          settings: {
            slidesToShow: 1,
          },
        },
      ],
    });
    $(".news-slider").slick({
      slidesToShow: 2,
      slidesToScroll: 1,
      arrows: false,
      dots: true,
      speed: 300,
      infinite: true,
      autoplaySpeed: 5000,
      loop: true,
      responsive: [
        {
          breakpoint: 1200,
          settings: {
            slidesToShow: 2,
            variableWidth: true,
          },
        },
      ],
    });
    $(".news-slider-child").slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      dots: true,
      speed: 300,
      infinite: true,
    });
    $(".video-slider").slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      dots: true,
      speed: 300,
      infinite: true,
      autoplaySpeed: 5000,
      loop: true,
      responsive: [
        {
          breakpoint: 1366,
          settings: {
            slidesToShow: 1,
          },
        },
      ],
    });

    $(".box-category-slider").slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      dots: true,
      speed: 300,
      infinite: true,
      autoplaySpeed: 5000,
      loop: true,
    });

    $('button[data-bs-toggle="tab"]').one("shown.bs.tab", function (e) {
      var target = $(e.target).attr("data-bs-target") || $(e.target).attr("href");
      var slider = $(target).find(".box-category-slider");
      if (slider.length && !slider.hasClass("slick-initialized")) {
        slider.slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: false,
          dots: true,
          speed: 300,
          infinite: true,
          autoplaySpeed: 5000,
          loop: true,
        });
      }
    });

    var boxCategoryTabs = document.getElementById("box-category-title");
    if (boxCategoryTabs) {
      boxCategoryTabs.addEventListener("shown.bs.tab", function (e) {
        var targetSelector = e.target.getAttribute("data-bs-target");
        if (!targetSelector) return;
        var pane = document.querySelector(targetSelector);
        if (pane && pane.classList.contains("category-tab-pane-scroll")) {
          pane.scrollTop = 0;
        }
      });
    }

    $(".box-partner-slider").slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      dots: true,
      speed: 300,
      infinite: true,
      autoplaySpeed: 5000,
      loop: true,
    });

    $(".box-media-slider").slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      dots: true,
      speed: 300,
      infinite: true,
      autoplaySpeed: 5000,
      loop: true,
    });

    $('button[data-bs-toggle="tab"]').one("shown.bs.tab", function (e) {
      var target = $(e.target).attr("data-bs-target") || $(e.target).attr("href");
      var slider = $(target).find(".box-media-slider");
      if (slider.length && !slider.hasClass("slick-initialized")) {
        slider.slick({
          slidesToShow: 1,
          slidesToScroll: 1,
          arrows: false,
          dots: true,
          speed: 300,
          infinite: true,
          autoplaySpeed: 5000,
          loop: true,
        });
      }
    });

    $(".category-banner-design-slider").each(function () {
      const autoplaySpeed = $(this).data("autoplay-speed") || 3000;
      $(this).slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: false,
        dots: true,
        speed: 300,
        infinite: true,
        autoplaySpeed: autoplaySpeed,
        loop: true,
        autoplay: true,
      });
    });

    $(".category-why-slider").slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      dots: true,
      speed: 300,
      infinite: true,
      autoplaySpeed: 3000,
      loop: true,
      autoplay: true,
    });

    $(".product-detail-slider .slider-for").slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      fade: true,
      asNavFor: ".product-detail-slider .slider-nav",
      dots: true,
    });
    $(".product-detail-slider .slider-nav").slick({
      slidesToShow: 3,
      slidesToScroll: 1,
      vertical: true,
      asNavFor: ".slider-for",
      dots: false,
      focusOnSelect: true,
      verticalSwiping: true,
      responsive: [
        {
          breakpoint: 1400,
          settings: {
            vertical: false,
          },
        },
      ],
    });

    // Refresh slider mỗi khi tab thay đổi
    $('button[data-bs-toggle="tab"]').on("shown.bs.tab", function () {
      setTimeout(function () {
        $(".slick-slider.slick-initialized").each(function () {
          $(this).slick("setPosition");
        });
      }, 100);
    });

    $(window).on("load", function () {
      $(".slick-initialized").each(function () {
        $(this).slick("setPosition");
      });
    });

    // Initial check (sau khi init slider)
    checkScroll();

    // Qty
    $(".qty-increase").click(function () {
      const qtyInput = $(this).siblings(".qty-input");
      let currentValue = parseInt(qtyInput.val()) || 1;
      let maxValue = parseInt(qtyInput.attr("max")) || 999; // Giới hạn tối đa

      if (currentValue < maxValue) {
        qtyInput.val(currentValue + 1);
      }
    });

    // Giảm số lượng
    $(".quantity .qty-decrease").click(function () {
      const qtyInput = $(this).siblings(".qty-input");
      let currentValue = parseInt(qtyInput.val()) || 1;
      let minValue = parseInt(qtyInput.attr("min")) || 1; // Giới hạn tối thiểu

      if (currentValue > minValue) {
        qtyInput.val(currentValue - 1);
      }
    });

    // Xử lý khi người dùng nhập trực tiếp
    $(".quantity .qty-input").on("input", function () {
      let value = parseInt($(this).val());
      let minValue = parseInt($(this).attr("min")) || 1;
      let maxValue = parseInt($(this).attr("max")) || 999;

      // Kiểm tra giá trị hợp lệ
      if (isNaN(value) || value < minValue) {
        $(this).val(minValue);
      } else if (value > maxValue) {
        $(this).val(maxValue);
      }
    });

    // Chặn nhập ký tự không phải số
    $(".quantity .qty-input").on("keypress", function (e) {
      // Chỉ cho phép số và một số phím đặc biệt
      if (e.which < 48 || e.which > 57) {
        e.preventDefault();
      }
    });

    // Xử lý khi mất focus
    $(".quantity .qty-input").on("blur", function () {
      let value = parseInt($(this).val());
      let minValue = parseInt($(this).attr("min")) || 1;

      if (isNaN(value) || value < minValue) {
        $(this).val(minValue);
      }
    });

    $(".social-sidebar a.custom-view").click(function (e) {
      e.preventDefault();
      $(".social-sidebar a:not(.custom-view)").toggleClass("active");
    });

    $(".social-sidebar .social-sidebar-item-icon").click(function (e) {
      // ✅ Bỏ qua nếu click vào .nav-btn hoặc các element bên trong
      if ($(e.target).closest(".nav-btn").length || $(e.target).closest(".navigation-control-container").length) {
        return; // Không xử lý, để event bubble lên handler của .nav-btn
      }

      e.stopPropagation(); // Ngăn event bubbling

      // Lấy item hiện tại được click
      const $currentItem = $(this).closest(".social-sidebar-item");
      const $currentBox = $currentItem.find(".social-sidebar-item-box");

      // Ẩn tất cả các box
      $(".social-sidebar-item .social-sidebar-item-box").removeClass("active");

      // Hiển thị box của item được click
      $currentBox.addClass("active");
    });

    // ✅ THÊM: Ẩn tất cả sidebar boxes khi click ra ngoài
    $(document).on("click", function (e) {
      // Kiểm tra xem click có nằm trong social-sidebar không
      if (!$(e.target).closest(".social-sidebar").length) {
        $(".social-sidebar-item .social-sidebar-item-box").removeClass("active");
      }
    });

    $(".social-sidebar .social-sidebar-item-icon").click(function (e) {
      // ✅ Bỏ qua nếu click vào .nav-btn hoặc các element bên trong
      if ($(e.target).closest(".nav-btn").length || $(e.target).closest(".navigation-control-container").length) {
        return; // Không xử lý, để event bubble lên handler của .nav-btn
      }

      e.stopPropagation(); // Ngăn event bubbling

      // Lấy item hiện tại được click
      const $currentItem = $(this).closest(".social-sidebar-item");
      const $currentBox = $currentItem.find(".social-sidebar-item-box");

      // Kiểm tra xem box hiện tại có đang active không
      const isCurrentlyActive = $currentBox.hasClass("active");

      // Ẩn tất cả các box khác
      $(".social-sidebar-item .social-sidebar-item-box").removeClass("active");

      // Nếu box hiện tại chưa active, thì hiển thị nó
      // Nếu đã active, thì ẩn nó (toggle behavior)
      if (!isCurrentlyActive) {
        $currentBox.addClass("active");
      }
    });

    $(".social-sidebar").on("click", "#support-show-sidebar", function (e) {
      e.preventDefault();
      $(this).closest(".social-sidebar-group").find(".social-sidebar-group-item").fadeToggle(300);
      $(this).find(".support-show-sidebar-close").toggleClass("d-none");
      $(this).find("img").toggleClass("d-none");
    });

    // Password toggle
    $(document).on("click", ".password-action", function () {
      const input = $(this).closest(".password-action-group").find("input");
      const icon = $(this).find("svg");
      if (input.attr("type") === "password") {
        input.attr("type", "text");
        icon.html(`
            <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7 7 0 0 0-2.79.588l.77.771A6 6 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755q-.247.248-.517.486z"/>
            <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829"/>
            <path d="M3.35 5.47q-.27.24-.518.487A13 13 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7 7 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12z"/>
        `);
      } else {
        input.attr("type", "password");
        icon.html(`
            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
        `);
      }
    });
    // Favourite
    $(".product-favourite").click(function () {
      $(this).toggleClass("actived");
    });
    // Favourite
    $(".category-search-filter-display-icon").click(function () {
      $(this).siblings().removeClass("active");
      $(this).addClass("active");
    });

    // Scroll header
    let isScrolling = false;

    $(window).scroll(function () {
      if (!isScrolling) {
        requestAnimationFrame(function () {
          if ($(window).scrollTop() >= $(window).height()) {
            if ($(".search-widget").hasClass("search-widget-secondary") && !$(".search-widget").hasClass("collapsed")) {
              $(".search-toggle-arrow").trigger("click");
            }
          } else {
            if ($(".search-widget").hasClass("search-widget-secondary") && $(".search-widget").hasClass("collapsed")) {
              $("#search-toggle-sidebar").trigger("click");
            }
          }

          isScrolling = false;
        });
        isScrolling = true;
      }
    });
    // $(window).resize(function () {
    //   if ($(window).width() > 991) {
    //     if ($(".search-widget").hasClass("search-widget-secondary")) {
    //       if ($(window).scrollTop() >= $(window).height()) {
    //         $(".search-widget").addClass("fixed-top");
    //       } else {
    //         $(".search-widget").removeClass("fixed-top");
    //       }
    //     }
    //   } else {
    //     if ($(".search-widget").hasClass("search-widget-secondary")) {
    //       $(".search-widget").removeClass("fixed-top");
    //     }
    //   }
    // });
    // function checkScrollState() {
    //   if ($(window).scrollTop() > 156) {
    //     $(".header").addClass("fixed");
    //   } else {
    //     $(".header").removeClass("fixed");
    //   }

    //   if ($(window).scrollTop() >= $(window).height()) {
    //     $(".header").addClass("header-secondary");
    //     if ($(".search-widget").hasClass("search-widget-secondary")) {
    //       if ($(window).width() > 991) {
    //         $(".search-widget").addClass("fixed-top");
    //       } else {
    //         $(".search-widget").removeClass("fixed-top");
    //       }
    //       $(".category-search").css("padding-top", "195px");
    //       $(".category-search .breadcrumb-dark").removeClass("d-none");
    //     }
    //   } else {
    //     $(".header").removeClass("header-secondary");
    //     if ($(".search-widget").hasClass("search-widget-secondary")) {
    //       $(".search-widget").removeClass("fixed-top");
    //       $(".category-search").css("padding-top", "40px");
    //       $(".category-search .breadcrumb-dark").addClass("d-none");
    //     }
    //   }
    //   if ($(window).width() > 991) {
    //     if ($(".search-widget").hasClass("search-widget-secondary")) {
    //       if ($(window).scrollTop() >= $(window).height()) {
    //         $(".search-widget").addClass("fixed-top");
    //       } else {
    //         $(".search-widget").removeClass("fixed-top");
    //       }
    //     }
    //   } else {
    //     if ($(".search-widget").hasClass("search-widget-secondary")) {
    //       $(".search-widget").removeClass("fixed-top");
    //     }
    //   }
    // }
    // checkScrollState();

    // Overlay element
    if (!$(".menu-overlay").length) {
      $("body").append('<div class="menu-overlay"></div>');
    }

    $(".menu-icon").click(function () {
      $(".nav-menu").css("left", "0");
      $(".menu-overlay").addClass("active");
      $("body").addClass("menu-open");
      $(".social-sidebar").css("display", "none");
    });

    $(".nav-menu .close-menu").click(function () {
      $(".nav-menu").css("left", "-100%");
      $(".menu-overlay").removeClass("active");
      $("body").removeClass("menu-open");
      $("body").find(".menu-level-1").removeClass("active");
      $(".social-sidebar").css("display", "");
    });

    // Đóng menu khi click vào overlay
    $(document).on("click", ".menu-overlay", function () {
      $(".nav-menu").css("left", "-100%");
      $(".menu-overlay").removeClass("active");
      $("body").removeClass("menu-open");
      $("body").find(".menu-level-1").removeClass("active");
    });

    // Hover menu level 1
    $(document).on("click", ".nav-menu .menu-center .show-level-1", function (e) {
      e.preventDefault();
      e.stopPropagation();
      const $menuLevel1 = $(this).closest("li").find(".menu-level-1");
      const isVisible = $menuLevel1.hasClass("active");
      if (isVisible) {
        // Nếu đang hiển thị → Ẩn đi
        $menuLevel1.removeClass("active");
      } else {
        // Nếu đang ẩn → Hiển thị
        // Đóng tất cả menu level 1 khác trước
        $(".menu-level-1").removeClass("active");

        // Hiện menu hiện tại
        $menuLevel1.addClass("active");
      }
    });

    // search-widget

    // $(".search-widget .list-btn-text button").click(function () {
    //   $(".search-widget .list-btn-text button").removeClass("active");
    //   $(this).addClass("active");
    // });
    // Handle modal search with step
    const typeModalEl = document.getElementById("search-widget-type");
    const sizeModalEl = document.getElementById("search-widget-size");
    const searchWidgetType = typeof bootstrap !== "undefined" && bootstrap.Modal && typeModalEl ? bootstrap.Modal.getOrCreateInstance(typeModalEl) : null;
    const searchWidgetSize = typeof bootstrap !== "undefined" && bootstrap.Modal && sizeModalEl ? bootstrap.Modal.getOrCreateInstance(sizeModalEl) : null;
    let searchWidgetPopupName = "";

    ///bỏ khi click input sẽ hiện popup search
    // $(document).on("focus", ".search-widget .search-widget-input", function () {
    //   const popupType = $(".search-widget .list-btn-text button.active").attr(
    //     "data-value"
    //   );
    //   if (popupType === "productTypeCar") {
    //     if (searchWidgetType) {
    //       searchWidgetType.show();
    //     }
    //     searchWidgetPopupName = "searchWidgetType";
    //   }
    //   if (popupType === "productTypeSize") {
    //     if (searchWidgetSize) {
    //       searchWidgetSize.show();
    //     }
    //     searchWidgetPopupName = "searchWidgetSize";
    //   }
    // });

    let selectedStep1 = "";
    let selectedStep2 = "";
    let selectedStep3 = "";

    // ✅ PREVENT CLICK VÀO HEADER/TITLE (chỉ cho phép click vào item list)
    $(document).on("click", ".search-widget-popup-title", function (e) {
      e.preventDefault();
      e.stopPropagation();
      console.warn("⚠️ Cannot click header! Please click on list items below.");
      alert("⚠️ Vui lòng chọn từ danh sách bên dưới, không click vào tiêu đề!");
      return false;
    });

    // ✅ Thêm CSS để làm rõ header không thể click
    $(document).ready(function () {
      $(".search-widget-popup-title").css({
        cursor: "default",
        "user-select": "none",
        "pointer-events": "auto", // Để vẫn catch event và show warning
      });
      $(".search-widget-popup-item").css({
        cursor: "pointer",
      });
    });

    // Ẩn tất cả các step khác ngoại trừ step 1 khi modal mở
    $(".search-widget-popup").on("show.bs.modal", function () {
      $(".search-widget-popup .search-widget-list").addClass("d-none");
      $(".search-widget-popup .search-widget-list[data-step-target=1]").removeClass("d-none");
      $(".search-widget-popup .search-widget-popup-title").removeClass("active");
      $('.search-widget-popup .search-widget-popup-title[data-step="1"]').addClass("active");
    });

    // Xử lý click vào item của brand (step 1) - Car hoặc Size
    $(document).on("click", ".search-widget-popup .search-widget-list[data-step-target=1] .search-widget-popup-item", function () {
      // ⚠️ DEBUG: Log element được click
      console.log("🖱️ Clicked element:", this);
      console.log("📋 Element HTML:", $(this).prop("outerHTML"));
      console.log("📝 data-value:", $(this).attr("data-value"));
      console.log("📝 text():", $(this).text());

      // ✅ Lấy data-value nếu có, fallback về text và trim whitespace
      selectedStep1 = $(this).attr("data-value") || $(this).text().trim();
      console.log("🔍 Step 1 selected:", selectedStep1);

      // ✅ CHECK: Đang ở modal nào? (car vs size)
      const isInSizeModal = $(this).closest("#search-widget-size").length > 0;

      if (isInSizeModal && typeof tireSizeCombinations !== "undefined") {
        // ✅ DYNAMIC FILTERING cho size search
        console.log("🔧 Applying dynamic filtering for size search");

        // Get available rates for selected wide
        const availableRates = tireSizeCombinations[selectedStep1] || {};
        const rateValues = Object.keys(availableRates);

        console.log("📊 Available rates for " + selectedStep1 + ":", rateValues);

        const sizeModal = $("#search-widget-size");
        sizeModal.data("selected-width", selectedStep1);
        sizeModal.removeData("selected-rate");
        sizeModal.removeData("selected-diameter");

        // Clear và rebuild step 2 với chỉ rates có sẵn
        const step2Container = $('#search-widget-size .search-widget-list[data-step-target="2"]');
        const backButton = step2Container.find(".search-widget-popup-back").parent();
        step2Container.find(".search-widget-popup-item").remove();

        // Add only available rates
        rateValues.sort((a, b) => parseInt(a) - parseInt(b));
        rateValues.forEach((rate) => {
          step2Container.append(`<div class="search-widget-popup-item" data-value="${rate}">${rate}</div>`);
        });
      }

      // Ẩn step 1, hiện step 2
      $(".search-widget-popup .search-widget-list").addClass("d-none");
      $(".search-widget-popup .search-widget-list[data-step-target=2]").removeClass("d-none");

      // Cập nhật title active
      $(".search-widget-popup .search-widget-popup-title").removeClass("active");
      $('.search-widget-popup .search-widget-popup-title[data-step="2"]').addClass("active");
    });

    // Xử lý click vào item của model (step 2) - Car hoặc Rate
    $(document).on("click", ".search-widget-popup .search-widget-list[data-step-target=2] .search-widget-popup-item", function () {
      // ⚠️ DEBUG: Log element được click
      console.log("🖱️ Clicked element (Step 2):", this);
      console.log("📋 Element HTML:", $(this).prop("outerHTML"));
      console.log("📝 data-value:", $(this).attr("data-value"));
      console.log("📝 text():", $(this).text());

      // ✅ Lấy data-value nếu có, fallback về text và trim whitespace
      selectedStep2 = $(this).attr("data-value") || $(this).text().trim();
      console.log("🔍 Step 2 selected:", selectedStep2);

      // ✅ CHECK: Đang ở modal size?
      const isInSizeModal = $(this).closest("#search-widget-size").length > 0;

      if (isInSizeModal && typeof tireSizeCombinations !== "undefined" && selectedStep1) {
        // ✅ DYNAMIC FILTERING cho diameter (step 3)
        console.log("🔧 Applying dynamic filtering for diameter");

        // Get available diameters for selected wide+rate
        const availableDiameters = tireSizeCombinations[selectedStep1]?.[selectedStep2] || [];

        console.log("📊 Available diameters for " + selectedStep1 + "/" + selectedStep2 + ":", availableDiameters);

        const sizeModal = $("#search-widget-size");
        sizeModal.data("selected-rate", selectedStep2);
        sizeModal.removeData("selected-diameter");

        // Clear và rebuild step 3 với chỉ diameters có sẵn
        const step3Container = $('#search-widget-size .search-widget-list[data-step-target="3"]');
        step3Container.find(".search-widget-popup-item").remove();

        // Add only available diameters
        // Hỗ trợ sort đúng với số thập phân (vd "22,5" hoặc "22.5")
        availableDiameters.sort((a, b) => {
          const na = parseFloat(String(a).replace(",", "."));
          const nb = parseFloat(String(b).replace(",", "."));
          return (isNaN(na) ? 0 : na) - (isNaN(nb) ? 0 : nb);
        });
        availableDiameters.forEach((diameter) => {
          step3Container.append(`<div class="search-widget-popup-item" data-value="${diameter}">${diameter}</div>`);
        });
      }

      // Ẩn step 2, hiện step 3
      $(".search-widget-popup .search-widget-list").addClass("d-none");
      $(".search-widget-popup .search-widget-list[data-step-target=3]").removeClass("d-none");

      // Cập nhật title active
      $(".search-widget-popup .search-widget-popup-title").removeClass("active");
      $('.search-widget-popup .search-widget-popup-title[data-step="3"]').addClass("active");
    });

    // Xử lý click vào item của year (step 3) - hoàn thành và đóng modal
    $(document).on("click", ".search-widget-popup .search-widget-list[data-step-target=3] .search-widget-popup-item", function () {
      // ⚠️ DEBUG: Log element được click
      console.log("🖱️ Clicked element (Step 3):", this);
      console.log("📋 Element HTML:", $(this).prop("outerHTML"));
      console.log("📝 data-value:", $(this).attr("data-value"));
      console.log("📝 text():", $(this).text());

      // ✅ Lấy data-value nếu có, fallback về text và trim whitespace
      selectedStep3 = $(this).attr("data-value") || $(this).text().trim();
      console.log("🔍 Step 3 selected:", selectedStep3);
      console.log("✅ Final selection:", { step1: selectedStep1, step2: selectedStep2, step3: selectedStep3 });

      // ✅ Validation: Đảm bảo không có placeholder text
      const invalidKeywords = ["THƯƠNG HIỆU", "MẪU XE", "NĂM", "ĐỘ RỘNG", "TỶ LỆ", "ĐƯỜNG KÍNH"];
      const hasInvalidValue = [selectedStep1, selectedStep2, selectedStep3].some((val) => invalidKeywords.some((keyword) => val.toUpperCase().includes(keyword)));

      if (hasInvalidValue) {
        console.error("❌ Invalid selection detected:", { step1: selectedStep1, step2: selectedStep2, step3: selectedStep3 });
        alert("Vui lòng chọn giá trị hợp lệ từ danh sách");
        return;
      }

      let searchQuery = "";

      if (searchWidgetPopupName === "searchWidgetType" && searchWidgetType) {
        searchWidgetType.hide();
        searchQuery = `Thương hiệu: ${selectedStep1}, Mẫu xe: ${selectedStep2}, Năm: ${selectedStep3}`;
        console.log(`Tìm kiếm theo: Loại Xe`);
        $(".search-widget .search-widget-input").val(searchQuery);
      }
      if (searchWidgetPopupName === "searchWidgetSize" && searchWidgetSize) {
        searchWidgetSize.hide();
        searchQuery = `Độ rộng: ${selectedStep1}, Tỷ lệ: ${selectedStep2}, Đường kính mâm xe: ${selectedStep3}`;
        console.log(`Tìm kiếm theo: Kích cỡ`);
        $(".search-widget .search-widget-input").val(searchQuery);
        const sizeModal = $("#search-widget-size");
        sizeModal.data("selected-width", selectedStep1);
        sizeModal.data("selected-rate", selectedStep2);
        sizeModal.data("selected-diameter", selectedStep3);
      }

      // ⚠️ DEBUG: Hiển thị query được tạo ra
      console.log("🔍 ========== DROPDOWN SEARCH DEBUG ==========");
      console.log("📝 Query created:", searchQuery);
      console.log("🎯 Type:", searchWidgetPopupName === "searchWidgetType" ? "car" : "size");
      console.log("📊 Values:", { step1: selectedStep1, step2: selectedStep2, step3: selectedStep3 });
      console.log("==============================================");
      $(".search-widget .list-btn-search-icon .btn-search-icon-custom").addClass("d-none");
      $(".search-widget .list-btn-search-icon .btn-search-icon-custom-reset").removeClass("d-none");
    });

    // Xử lý nút "Quay lại"
    $(document).on("click", ".search-widget-popup .search-widget-popup-back", function () {
      const currentStepElement = $(this).closest(".search-widget-list");
      const currentStep = currentStepElement.attr("data-step-target");
      if (currentStep === undefined) return;
      if (currentStep === "2") {
        // Từ step 2 về step 1
        $(".search-widget-popup .search-widget-list").addClass("d-none");
        $('.search-widget-popup .search-widget-list[data-step-target="1"]').removeClass("d-none");
        $(".search-widget-popup .search-widget-popup-title").removeClass("active");
        $('.search-widget-popup .search-widget-popup-title[data-step="1"]').addClass("active");
        selectedStep1 = "";
        selectedStep2 = "";
        selectedStep3 = "";
      }
      if (currentStep === "3") {
        // Từ step 3 về step 2
        $(".search-widget-popup .search-widget-list").addClass("d-none");
        $('.search-widget-popup .search-widget-list[data-step-target="2"]').removeClass("d-none");
        $(".search-widget-popup .search-widget-popup-title").removeClass("active");
        $('.search-widget-popup .search-widget-popup-title[data-step="2"]').addClass("active");
        selectedStep2 = "";
        selectedStep3 = "";
      }
    });

    $(".search-widget .list-btn-search-icon .btn-search-icon-custom-reset").click(function () {
      $(".search-widget .search-widget-input").val("");
      $(".search-widget .list-btn-search-icon .btn-search-icon-custom").removeClass("d-none");
      $(this).addClass("d-none");
      selectedStep1 = "";
      selectedStep2 = "";
      selectedStep3 = "";
    });

    // Search widget toggle functionality
    $(document).ready(function () {
      // Toggle search widget collapse/expand - click vào icon '>' trong search bar
      $(document).on("click", ".search-toggle-arrow", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $searchWidget = $(".search-widget");
        const $toggleSidebar = $("#search-toggle-sidebar");

        if ($searchWidget.hasClass("collapsed")) {
          // Expand - hiển thị đầy đủ thanh search
          $searchWidget.removeClass("collapsed");
          // Đợi animation xong rồi mới ẩn icon sidebar
          setTimeout(function () {
            $toggleSidebar.addClass("d-none");
          }, 400);
          $(this).attr("aria-label", "Ẩn thanh tìm kiếm");
        } else {
          // Collapse - ẩn thanh search
          $searchWidget.addClass("collapsed");
          // Hiện icon sidebar sau khi bắt đầu animation
          setTimeout(function () {
            $toggleSidebar.removeClass("d-none");
          }, 200);
          $(this).attr("aria-label", "Hiện thanh tìm kiếm");
        }
      });

      // Toggle search widget từ icon trong sidebar
      $(document).on("click", "#search-toggle-sidebar", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $searchWidget = $(".search-widget");
        const $toggleSidebar = $(this);

        // Expand - hiển thị lại thanh search
        $searchWidget.removeClass("collapsed");
        // Đợi animation xong rồi mới ẩn icon sidebar
        setTimeout(function () {
          $toggleSidebar.addClass("d-none");
        }, 400);
        $(".search-toggle-arrow").attr("aria-label", "Ẩn thanh tìm kiếm");
      });

      // Nút X ở đầu sidebar: ẩn cụm hỗ trợ nhanh
      $(document).on("click", "#social-items-close-btn", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $socialSidebar = $("#socialSidebarGroup");
        $socialSidebar.find(".social-sidebar-item-box").removeClass("active");
        $socialSidebar.addClass("social-items-collapsed");
        $("#social-items-expand-btn").removeClass("d-none");
      });

      // Icon riêng: hiện lại cụm hỗ trợ nhanh
      $(document).on("click", "#social-items-expand-btn", function (e) {
        e.preventDefault();
        e.stopPropagation();

        const $socialSidebar = $("#socialSidebarGroup");
        $socialSidebar.removeClass("social-items-collapsed");
        $(this).addClass("d-none");
      });
    });
    // Cho category-search-hot-link
    function checkScrollIfOverflow($container, $hint) {
      const el = $container[0];
      if (!el) return;

      const canScroll = el.scrollWidth > el.clientWidth;
      const atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 2;

      $hint.toggleClass("d-none", !canScroll || atEnd);
    }
    const $hotLink = $(".category-search-hot-link");
    const $hotHint = $hotLink.find(".scroll-if-overflow");
    checkScrollIfOverflow($hotLink, $hotHint);

    $hotLink.on("scroll", function () {
      checkScrollIfOverflow($hotLink, $hotHint);
    });

    $(window).on("resize", function () {
      checkScrollIfOverflow($hotLink, $hotHint);
    });
  });
})(jQuery);

/* ============================================
   javascript Contact Icon với Popup - hotline - zalo
   ============================================ */
document.addEventListener("DOMContentLoaded", function () {
  const contactIcon = document.getElementById("contactIcon");
  const contactPopup = document.getElementById("contactPopup");

  if (!contactIcon || !contactPopup) return;

  // Đóng popup khi click ra ngoài
  document.addEventListener("click", function (e) {
    if (!contactIcon.contains(e.target) && !contactPopup.contains(e.target)) {
      contactPopup.style.opacity = "0";
      contactPopup.style.visibility = "hidden";
    }
  });

  // Mở popup khi click vào icon (cho mobile)
  if (window.innerWidth <= 768) {
    contactIcon.addEventListener("click", function (e) {
      e.preventDefault();
      const isVisible = contactPopup.style.visibility === "visible";
      contactPopup.style.opacity = isVisible ? "0" : "1";
      contactPopup.style.visibility = isVisible ? "hidden" : "visible";
    });
  }
});

/**
 *
 * ///////////////Shopping Cart//////////////
 *
 **/
// Step handle
$(".shopping-cart .shopping-cart-content-item .btn-completed").on("click", function () {
  const currentStep = $(this).closest(".shopping-cart-content-item").attr("data-step");
  const nextStep = parseInt(currentStep) + 1;
  // Content
  $(".shopping-cart .shopping-cart-content-item").addClass("d-none");
  $(`.shopping-cart .shopping-cart-content-item[data-step='${nextStep}']`).addClass("active").removeClass("d-none");
  // Tab
  $(".shopping-cart .shopping-cart-tab-item").each(function () {
    const step = $(this).attr("data-step");
    if (parseInt(step) < nextStep) {
      $(this).addClass("completed").removeClass("active");
    } else if (parseInt(step) === nextStep) {
      $(this).addClass("active").removeClass("completed");
    } else {
      $(this).removeClass("active completed");
    }
  });
});

// Checkboxes "Choose all" in cart
$(".shopping-cart #shopping-cart-content-products-checkall").on("change", function () {
  const isChecked = $(this).is(":checked");
  $(".shopping-cart .table-div-row-product .form-check-input").prop("checked", isChecked);
});
// Click checkbox in product rows
$(".shopping-cart  .table-div-row-product .form-check-input").on("change", function () {
  const totalProductCheckboxes = $(".shopping-cart .table-div-row-product .form-check-input").length;
  const checkedProductCheckboxes = $(".shopping-cart .table-div-row-product .form-check-input:checked").length;
  if (checkedProductCheckboxes === totalProductCheckboxes) {
    $(".shopping-cart #shopping-cart-content-products-checkall").prop("checked", true);
  } else {
    $(".shopping-cart #shopping-cart-content-products-checkall").prop("checked", false);
  }
});
// Click checkbox in payment
$(".shopping-cart .shopping-cart-content-payment-item .form-check-input").on("change", function () {
  if (this.checked) {
    // Uncheck tất cả checkbox khác
    $(".shopping-cart .shopping-cart-content-payment-item .form-check-input").not(this).prop("checked", false);
    $(".shopping-cart .shopping-cart-content-payment-item .form-check-input").not(this).closest(".shopping-cart-content-payment-item").removeClass("active");
    this.closest(".shopping-cart-content-payment-item").classList.add("active");
  }
});
// Click show/hide product confirm list
$(".shopping-cart .shopping-cart-content-sidebar .product-confirm-list-action").on("click", function () {
  const isShow = $(".shopping-cart .shopping-cart-content-sidebar .product-confirm-list").hasClass("active");
  if (isShow) {
    $(".shopping-cart .shopping-cart-content-sidebar .product-confirm-list").removeClass("active");
    $(this).find("svg").css("transform", "rotate(0deg)");
  } else {
    $(".shopping-cart .shopping-cart-content-sidebar .product-confirm-list").addClass("active");
    $(this).find("svg").css("transform", "rotate(180deg)");
  }
});
// Click confirm list remove
$(".shopping-cart .shopping-cart-content-sidebar .product-confirm-item-action .product-confirm-item-remove").on("click", function () {
  $(this).closest(".product-confirm-item").remove();
});
// Biến lưu loại xe hiện tại
let currentVehicleType = "oto"; // Mặc định là ô tô

// Hàm load data theo loại xe từ API
function loadVehicleData(vehicleType) {
  // Hiển thị loading (nếu có)
  console.log("🔄 Loading data for vehicle type:", vehicleType);

  // Gọi API để lấy data
  $.ajax({
    url: `/api/vehicle-data/${vehicleType}`,
    method: "GET",
    success: function (response) {
      if (response.success && response.data) {
        // ✅ Cập nhật carModels và carYears (nếu có)
        if (response.data.carSearchData) {
          window.carModels = response.data.carSearchData.models || {};
          window.carYears = response.data.carSearchData.years || {};

          // ✅ Cập nhật danh sách manufacturers trong modal
          updateManufacturersList(response.data.carSearchData.manufacturers || []);

          console.log("✅ Car search data loaded:", {
            vehicleType: vehicleType,
            manufacturers: response.data.carSearchData.manufacturers,
            models: window.carModels,
            years: window.carYears,
          });
        } else {
          console.warn("⚠️ No carSearchData in response for vehicle type:", vehicleType);
        }

        // ✅ Cập nhật tireSizeData
        if (response.data.tireSizeData) {
          window.tireSizeCombinations = response.data.tireSizeData.combinations || {};

          // ✅ Cập nhật danh sách quy cách trong modal
          updateTireSizeLists(response.data.tireSizeData);

          console.log("✅ Tire size data loaded:", {
            vehicleType: vehicleType,
            wides: response.data.tireSizeData.wides,
            rates: response.data.tireSizeData.rates,
            diameters: response.data.tireSizeData.diameters,
            combinations_count: Object.keys(window.tireSizeCombinations).length,
          });
        } else {
          console.warn("⚠️ No tireSizeData in response for vehicle type:", vehicleType);
        }
      } else {
        console.warn("⚠️ Response success but no data:", response);
      }
    },
    error: function (xhr, status, error) {
      console.error("❌ Error loading vehicle data:", {
        vehicleType: vehicleType,
        status: status,
        error: error,
        responseText: xhr.responseText,
      });
      // Fallback: Giữ nguyên data hiện tại
    },
  });
}

// Hàm cập nhật danh sách manufacturers
function updateManufacturersList(manufacturers) {
  console.log("🔄 Updating manufacturers list:", manufacturers);

  let html = "";
  manufacturers.forEach(function (manufacturer) {
    html += `<div class="search-widget-popup-item manufacturer-item" data-value="${manufacturer}">${manufacturer}</div>`;
  });

  // Update vào modal MẪU XE - step 1
  const $brandList = $("#search-widget-type .search-widget-type-brand-list");
  // Chỉ xóa các items, giữ lại phần mb-3 (chứa nút Thoát)
  $brandList.find(".manufacturer-item").remove();

  if (html) {
    $brandList.append(html);
    console.log("✅ Updated manufacturers:", manufacturers.length, "items");
  } else {
    console.warn("⚠️ No manufacturers to update");
  }
}

// Hàm cập nhật danh sách quy cách
function updateTireSizeLists(tireSizeData) {
  console.log("🔄 Updating tire size lists:", tireSizeData);

  // Update độ rộng (step 1)
  let wideHtml = "";
  (tireSizeData.wides || []).forEach(function (wide) {
    wideHtml += `<div class="search-widget-popup-item" data-value="${wide}">${wide}</div>`;
  });

  const $wideList = $("#search-widget-size .search-widget-size-brand-list");
  // Chỉ xóa các items, giữ lại phần mb-3 (chứa nút Thoát)
  $wideList.find(".search-widget-popup-item").not(".search-widget-popup-close").remove();

  if (wideHtml) {
    $wideList.append(wideHtml);
    console.log("✅ Updated wides:", tireSizeData.wides);
  } else {
    console.warn("⚠️ No wides data to update");
  }

  // Update tỷ lệ (step 2)
  let rateHtml = "";
  (tireSizeData.rates || []).forEach(function (rate) {
    rateHtml += `<div class="search-widget-popup-item" data-value="${rate}">${rate}</div>`;
  });

  const $rateList = $("#search-widget-size .search-widget-size-model-list");
  // Chỉ xóa các items, giữ lại phần mb-3 (chứa nút Quay lại)
  $rateList.find(".search-widget-popup-item").not(".search-widget-popup-back").remove();

  if (rateHtml) {
    $rateList.append(rateHtml);
    console.log("✅ Updated rates:", tireSizeData.rates);
  } else {
    console.warn("⚠️ No rates data to update");
  }

  // Update đường kính (step 3) - sẽ được update động khi chọn tỷ lệ
  // Dựa vào combinations hierarchy
  if (tireSizeData.diameters && tireSizeData.diameters.length > 0) {
    let diameterHtml = "";
    tireSizeData.diameters.forEach(function (diameter) {
      diameterHtml += `<div class="search-widget-popup-item" data-value="${diameter}">${diameter}</div>`;
    });

    const $diameterList = $("#search-widget-size .search-widget-size-year-list");
    // Chỉ xóa các items, giữ lại phần mb-3 (chứa nút Quay lại)
    $diameterList.find(".search-widget-popup-item").not(".search-widget-popup-back").remove();

    if (diameterHtml) {
      $diameterList.append(diameterHtml);
      console.log("✅ Updated diameters:", tireSizeData.diameters);
    }
  }

  // Cập nhật combinations để dùng cho dynamic dropdown
  if (tireSizeData.combinations) {
    window.tireSizeCombinations = tireSizeData.combinations;
    console.log("✅ Updated tireSizeCombinations:", Object.keys(window.tireSizeCombinations).length, "wides");
  }
}

// Hàm ẩn/hiện nút MẪU XE
function toggleModelSearchButton(vehicleType) {
  const $mauXeBtn = $("#tim-mau-xe");

  if (vehicleType === "xe-tai") {
    // Ẩn nút MẪU XE khi chọn xe tải
    $mauXeBtn.hide();

    // Đảm bảo QUY CÁCH được active
    $(".list-btn-text .btn").removeClass("active");
    $('.list-btn-text .btn[data-value="productTypeSize"]').addClass("active");

    // Đóng modal MẪU XE nếu đang mở (sử dụng jQuery thay vì bootstrap instance)
    $("#search-widget-type").modal("hide");
  } else {
    // Hiện nút MẪU XE cho xe máy và ô tô
    $mauXeBtn.show();
  }
}

// Xử lý click chọn loại xe (xe máy, ô tô, xe tải)
$(".search-widget .list-btn-icon button").on("click", function (e) {
  e.preventDefault();
  e.stopPropagation(); // ✅ Ngăn các handler khác chạy

  // Remove active từ tất cả buttons
  $(".search-widget .list-btn-icon button").removeClass("active");
  // Add active cho button được click
  $(this).addClass("active");

  // Xác định loại xe được chọn
  const vehicleId = $(this).attr("id");
  let vehicleType = "oto";

  if (vehicleId === "chon-xe-may") {
    vehicleType = "xe-may";
  } else if (vehicleId === "chon-xe-tai") {
    vehicleType = "xe-tai";
  } else if (vehicleId === "chon-xe-oto") {
    vehicleType = "oto";
  }

  currentVehicleType = vehicleType;
  window.currentVehicleType = vehicleType; // Cập nhật biến global

  // ✅ Ẩn/hiện nút MẪU XE dựa trên loại xe (logic từ handler cũ)
  if (vehicleType === "xe-tai" || vehicleType === "xe-may") {
    // ✅ CHỈ ẩn nếu là xe tải (xe máy vẫn hiện)
    if (vehicleType === "xe-tai") {
      $("#tim-mau-xe").hide();
    } else {
      $("#tim-mau-xe").show();
    }
  } else {
    $("#tim-mau-xe").show();
  }

  // Ẩn/hiện nút MẪU XE dựa trên loại xe
  toggleModelSearchButton(vehicleType);

  // Load data theo loại xe
  loadVehicleData(vehicleType);

  // Reset về QUY CÁCH nếu đang ở MẪU XE và chọn xe tải
  if (vehicleType === "xe-tai") {
    const $mauXeBtn = $("#tim-mau-xe");
    if ($mauXeBtn.hasClass("active")) {
      // Chuyển sang QUY CÁCH
      $('.list-btn-text .btn[data-value="productTypeSize"]').click();
    }
  }
});

// Khởi tạo khi page load
$(document).ready(function () {
  // Kiểm tra loại xe mặc định và ẩn/hiện nút MẪU XE
  const defaultVehicleId = $(".list-btn-icon .btn.active").attr("id");
  let defaultVehicleType = "oto";

  if (defaultVehicleId === "chon-xe-tai") {
    toggleModelSearchButton("xe-tai");
    currentVehicleType = "xe-tai";
    defaultVehicleType = "xe-tai";
    window.currentVehicleType = "xe-tai";
  } else if (defaultVehicleId === "chon-xe-may") {
    currentVehicleType = "xe-may";
    defaultVehicleType = "xe-may";
    window.currentVehicleType = "xe-may";
  } else {
    window.currentVehicleType = "oto";
  }

  // Cập nhật biến global
  if (typeof window.currentVehicleType === "undefined") {
    window.currentVehicleType = defaultVehicleType;
  }

  // ✅ Load data cho loại xe mặc định khi page load
  // Đợi một chút để đảm bảo DOM đã sẵn sàng
  setTimeout(function () {
    loadVehicleData(defaultVehicleType);
  }, 100);
});
/**
 *
 * ///////////////END - Shopping Cart//////////////
 *
 **/

// ===================================
// Navigation Control - Điều hướng trang
// ===================================
$(document).on("click", ".nav-btn", function (e) {
  e.preventDefault();
  e.stopPropagation();

  const direction = $(this).data("direction");

  switch (direction) {
    case "up":
      // Cuộn về đầu trang
      $("html, body").animate(
        {
          scrollTop: 0,
        },
        500,
        "swing",
      );
      break;

    case "down":
      // Cuộn xuống cuối trang
      $("html, body").animate(
        {
          scrollTop: $(document).height() - $(window).height(),
        },
        500,
        "swing",
      );
      break;

    case "left":
      // Back về trang trước
      if (window.history.length > 1) {
        window.history.back();
      } else {
        // Nếu không có lịch sử, quay về trang chủ
        window.location.href = "/";
      }
      break;

    case "right":
      // Forward về trang sau
      if (window.history.length > 0) {
        window.history.forward();
      }
      break;
  }
});

///javascript toggle menu level 2 nested
$(document).on("click", ".nav-menu .menu-center .show-level-2", function (e) {
  e.preventDefault();
  e.stopPropagation();
  const $menuLevel2Nested = $(this).closest("li").find(".menu-level-2-nested");
  const isVisible = $menuLevel2Nested.hasClass("active");

  if (isVisible) {
    // Nếu đang hiển thị → Ẩn đi
    $menuLevel2Nested.removeClass("active");
  } else {
    // Nếu đang ẩn → Hiển thị
    // Đóng tất cả menu level 2 nested khác trong cùng level 2
    $(this).closest(".menu-level-2").find(".menu-level-2-nested").removeClass("active");

    // Hiện menu hiện tại
    $menuLevel2Nested.addClass("active");
  }
});

$(document).on("click", ".product-item", function (e) {
  // Giữ nguyên hành vi cho giỏ hàng và các phần tử tương tác
  if ($(e.target).closest(".add-to-cart-btn, .product-favourite, button, input, select, textarea, label").length) {
    return;
  }

  // Nếu click vào link có sẵn thì để browser xử lý như cũ
  if ($(e.target).closest("a").length) {
    return;
  }

  // Tìm link chi tiết sản phẩm theo thứ tự ưu tiên
  const detailUrl = $(this).find(".product-item-title a").attr("href") || $(this).find(".product-item-view a").attr("href") || $(this).find(".link-overlay").attr("href");

  if (detailUrl) {
    window.location.href = detailUrl;
  }
});
