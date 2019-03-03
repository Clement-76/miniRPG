class Chat {

    /**
     * @param formId
     * @param messagesAreaId the area where there are all the messages
     * @param textareaId
     * @param ajaxPollingSeconds the number of seconds between each ajax request to get messages
     */
    constructor(formId, messagesAreaId, textareaId, ajaxPollingSeconds) {
        this.messages = $('#' + messagesAreaId);
        this.textareaId = textareaId;
        this.ajaxPollingSeconds = ajaxPollingSeconds * 1000;
        this.lastIdRetrieved = 0;
        this.getMessages();
        this.textareaHeight();

        $('#' + this.textareaId).on("keypress", this.enterDetection.bind(this));

        setInterval(() => {
            this.getMessages();
        }, this.ajaxPollingSeconds);

        $('#' + formId).on("submit", this.sendMessage.bind(this));
    }

    /**
     * auto resizes the textarea when the user writes inside
     */
    textareaHeight() {
        $('#' + this.textareaId).one('focus', function () {
            // 'this' refers to the element, not the object
            this.baseScrollHeight = this.scrollHeight;
        });

        $('#' + this.textareaId).on('input', function () {
            this.rows = 1; // base number of "rows" on the textarea
            let fontSize = parseInt($(this).css("font-size"));
            let rows = Math.floor((this.scrollHeight - this.baseScrollHeight) / fontSize);
            this.rows += rows; // the new number of rows
        });
    }

    /**
     * send a new message if the enter key is pressed but not the shift key
     * @param e the event object
     */
    enterDetection(e) {
        if (e.keyCode === 13) {
            if (!e.shiftKey) {
                this.sendMessage(e);
            }
        }
    }

    /**
     * gets all messages with ajax request
     */
    getMessages() {
        $.post("index.php?action=chat.getJSONChatMessages", {lastIdRetrieved: this.lastIdRetrieved}, (data) => {
            if (data.status === "success") {
                // if there are new messages
                if (data.messages !== 'nothing') {
                    data.messages.forEach((message) => {
                        this.displayMessage(message);
                    });

                    this.lastIdRetrieved = data.lastIdRetrieved;
                }
            } else {
                throw new Error(data.message);
            }
        }, "json");
    }

    /**
     * sends a new message with an ajax request
     * @param e the event object
     */
    sendMessage(e) {
        e.preventDefault();

        let emptyRegex = /^[\s]*$/;
        let message = $('#' + this.textareaId).val();

        if (!emptyRegex.test(message)) {
            $.post("index.php?action=chat.addMessage", {message: message}, (data) => {
                if (data.status === 'success') {
                    $('#' + this.textareaId).val("");
                    $('#' + this.textareaId).attr('rows', '1');
                    $('#' + this.textareaId).focus();

                    this.lastIdRetrieved = data.newMessage.id;
                    this.displayMessage(data.newMessage);
                } else {
                    console.error(data.message);
                    alert('Une erreur est survenue, veuillez réessayer');
                }
            }, "json");
        }
    }

    /**
     * displays a message in the messages area
     * @param message
     */
    displayMessage(message) {
        let $message;

        if (message.isOwner) {
            $message = $(`<div class="message me"></div>`);
        } else {
            $message = $(`<div class="message other"></div>`);
        }

        $message.append(`<span class="pseudo">${message.author}</span>`);
        $message.append(`<div class="content">${message.content}</div>`);

        // if the user is at the bottom of the messages div
        if (this.messages.scrollTop() + this.messages.innerHeight() === this.messages.prop('scrollHeight')) {
            this.messages.append($message);
            this.messages.scrollTop(this.messages.prop('scrollHeight'));
        } else {
            this.messages.append($message);
        }
    }
}
