$(function() {
    $(".btn-group button").on("click", function() {
        if (!$(this).hasClass("active")) {
            let $active = $(".group > .active");
            let $inactive = $(".group > :not(.active)");

            $active.hide().removeClass("active");
            $inactive.show().addClass("active");
        }

        $(this).addClass("active").siblings().removeClass("active");
    });
});
