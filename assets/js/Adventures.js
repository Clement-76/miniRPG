class Adventures {
    /**
     * @param adventuresContainerId
     * @param userObj
     */
    constructor(adventuresContainerId, userObj) {
        this.adventures = [];
        this.currentAdventure = null;
        this.user = userObj;
        this.adventuresContainer = $('#' + adventuresContainerId);
        this.getAdventures();
    }

    /**
     * retrieves adventures with ajax request and execute code in case of success
     */
    getAdventures() {
        $.get("index.php?action=adventures.getJSONAdventures", (data) => {
            if (data.status === "success") {
                data.adventures.forEach((adventure) => {
                    this.displayAdventure(adventure);
                    this.adventures.push(adventure);

                    // if the user is currently in an adventure, we store it in a property
                    if (this.user.currentAdventureId === adventure.id) {
                        this.currentAdventure = adventure;
                    }
                });

                let adventure = this.checkAdventureStatus();

                if (adventure === 'end') {
                    this.adventureCompleted();
                } else if (adventure === true) {
                    //-> timer en deduisant le temps deja écoulé ---------------------------
                    this.adventureTimer();
                }
            } else {
                console.error(data.message);
            }
        }, "json");
    }

    /**
     * creates the HTML code and display an adventure in the adventures window
     * @param adventure
     */
    displayAdventure(adventure) {
        let adventureDiv = create('div', {class: 'adventure'}, this.adventuresContainer[0]);
        create('p', {text: `Nom: ${adventure.name}`}, adventureDiv);
        create('p', {text: `Durée: ${toHHMMSS(adventure.duration)}`}, adventureDiv);
        create('p', {text: `Récompenses: ${adventure.rewards}`}, adventureDiv);

        let startDiv = create('div', {class: 'start-adventure'}, adventureDiv);
        let startBtn = create('button', {text: 'Débuter'}, startDiv);

        $(startDiv).on('click', this.beginAdventure.bind(this, adventure));

        let classes = ['required-lvl'];
        if (this.user.lvl < adventure.requiredLvl) classes.push('not-allowed');
        create('p', {class: classes, text: `niveau requis: ${adventure.requiredLvl}`}, startDiv);

        adventure.HTMLElt = adventureDiv;
    }

    /**
     * returns a value according to the status of the current adventure
     * false if there is no adventure in progress
     * true if there is an adventure in progress
     * 'end' if there is an adventure completed
     * @returns {boolean|string}
     */
    checkAdventureStatus() {
        let isInAdventure;

        if (this.user.adventureBeginning == null) {
            isInAdventure = false;
        } else {
            let adventureDuration = this.currentAdventure.duration;
            let adventureBeginning = this.user.adventureBeginning;
            let now = Date.now();

            if (adventureBeginning + (adventureDuration * 1000) <= now) {
                // adventure done
                isInAdventure = 'end';
            } else {
                isInAdventure = true;
            }
        }

        return isInAdventure;
    }

    /**
     * executes an ajax request to get rewards if the adventure is over
     */
    adventureCompleted() {
        $.post("index.php?action=adventures.finishedAdventure", (data) => {
            if (data.status === "success") {
                this.currentAdventure = null;
                this.user.adventureBeginning = null;
                this.user.inventoryObj.addStuff(data['stuff']);
                this.user.dollars = data['dollars'];
                this.user.xp = data['xp'];
                this.user.displayUserStats();

                this.showRewards(data['xpGained'], data['dollarsGained'], data['stuff']);
            } else {
                console.error(data.message);
            }
        }, "json");
    }

    showRewards(xpGained, dollarsGained, stuffGained) {
        let modalContent = create('div', null);
        create('h3', {text: `Votre aventure est terminée`}, modalContent);
        create('p', {text: `Vous avez gagné ${xpGained}xp`}, modalContent);
        create('p', {text: `Vous avez gagné ${dollarsGained}$`}, modalContent);
        create('p', {text: `Vous avez reçu un nouvel équipement :`}, modalContent);
        create('div', {class: ['stuff', stuffGained.rarityClass], innerHTML: stuffGained.htmlElt.innerHTML}, modalContent);

        new Modal(modalContent, ['adventure-completed']);
    }

    /**
     * displays the remaining time before the end of the adventure
     */
    adventureTimer() {
        let timeElapsed = Math.floor((Date.now() - this.user.adventureBeginning) / 1000);
        let remainingSeconds = this.currentAdventure.duration - timeElapsed;
        let startBtn = $(this.currentAdventure.HTMLElt).find('.start-adventure button');

        $(startBtn).siblings('p').hide();
        $(startBtn).hide();

        let timeP = create('p', {class: 'time', text: toHHMMSS(remainingSeconds)}, $(startBtn).parent()[0]);

        let timer = setInterval(() => {
            if (remainingSeconds > 1) {
                remainingSeconds -= 1;
                $(timeP).text(toHHMMSS(remainingSeconds));
            } else {
                $(timeP).remove();
                $(startBtn).siblings('p').show();
                $(startBtn).show();
                this.adventureCompleted();
                clearInterval(timer);
            }
        }, 1000);
    }

    /**
     * start a new adventure
     * @param adventure
     */
    beginAdventure(adventure) {
        // if the user isn't already in an adventure
        if (this.checkAdventureStatus() === false) {
            if (this.user.lvl >= adventure.requiredLvl) {
                $.post("index.php?action=adventures.startAdventure", {adventureId: adventure.id}, (data) => {
                    if (data.status === "success") {
                        this.currentAdventure = adventure;
                        this.user.adventureBeginning = Date.now();
                        this.adventureTimer();
                    } else {
                        console.error(data.message);
                    }
                }, "json");
            } else {
                new Modal(create('p', {class: 'error-message', text: `Vous n'avez pas le niveau nécessaire`}));
            }
        } else {
            new Modal(create('p', {class: 'error-message', text: `Vous êtes déjà dans une aventure`}));
        }
    }
}
