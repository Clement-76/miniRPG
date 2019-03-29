class UsersAdmin {
    /**
     * @param usersAdminContainerId
     */
    constructor(usersAdminContainerId) {
        this.usersContainer = $('#' + usersAdminContainerId);

        this.getUsers((data) => {
            data.users.forEach((user) => {
                console.log(user)
                this.displayUser(user);
            });
        });
    }

    /**
     * makes ajax request and executes callback function if successful
     * @param callback
     */
    getUsers(callback) {
        $.get('index.php?action=users.getJSONUsers', (data) => {
            if (data.status === 'success') {
                callback(data);
            } else {
                console.error(data.message);
            }
        }, 'json');
    }

    /**
     * displays user in the admin panel
     * @param user
     */
    displayUser(user) {
        let userTr = create('tr', null, this.usersContainer[0]);
        user.pseudoElt = create('td', {class: 'user-pseudo', text: user.pseudo}, userTr);
        user.lvlElt = create('td', {class: 'user-lvl', text: user.lvl}, userTr);
        user.registrationDateElt = create('td', {class: 'user-registration-date', text: user.registrationDate}, userTr);
        user.dollarsElt = create('td', {class: 'user-dollars', text: user.dollars}, userTr);
        user.tElt = create('td', {class: 'user-t', text: user.T}, userTr);
        user.warningsElt = create('td', {class: 'user-warnings', text: user.warnings}, userTr);
        user.bannedElt = create('td', {class: 'user-banned', text: user.banned === 'true' ? 'Oui' : 'Non'}, userTr);

        let warnContainer = create('td', {class: 'warn', text: 'Avertir '}, userTr);
        create('i', {class: ['fas', 'fa-exclamation-triangle']}, warnContainer);
        $(warnContainer).on('click', this.warnUser.bind(this, user));

        let banContainer = create('td', {class: 'ban', text: 'Bannir '}, userTr);
        create('i', {class: ['fas', 'fa-user-times']}, banContainer);
        $(banContainer).on('click', this.banUser.bind(this, user));

        user.adminHTMLElt = userTr;
    }

    warnUser() {

    }

    banUser() {

    }
}
