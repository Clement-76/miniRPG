class Chat {
    /**
     * @param formId
     * @param messagesAreaId the area where there are all the messages
     * @param textareaId
     */
    constructor(formId, messagesAreaId, textareaId) {
        this.messages = $('#' + messagesAreaId);
        this.textareaId = textareaId;
        this.getMessages();
        this.textareaHeight();

        setInterval(() => {
            this.getMessages();
        }, 5000);

        $('#' + formId).on("submit", this.sendMessage.bind(this));
    }

    /**
     * auto resizes the textarea when the user writes inside
     */
    textareaHeight() {
        $('#' + this.textareaId).one('focus', function() {
            // 'this' refers to the element, not the object
            this.baseScrollHeight = this.scrollHeight;
        });

        $('#' + this.textareaId).on('input', function() {
            this.rows = 1; // base number of "rows" on the textarea
            let fontSize = parseInt($(this).css("font-size"));
            let rows = Math.floor((this.scrollHeight - this.baseScrollHeight) / fontSize);
            this.rows += rows; // the new number of rows
        });
    }

    /**
     * gets all messages with ajax request
     */
    getMessages() {
        $.get("index.php?action=chat.getJSONChatMessages", (data) => {
            data = JSON.parse(data);

            if (data[0] === "success") {
                let messages = data[1];

                messages.forEach((message) => {
                    this.displayMessage(message);
                });
            } else {
                throw new Error(data[1]);
            }
        });
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
            $.post("index.php?action=chat.createMessage", {message: message}, (data) => {
                if (data[0] === 'success') {
                    $('#' + this.textareaId).val("");
                    let newMessage = data[1];
                    this.displayMessage(newMessage);
                } else {
                    console.error(data[1]);
                    alert('Une erreur est survenue, veuillez réessayer');
                }
            }, "json");
        } else {
            $('#' + this.textareaId)[0].setCustomValidity("Veuillez entrer un message");
        }
    }

    /**
     * displays a message in the messages area
     * @param message
     */
    displayMessage(message) {
        let $message = $(`<div class="message"></div>`);
        $message.append(`<p>${message.author}</p>`);
        $message.append(`<span>${message.content}</span>`);

        // add that if the pseudo === the player.pseudo
        // add the class "me" else add "other"

        // if the user is at the bottom of the messages div
        if (this.messages.scrollTop() + this.messages.innerHeight() === this.messages.prop('scrollHeight')) {
            this.messages.append($message);
            this.messages.scrollTop(this.messages.prop('scrollHeight'));
        } else {
            this.messages.append($message);
        }
    }
}
