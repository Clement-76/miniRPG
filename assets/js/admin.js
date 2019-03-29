$(function () {
    $('#open-admin-menu-btn').on('click', function () {
        $('#admin-menu').toggle();
    });

    $(document).on("click", function (e) {
        let trigger = $("#open-admin-menu-btn")[0];
        let adminMenu = $("#admin-menu")[0];

        // hide the menu if we click outside the menu or on the button to open it
        if (adminMenu !== e.target && trigger !== e.target && !trigger.contains(e.target)) {
            $("#admin-menu").hide();
        }
    });

    new StuffAdmin('stuff-admin-container', 'create-stuff');
    new UsersAdmin('users-admin-container');
});
