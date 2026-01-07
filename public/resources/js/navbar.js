export default function navbarScript() {
  $(document).ready(() => {
    $(".navbar-light .dmenu").hover(
      function () {
        $(this).find(".sm-menu").first().stop(true, true).slideDown(550);
      },
      function () {
        $(this).find(".sm-menu").first().stop(true, true).slideUp(505);
      }
    );

    $(".megamenu").on("click", function (e) {
      e.stopPropagation();
    });
  });
}
