(function ($, Drupal) {
    Drupal.behaviors.rsvpMain = {
        attach: function (context, settings) {

            // Toggle functionality for mobile menu.
            $(".mobile-menu").on('click', function() {
                $("nav.menu--main").toggle();
                $(".mobile-menu-close").toggle();
                $(".mobile-menu-open").toggle();
                $("body").toggleClass("menu--main--active");
                $("header").toggleClass("menu--main--active");
                $("footer").toggleClass("menu--main--active");
                $("div.mobile-menu").toggleClass("menu--main--active");
            });

            // Shadow effect for card.
            $( ".card" ).hover(
                function() {
                    $(this).addClass('shadow').css('cursor', 'pointer');
                }, function() {
                    $(this).removeClass('shadow');
                }
            );
        }
    };
})(jQuery, Drupal);
