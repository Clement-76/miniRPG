class WindowRPG {
    constructor(windowClass, contentId) {
        this.windowClass = windowClass;
        this.window = $(`.window.${windowClass}`);
        this.openBtn = $('#' + this.windowClass);
        this.closeBtn = $(`.${windowClass} .icon-close`);
        this.windowTopBar = $(`.${windowClass} > h2`);
        this.content = $('#' + contentId);

        this.openBtn.on('click', this.open.bind(this));
        this.closeBtn.on('click', this.close.bind(this));
        this.windowTopBar.on('mousedown', this.grab.bind(this));
        this.window.on('mousedown', this.bringForward.bind(this));
    }

    bringForward() {
        $('.window').each((i, elt) => {
            let opacityValue = '0.6';
            let windowClass = elt.className.replace('window', '');
            $('#' + windowClass).removeClass('active');

            if ($(elt).css('z-index') > 0) {
                $(elt).css('z-index', parseFloat($(elt).css('z-index')) - 1);
            }

            $(`.${windowClass} .window-title`).css('opacity', opacityValue);
            $(`.${windowClass} .window-content`).css('opacity', opacityValue);
        });

        this.window.children('.window-title, .window-content').css('opacity', '1');

        this.window.css('z-index', '100');
        this.openBtn.addClass('active');
    }

    open() {
        this.window.show();
        this.bringForward();
        this.openBtn.addClass('active');
        this.openBtn.addClass('open');
    }

    close() {
        this.window.hide();
        this.openBtn.removeClass('open');
        this.openBtn.removeClass('active');
    }

    grab(e) {
        // if the target is the element that has the event attach
        if (e.target === e.currentTarget) {
            // initial position of the mouse
            this.pageX = e.pageX;
            this.pageY = e.pageY;

            this.content.on('mousemove', this.move.bind(this));

            $(document).on('mouseup', () => {
                this.content.off('mousemove');
            });
        }
    }

    move(e) {
        let valX = e.pageX - this.pageX;
        let valY = e.pageY - this.pageY;

        this.window.css('left', parseFloat(this.window.css('left')) + valX);
        this.window.css('top', parseFloat(this.window.css('top')) + valY);
        this.window.css('bottom', 'auto');
        this.window.css('right', 'auto');

        this.pageX = e.pageX;
        this.pageY = e.pageY;
    }
}
