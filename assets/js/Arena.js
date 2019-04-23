class Arena {
    /**
     * @param {string} playersContainerId
     */
    constructor(playersContainerId, remainingBattlesContainerId, timerId, userObj, maxBattles) {
        this.playersContainer = $('#' + playersContainerId);
        this.remainingBattlesContainer = $('#' + remainingBattlesContainerId);
        this.timer = $('#' + timerId);
        this.userObj = userObj;
        this.maxBattles = maxBattles;

        this.displayRemainingBattles();
        this.displayAllPlayers();
    }

    /**
     * @param data
     */
    displayAllPlayers() {
        this.getPlayers((data) => {
            this.playersContainer.html('');

            // if there is players to fight
            if (data.players !== false) {
                data.players.forEach(player => this.displayPlayer(player));
            } else {
                this.playersContainer.append(`<p class="empty">Aucun combattant dans l'arène</p>`);
            }
        });
    }

    /**
     * do an ajax request to get players who have
     * about the same lvl than the user
     * @param callback the function to call in case of success
     */
    getPlayers(callback) {
        $.get(baseUrl + 'Users/getJSONUsersWithLvlDifference', (data) => {
            if (data.status === 'success') {
                callback(data);
            } else {
                console.error(data.message);
            }
        }, 'json');
    }

    /**
     * displays the player in the players container in the arena window
     * @param player
     */
    displayPlayer(player) {
        player.playerDivElt = create('div', {class: 'player'}, this.playersContainer[0]);
        create('p', {class: 'player-info', text: `${player.pseudo} (lvl ${player.lvl})`}, player.playerDivElt);
        let fightBtn = create('button', {class: 'fight-btn', text: 'Combattre'}, player.playerDivElt);

        $(fightBtn).on('click', this.fightPlayer.bind(this, player));
    }

    /**
     * @param player
     */
    fightPlayer(player) {
        if (this.userObj.remainingBattles > 0) {

            $.post(baseUrl + 'Users/fightUser', {userId: player.id}, (data) => {
                if (data.status === 'success') {
                    this.userObj.remainingBattles--;
                    this.displayRemainingBattles();

                    let logs = create('div', {class: 'logs'});
                    create('p', {class: 'text', text: 'Le combat commence'}, logs)
                    let logsModal = new Modal(logs);

                    let logsLength = data.logs.length;
                    let i = 0;

                    if (data.win) {
                        this.userObj.xp = data.xp;
                        this.userObj.displayUserStats();
                        player.playerDivElt.remove();
                    }

                    let logsInterval = setInterval(() => {
                        let text;

                        if (i === logsLength) {
                            clearInterval(logsInterval);

                            if (data.win) {
                                text = create('p', {
                                    class: 'status',
                                    innerHTML: `Victoire ! Vous avez gagné <b class="underline">${data.xpGained}</b> points d'xp`
                                });
                            } else {
                                text = create('p', {class: 'status', text: `Défaite`});
                            }
                        } else {
                            text = create('p', {class: ['text', 'hide'], text: data.logs[i]});
                            i++;
                        }

                        // if the user is at the bottom of the logs container
                        if (logsModal.status === 'open' && $(logs).scrollTop() + $(logs).innerHeight() === $(logs).prop('scrollHeight')) {
                            $(logs).append(text);
                            $(logs).scrollTop($(logs).prop('scrollHeight'));
                        } else {
                            $(logs).append(text);
                        }
                    }, 800);
                } else {
                    if (data.messages) {
                        data.messages.forEach(message => console.error(message));
                    } else {
                        console.error(data.message);
                    }
                }
            }, 'json');
        } else {
            new Modal(create('p', {class: 'info-message', text: `Vous n'avez plus de tentatives disponibles`}));
        }
    }

    displayRemainingBattles() {
        this.remainingBattlesContainer.html('');

        for (let i = 1; i <= this.maxBattles; i++) {
            if (i > this.userObj.remainingBattles) {
                create('div', {class: 'square'}, this.remainingBattlesContainer[0]);
            } else {
                // if there is remaining battles
                create('div', {class: ['square', 'full']}, this.remainingBattlesContainer[0]);
            }
        }

        this.setBattlesTimer();
    }

    setBattlesTimer() {
        // determiner l'avancement du timer
        if (this.userObj.remainingBattles < this.maxBattles) {
            this.timer.text('(+1 dans 38:12)');
        } else {
            this.timer.text('Tentatives restaurées');
        }
    }
}
