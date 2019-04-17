class Arena {
    constructor(playersContainerId) {
        this.playersContainer = $('#' + playersContainerId);
        this.getPlayers((data) => {
            if (data.players !== false) {
                data.players.forEach(player => this.displayPlayer(player));
            } else {
                this.playersContainer.append(`<p class="empty">Aucun combattant dans l'arène</p>`);
            }
        });
    }

    getPlayers(callback) {
        $.get(baseUrl + 'Users/getJSONUsersWithLvlDifference', (data) => {
            if (data.status === 'success') {
                callback(data);
            } else {
                console.error(data.message);
            }
        }, 'json');
    }

    displayPlayer(player) {
        let playerDiv = create('div', {class: 'player'}, this.playersContainer[0]);
        create('p', {class: 'player-info', text: `${player.pseudo} (lvl ${player.lvl})`}, playerDiv);
        create('button', {class: 'fight-btn', text: 'Combattre'}, playerDiv);
    }
}
