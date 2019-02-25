$(function () {
    $(".icon-visible, .icon-invisible").on("click", (e) => {
        $(e.target).toggleClass("icon-visible");
        $(e.target).toggleClass("icon-invisible");

        let $input = $(e.target).siblings(".password");
        if ($input.attr("type") === "password") {
            $input.attr("type", "text");
        } else {
            $input.attr("type", "password");
        }
    });
});
