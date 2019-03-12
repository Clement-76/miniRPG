$(function() {
    let contentHeight = $('header.top').height() + $('footer.menu').height();
    $('#content').css('height', `calc(100% - ${contentHeight}px)`);

    $('.window').each((i, elt) => {
        let windowClass = elt.className.replace('window', '');
        let window = new WindowRPG(windowClass, 'content');
    });

    let user = new User();
    let chat = new Chat('chat-form', 'chat-messages', 'user-message', 1);
});
