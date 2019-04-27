class Arena {
    /**
     * @param {string} playersContainerId
     */
    constructor(playersContainerId, remainingBattlesContainerId, timerId, userObj) {
        this.playersContainer = $('#' + playersContainerId);
        this.remainingBattlesContainer = $('#' + remainingBattlesContainerId);
        this.timer = $('#' + timerId);
        this.userObj = userObj;
        this.playersInArena;
        this.attemptTimerInProgress = false;

        this.displayRemainingBattles();
        this.displayAllPlayers();
        this.updateAttempts();
    }

    /**
     * @param data
     */
    displayAllPlayers() {
        this.getPlayers((data) => {
            this.playersContainer.html('');

            // if there is players to fight
            if (data.players !== false) {
                this.playersInArena = data.players.length;
                data.players.forEach(player => this.displayPlayer(player));
            } else {
                this.playersContainer.append(`<p class="empty">Aucun combattant dans l'arène</p>`);
                this.playersInArena = 0;
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
                    this.updateAttempts(data, false);
                    // this.displayRemainingBattles();

                    let logs = create('div', {class: 'logs'});
                    create('p', {class: 'text', text: 'Le combat commence'}, logs)
                    let logsModal = new Modal(logs);

                    let logsLength = data.logs.length;
                    let i = 0;

                    if (data.win) {
                        this.userObj.xp = data.xp;
                        this.userObj.displayUserStats();
                        player.playerDivElt.remove();
                        this.playersInArena--;

                        if (this.playersInArena === 0) {
                            this.displayAllPlayers();
                        }
                    }

                    let logsInterval = setInterval(() => {
                        let text;

                        if (i === logsLength) {
                            clearInterval(logsInterval);

                            if (data.win) {
                                text = create('p', {class: 'status',
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

        for (let i = 1; i <= this.userObj.maxRemainingBattles; i++) {
            if (i > this.userObj.remainingBattles) {
                create('div', {class: 'square'}, this.remainingBattlesContainer[0]);
            } else {
                // if there is remaining battles
                create('div', {class: ['square', 'full']}, this.remainingBattlesContainer[0]);
            }
        }

        // this.setBattlesTimer();
    }

    setBattlesTimer() {
        if (!this.attemptTimerInProgress) {
            let userHasMaxRemainingBattles = this.userObj.remainingBattles === this.userObj.maxRemainingBattles;

            if (!userHasMaxRemainingBattles && this.userObj.newAttemptTimerStartTime !== null) {
                let timeElapsed = Math.floor((Date.now() - this.userObj.newAttemptTimerStartTime) / 1000);
                let remainingSeconds = this.userObj.timeToRecoverAnAttempt - timeElapsed;
                this.attemptTimerInProgress = true;
                this.timer.text(`(+1 dans ${secondsFormat(remainingSeconds, 'm:s')})`);

                let timer = setInterval(() => {
                    if (remainingSeconds > 1) {
                        remainingSeconds--;
                        this.timer.text(`(+1 dans ${secondsFormat(remainingSeconds, 'm:s')})`);
                    } else {
                        clearInterval(timer);
                        this.updateAttempts();
                        this.attemptTimerInProgress = false;
                        let userHasMaxRemainingBattles = this.userObj.remainingBattles === this.userObj.maxRemainingBattles;

                        if (!userHasMaxRemainingBattles) this.timer.text('Tentatives restaurées');
                    }
                }, 1000);
            } else {
                this.timer.text('Tentatives restaurées');
            }
        }
    }

    updateAttempts(data = null, doAjaxRequest = true) {
        let updateAttemptsData = (data) => {
            if (typeof data.newAttemptTimerStartTime !== 'undefined') {
                if (data.newAttemptTimerStartTime !== null) {
                    this.userObj.newAttemptTimerStartTime = new Date(data.newAttemptTimerStartTime);
                } else {
                    this.userObj.newAttemptTimerStartTime = null;
                }
            }

            if (data.remainingBattles !== this.maxRemainingBattles) this.setBattlesTimer();

            if (typeof data.remainingBattles !== 'undefined') {
                this.userObj.remainingBattles = data.remainingBattles;
            }

            this.displayRemainingBattles();
        }

        if (data !== null && doAjaxRequest === false) {
            updateAttemptsData(data);
        } else if (data === null && doAjaxRequest === true) {
            $.post(baseUrl + 'Users/updateUserAttempts', (data) => {
                if (data.status === 'success') {
                    updateAttemptsData(data);
                } else {
                    console.error(data.message);
                }
            }, 'json');
        } else {
            throw new Error('Incorrect parameters value value(s)');
        }
    }
}
