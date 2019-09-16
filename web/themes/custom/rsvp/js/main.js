(function ($, Drupal) {
    Drupal.behaviors.rsvpMain = {
        attach: function (context, settings) {
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