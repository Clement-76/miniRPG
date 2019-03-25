class Modal {
    /**
     * @param content
     * @param {array} modalClasses ['class1', 'class2']
     */
    constructor(content, modalClasses = []) {
        this.modalClasses = modalClasses;
        this.content = content;
        this.showModal();
    }

    showModal() {
        let modal = create('div', {class: 'modal'}, $('body')[0]);
        let overlay = create('div', {class: 'modal-overlay'}, modal);
        $(overlay).on('click', this.closeModal.bind(this));

        this.modalClasses.push('modal-content');
        let content = create('div', {class: this.modalClasses}, overlay);

        let closeContainer = create('div', {class: 'modal-close'}, content);
        let close = create('i', {class: 'icon-close'}, closeContainer);
        $(close).on('click', this.closeModal.bind(this));

        $(content).append(this.content);

        this.modal = modal;
    }

    closeModal(e = null) {
        if (e !== null) {
            if (e.target === e.currentTarget) {
                $(this.modal).remove();
            }
        } else {
            $(this.modal).remove();
        }
    }
}