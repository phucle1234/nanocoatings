$(document).ready(function(){
    $(".preloader").fadeOut();

    /*--------- GO TO TOP ---------*/
    $(".to-top").on("click", function (a) {
      a.preventDefault();
      $("html, body").animate({
          scrollTop: 0
      }, 800);
      return false;
    });
    // scroll animation ------------------
    $(window).on("scroll", function (a) {
      if ($(this).scrollTop() > 150) {
          $(".to-top").fadeIn(500);
      } else {
          $(".to-top").fadeOut(500);
      }
    });

    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    })

    $("#show-form-search").click(function(){
      $("#box-form-search").fadeToggle("slow");
      $(this).toggleClass('close-form');
    });

    $(".list-btn-check .btn-check").click(function(){
      $(".list-btn-check .btn-check").removeClass("active");
      $(this).addClass("active");
    });

    $("#show-account-block-mb").click(function(){
      $("#account-block-mb-show").fadeToggle("slow");
      $(this).toggleClass("active");
    });

    // wallet ------------------
    $("#deposit_tab-tab").on('click', function(e) {
      $(".table-history").hide();
      $("#history-deposit").fadeIn();
    });
    $("#transfer_tab-tab").on('click', function(e) {
      $(".table-history").hide();
      $("#history-transfer").fadeIn();
    });
    $("#withdraw_tab-tab").on('click', function(e) {
      $(".table-history").hide();
      $("#history-withdraw").fadeIn();
    });
});
