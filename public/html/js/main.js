"use strict";
(function ($) {
  $(document).ready(function () {
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

    // Initial check
    checkScroll();

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
      // variableWidth: true,
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
      autoplaySpeed: 5000,
    });
    $(".products-sales-slider").slick({
      slidesToShow: 3,
      // slidesToScroll: 1,
      arrows: false,
      dots: true,
      speed: 300,
      infinite: true,
      autoplaySpeed: 5000,
      // variableWidth: true,
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
      // autoplaySpeed: 5000,
      // loop: true,
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
      var target = $(e.target).attr("href");
      var slider = $(target).find(".box-category-slider");

      // Khởi tạo slider cho tab này
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
    });

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
      var target = $(e.target).attr("href");
      var slider = $(target).find(".box-media-slider");
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
    });

    $(".category-banner-design-slider").slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      dots: true,
      speed: 300,
      infinite: true,
      autoplaySpeed: 5000,
      loop: true,
    });

    $(".category-why-slider").slick({
      slidesToShow: 1,
      slidesToScroll: 1,
      arrows: false,
      dots: true,
      speed: 300,
      infinite: true,
      autoplaySpeed: 5000,
      loop: true,
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
        $(".slick-slider").slick("setPosition");
      }, 100);
    });

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
    // let isScrolling = false;

    // $(window).scroll(function () {
    //   if (!isScrolling) {
    //     requestAnimationFrame(function () {
    //       if ($(window).scrollTop() > 156) {
    //         $(".header:not(.bg-white)").addClass("fixed");
    //       } else {
    //         $(".header:not(.bg-white)").removeClass("fixed");
    //       }
    //       if ($(window).scrollTop() >= $(window).height()) {
    //         $(".header:not(.bg-white)").addClass("header-secondary");
    //         if ($(".search-widget").hasClass("search-widget-secondary")) {
    //           if ($(window).width() > 991) {
    //             $(".search-widget").addClass("fixed-top");
    //           } else {
    //             $(".search-widget").removeClass("fixed-top");
    //           }
    //           $(".category-search").css("padding-top", "195px");
    //           $(".category-search .breadcrumb-dark").removeClass("d-none");
    //         }
    //       } else {
    //         $(".header:not(.bg-white)").removeClass("header-secondary");
    //         if ($(".search-widget").hasClass("search-widget-secondary")) {
    //           $(".search-widget").removeClass("fixed-top");
    //           $(".category-search").css("padding-top", "40px");
    //           $(".category-search .breadcrumb-dark").addClass("d-none");
    //         }
    //       }

    //       isScrolling = false;
    //     });
    //     isScrolling = true;
    //   }
    // });
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
    //     $(".header:not(.bg-white)").addClass("fixed");
    //   } else {
    //     $(".header:not(.bg-white)").removeClass("fixed");
    //   }

    //   if ($(window).scrollTop() >= $(window).height()) {
    //     $(".header:not(.bg-white)").addClass("header-secondary");
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
    //     $(".header:not(.bg-white)").removeClass("header-secondary");
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
    });

    $(".nav-menu .close-menu").click(function () {
      $(".nav-menu").css("left", "-100%");
      $(".menu-overlay").removeClass("active");
      $("body").removeClass("menu-open");
    });

    // Đóng menu khi click vào overlay
    $(document).on("click", ".menu-overlay", function () {
      $(".nav-menu").css("left", "-100%");
      $(".menu-overlay").removeClass("active");
      $("body").removeClass("menu-open");
    });

    /**
     *
     * Handle search widget
     *
     **/
    $(".search-widget .list-btn-icon button").click(function () {
      $(".search-widget .list-btn-icon button").removeClass("active");
      $(this).addClass("active");
    });
    $(".search-widget .list-btn-text button").click(function () {
      $(".search-widget .list-btn-text button").removeClass("active");
      $(this).addClass("active");
    });

    // Handle modal search with step
    const searchWidgetType = bootstrap.Modal.getOrCreateInstance("#search-widget-type");
    const searchWidgetSize = bootstrap.Modal.getOrCreateInstance("#search-widget-size");
    let searchWidgetPopupName = "";
    $(document).on("focus", ".search-widget .search-widget-input", function () {
      const popupType = $(".search-widget .list-btn-text button.active").attr("data-value");
      if (popupType === "productTypeCar") {
        searchWidgetType.show();
        searchWidgetPopupName = "searchWidgetType";
      }
      if (popupType === "productTypeSize") {
        searchWidgetSize.show();
        searchWidgetPopupName = "searchWidgetSize";
      }
    });

    let selectedStep1 = "";
    let selectedStep2 = "";
    let selectedStep3 = "";
    // Ẩn tất cả các step khác ngoại trừ step 1 khi modal mở
    $(".search-widget-popup").on("show.bs.modal", function () {
      $(".search-widget-popup .search-widget-list").addClass("d-none");
      $(".search-widget-popup .search-widget-list[data-step-target=1]").removeClass("d-none");
      $(".search-widget-popup .search-widget-popup-title").removeClass("active");
      $('.search-widget-popup .search-widget-popup-title[data-step="1"]').addClass("active");
    });

    // Xử lý click vào item của brand (step 1)
    $(document).on("click", ".search-widget-popup .search-widget-list[data-step-target=1] .search-widget-popup-item", function () {
      selectedStep1 = $(this).text();
      // Ẩn step 1, hiện step 2
      $(".search-widget-popup .search-widget-list").addClass("d-none");
      $(".search-widget-popup .search-widget-list[data-step-target=2]").removeClass("d-none");

      // Cập nhật title active
      $(".search-widget-popup .search-widget-popup-title").removeClass("active");
      $('.search-widget-popup .search-widget-popup-title[data-step="2"]').addClass("active");
    });

    // Xử lý click vào item của model (step 2)
    $(document).on("click", ".search-widget-popup .search-widget-list[data-step-target=2] .search-widget-popup-item", function () {
      selectedStep2 = $(this).text();

      // Ẩn step 2, hiện step 3
      $(".search-widget-popup .search-widget-list").addClass("d-none");
      $(".search-widget-popup .search-widget-list[data-step-target=3]").removeClass("d-none");

      // Cập nhật title active
      $(".search-widget-popup .search-widget-popup-title").removeClass("active");
      $('.search-widget-popup .search-widget-popup-title[data-step="3"]').addClass("active");
    });

    // Xử lý click vào item của year (step 3) - hoàn thành và đóng modal
    $(document).on("click", ".search-widget-popup .search-widget-list[data-step-target=3] .search-widget-popup-item", function () {
      selectedStep3 = $(this).text();
      if (searchWidgetPopupName === "searchWidgetType") {
        searchWidgetType.hide();
        console.log(`Tìm kiếm theo: Loại Xe`);
        $(".search-widget .search-widget-input").val(`Thương hiệu: ${selectedStep1}, Mẫu xe: ${selectedStep2}, Năm: ${selectedStep3}`);
      }
      if (searchWidgetPopupName === "searchWidgetSize") {
        searchWidgetSize.hide();
        console.log(`Tìm kiếm theo: Kích cỡ`);
        $(".search-widget .search-widget-input").val(`Độ rộng: ${selectedStep1}, Tỷ lệ: ${selectedStep2}, Đường kính mâm xe: ${selectedStep3}`);
      }
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

    /**
     *
     * Shopping Cart
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
  });
})(jQuery);

/* ============================================
   javascript Contact Icon với Popup - hotline - zalo
   ============================================ */
document.addEventListener("DOMContentLoaded", function () {
  const contactIcon = document.getElementById("contactIcon");
  const contactPopup = document.getElementById("contactPopup");

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
