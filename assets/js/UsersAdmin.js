class UsersAdmin {
    /**
     * @param usersAdminContainerId
     */
    constructor(usersAdminContainerId) {
        this.usersContainer = $('#' + usersAdminContainerId);

        this.getUsers((data) => {
            data.users.forEach((user) => {
                this.displayUser(user);
            });
        });
    }

    /**
     * makes ajax request and executes callback function if successful
     * @param callback
     */
    getUsers(callback) {
        $.get(baseUrl + 'Users/getJSONUsers', (data) => {
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
        user.bannedElt = create('td', {class: 'user-banned', text: user.banned === true ? 'Oui' : 'Non'}, userTr);

        let warnContainer = create('td', {class: 'warn', text: 'Avertir '}, userTr);
        create('i', {class: ['fas', 'fa-exclamation-triangle']}, warnContainer);
        $(warnContainer).on('click', this.warnUser.bind(this, user));

        let banContainer = create('td', {class: 'ban', text: 'Bannir '}, userTr);
        create('i', {class: ['fas', 'fa-user-times']}, banContainer);
        $(banContainer).on('click', this.banUser.bind(this, user));

        user.adminHTMLElt = userTr;
    }

    /**
     * warn the user and if successfull change the warnings value of the user in the admin panel
     * @param user
     */
    warnUser(user) {
        if (confirm('Êtes-vous sûr de vouloir ajouter un avertissement à ce joueur ?')) {
            $.post(baseUrl + 'Users/warnUser', {userId: user.id}, (data) => {
                if (data.status === 'success') {
                    user.warnings++;
                    user.warningsElt.textContent = user.warnings;
                } else {
                    console.error(data.message);
                }
            }, 'json');
        }
    }

    /**
     * ban the user and if successful change the value of the column banned of the user in the panel
     * @param user
     */
    banUser(user) {
        if (confirm('Êtes-vous sûr de vouloir bannir ce joueur ?')) {
            $.post(baseUrl + 'Users/banUser', {userId: user.id}, (data) => {
                if (data.status === 'success') {
                    user.banned = true;
                    user.bannedElt.textContent = 'Oui';
                } else {
                    console.error(data.message);
                }
            }, 'json');
        }
    }
}
