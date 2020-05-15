/**
 * jQuery scrollTopTop function
 */
(function($) {
    $.fn.scrollToTop = function(options) {
        var config = {
            "speed" : 800
        };

        if (options) {
            $.extend(config, {
                "speed" : options
            });
        }

        return this.each(function() {

            var $this = $(this);

            $(window).scroll(function() {
                if ($(this).scrollTop() > 100) {
                    $this.fadeIn();
                } else {
                    $this.fadeOut();
                }
            });

            $this.click(function(e) {
                e.preventDefault();
                $("body, html").animate({
                    scrollTop : 0
                }, config.speed);
            });

        });
    };
})(jQuery);

$(function() {
    $("#toTop").scrollToTop();
});