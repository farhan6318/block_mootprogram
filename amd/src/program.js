
define(['core/config', 'jquery'], function (config, $) {
    return {
        init: function () {
            localStorage.setItem('needesreloading', 'false');
            $('body').on('click', '.star-button', function (e) {
                e.preventDefault();
                var sessionId = $(this).attr('data-sessionid');
                var span = $(this).find("span");
                var wwwRoot = config.wwwroot + "/blocks/mootprogram/starsession.php";

                var args = {
                    session: sessionId,
                    noredirect: 1
                };

                $.ajax({
                    method: "GET",
                    url: wwwRoot,
                    data: args,
                    dataType : "JSON",
                    success: function() {
                        span.toggleClass('session-starred');
                        localStorage.setItem('needesreloading', 'true');
                    },
                    error: function() {
                    }
                });
            });
            $('.nav-tabs a').on('show.bs.tab', function() {
                if ($(this).hasClass('starpagetab')) {
                    if ((localStorage.getItem('needesreloading') == 'true')) {
                        location.hash = $(this).attr('href');
                        location.reload();
                    }
                }
            });
        }
    };
});