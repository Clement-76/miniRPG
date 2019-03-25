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

    let userObj = new User();
    let chatObj = new Chat('chat-form', 'chat-messages', 'user-message', 1);
    let adventuresObj = new Adventures('adventures', 'adventures-admin-container', userObj);
});
