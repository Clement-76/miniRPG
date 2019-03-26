$(function() {
    let contentHeight = $('header.top').height() + $('footer.menu').height();
    $('#content').css('height', `calc(100% - ${contentHeight}px)`);

    $('.window').each((i, elt) => {
        let windowClass = elt.className.replace('window', '');
        let window = new WindowRPG(windowClass, 'content');
    });

    $('#open-admin-menu-btn').on('click', function() {
        $('#admin-menu').toggle();
    });

    $(document).on("click", function(e) {
        let trigger = $("#open-admin-menu-btn")[0];
        let adminMenu = $("#admin-menu")[0];

        // hide the menu if we click outside the menu or on the button to open it
        if (adminMenu !== e.target && trigger !== e.target && !trigger.contains(e.target)) {
            $("#admin-menu").hide();
        }
    });

    let userObj = new User();
    let chatObj = new Chat('chat-form', 'chat-messages', 'user-message', 1);
    let adventuresObj = new Adventures('adventures', 'adventures-admin-container', 'create-adventure', userObj);
});
