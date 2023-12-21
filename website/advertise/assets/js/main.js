(function ($) {
  "use strict";

  // countdown-jquery
  $("#countdown").countdown(
    {
      date: "31 maret 2024 09:00:00" /*Change your time here*/,
      format: "on",
    },
    function () {
      // callback function
    }
  );

  // background-start
  $("[data-background]").each(function () {
    $(this).css(
      "background-image",
      "url(" + $(this).attr("data-background") + ")"
    );
  });

  // preloder-heare
  var loader = document.getElementById("preloader");
  window.addEventListener("load", function () {
    loader.style.display = "none";
  });

  // contact-form-sidebar
  $(".contact-form-ep").on("click", function () {
    $(".contact-form-open").addClass("active");
  });

  $(".contact-form-close").on("click", function () {
    $(".contact-form-open").removeClass("active");
  });
})(jQuery);
