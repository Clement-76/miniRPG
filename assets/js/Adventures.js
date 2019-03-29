class Adventures {
    /**
     * @param adventuresContainerId
     * @param adventuresAdminContainerId
     * @param btnCreateAdventureId
     * @param formAdventureId
     * @param userObj
     */
    constructor(adventuresContainerId, adventuresAdminContainerId, btnCreateAdventureId, userObj) {
        this.adventures = [];
        this.currentAdventure = null;
        this.user = userObj;
        this.adventuresContainer = $('#' + adventuresContainerId);
        this.adventuresAdminContainer = $('#' + adventuresAdminContainerId);
        this.btnCreateAdventure = $('#' + btnCreateAdventureId);

        this.btnCreateAdventure.on('click', (e) => {
            this.displayAdventuresForm(e);
        });

        this.getAdventures((data) => {
            data.adventures.forEach((adventure) => {
                this.displayAdventure(adventure);
                this.adventures.push(adventure);

                if (this.user.role === 'admin') this.displayAdventuresAdmin(adventure);

                // if the user is currently in an adventure, we store it in a property
                if (this.user.currentAdventureId === adventure.id) {
                    this.currentAdventure = adventure;
                }
            });

            let adventure = this.checkAdventureStatus();

            if (adventure === 'end') {
                this.adventureCompleted();
            } else if (adventure === true) {
                this.adventureTimer();
            }
        });
    }

    /**
     * retrieves adventures with ajax request and executes the callback function in case of success
     * @param callback
     */
    getAdventures(callback) {
        $.get("index.php?action=adventures.getJSONAdventures", (data) => {
            if (data.status === "success") {
                callback(data);
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
        create('p', {text: `Récompenses: équipements/argent($)/points d'expérience`}, adventureDiv);

        let startDiv = create('div', {class: 'start-adventure'}, adventureDiv);
        let startBtn = create('button', {text: 'Débuter'}, startDiv);

        $(startBtn).on('click', this.beginAdventure.bind(this, adventure));

        let classes = ['required-lvl'];
        if (this.user.lvl < adventure.requiredLvl) classes.push('not-allowed');
        create('p', {class: classes, text: `niveau requis: ${adventure.requiredLvl}`}, startDiv);

        adventure.HTMLElt = adventureDiv;
    }

    /**
     * displays adventures in the admin panel
     * @param adventure
     */
    displayAdventuresAdmin(adventure) {
        let adventureTr = create('tr', null, this.adventuresAdminContainer[0]);
        adventure.nameElt = create('td', {class: 'adventure-name', text: adventure.name}, adventureTr);
        adventure.durationElt = create('td', {class: 'adventure-duration', text: adventure.duration}, adventureTr);
        adventure.requiredLvlElt = create('td', {class: 'adventure-lvl', text: adventure.requiredLvl}, adventureTr);
        adventure.dollarsElt = create('td', {class: 'adventure-dollars', text: adventure.dollars}, adventureTr);
        adventure.xpElt = create('td', {class: 'adventure-xp', text: adventure.xp}, adventureTr);

        let editContainer = create('td', {class: 'edit', text: 'Modifier '}, adventureTr);
        create('i', {class: ['far', 'fa-edit']}, editContainer);
        $(editContainer).on('click', (e) => {
            this.displayAdventuresForm(e, adventure);
        });

        let deleteContainer = create('td', {class: 'delete', text: 'Supprimer '}, adventureTr);
        create('i', {class: ['far', 'fa-trash-alt']}, deleteContainer);
        $(deleteContainer).on('click', this.deleteAdventure.bind(this, adventure));

        adventure.adminHTMLElt = adventureTr;
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

    /**
     * show the rewards of an adventure in a modal
     * @param xpGained
     * @param dollarsGained
     * @param stuffGained
     */
    showRewards(xpGained, dollarsGained, stuffGained) {
        let modalContent = create('div', null);
        create('h3', {text: `Votre aventure est terminée`}, modalContent);
        create('p', {text: `Vous avez gagné ${xpGained}xp`}, modalContent);
        create('p', {text: `Vous avez gagné ${dollarsGained}$`}, modalContent);
        create('p', {text: `Vous avez reçu un nouvel équipement :`}, modalContent);
        create('div', {
            class: ['stuff', stuffGained.rarityClass],
            innerHTML: stuffGained.htmlElt.innerHTML
        }, modalContent);

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
                new Modal(create('p', {class: 'info-message', text: `Vous n'avez pas le niveau nécessaire`}));
            }
        } else {
            new Modal(create('p', {class: 'info-message', text: `Vous êtes déjà dans une aventure`}));
        }
    }

    /**
     * send an ajax request to delete an adventure, in case of success, removes it from the admin panel
     * @param adventure
     */
    deleteAdventure(adventure) {
        if (confirm('Êtes-vous sûr de vouloir supprimer cette aventure ?')) {
            $.post("index.php?action=adventures.deleteAdventure", {adventureId: adventure.id}, (data) => {
                if (data.status === "success") {
                    new Modal(create('p', {text: `L'aventure a bien été supprimée`, class: 'info-message'}));
                    $(adventure.adminHTMLElt).remove();
                } else {
                    console.error(data.message);
                }
            }, "json");
        }
    }

    /**
     * displays the form to create a new adventure
     */
    displayAdventuresForm(e, adventure = null) {
        let adventureForm = create('form', null);
        let h2Elt = create('h2', {text: `Création de l'aventure`}, adventureForm);

        create('label', {for: 'adventure-name', text: `Nom de l'aventure :`}, adventureForm);
        let nameInput = create('input', {id: 'adventure-name', maxlength: 50, required: ''}, adventureForm);

        create('label', {for: 'adventure-duration', text: `Durée (en secondes) :`}, adventureForm);
        let durationInput = create('input', {id: 'adventure-duration', type: 'text'}, adventureForm);

        create('label', {for: 'adventure-required-lvl', text: `Niveau requis :`}, adventureForm);
        let requiredLvlInput = create('input', {id: 'adventure-required-lvl', type: 'text', required: ''}, adventureForm);

        create('label', {for: 'adventure-dollars', text: `Récompense en dollars :`}, adventureForm);
        let dollarsInput = create('input', {id: 'adventure-dollars', type: 'text', required: ''}, adventureForm);

        create('label', {for: 'adventure-xp', text: `Récompense en xp :`}, adventureForm);
        let xpInput = create('input', {id: 'adventure-xp', type: 'text', required: ''}, adventureForm);

        let submitInput = create('input', {type: 'submit', value: 'Créer'}, adventureForm);

        let modal = new Modal(adventureForm);

        if (adventure !== null) {
            h2Elt.textContent = `Modification de l'aventure`;
            nameInput.value = adventure.name;
            durationInput.value = adventure.duration;
            requiredLvlInput.value = adventure.requiredLvl;
            dollarsInput.value = adventure.dollars;
            xpInput.value = adventure.xp;
            submitInput.value = 'Modifier';

            $(adventureForm).on('submit', this.editAdventure.bind(this, adventure, modal, adventureForm, nameInput, durationInput, requiredLvlInput, dollarsInput, xpInput));
        } else {
            $(adventureForm).on('submit', this.createAdventure.bind(this, modal, adventureForm, nameInput, durationInput, requiredLvlInput, dollarsInput, xpInput));
        }
    }

    /**
     * create a new adventure and in case of success, adds it to the admin panel
     * @param modal
     * @param adventureForm
     * @param nameInput
     * @param durationInput
     * @param requiredLvlInput
     * @param dollarsInput
     * @param xpInput
     * @param e
     */
    createAdventure(modal, adventureForm, nameInput, durationInput, requiredLvlInput, dollarsInput, xpInput, e) {
        e.preventDefault();

        let name = nameInput.value;
        let duration = durationInput.value;
        let requiredLvl = requiredLvlInput.value;
        let dollars = dollarsInput.value;
        let xp = xpInput.value;

        if (!(isNaN(duration) || isNaN(requiredLvl) || isNaN(dollars) || isNaN(xp))) {
            let data = {
                name: name,
                duration: duration,
                requiredLvl: requiredLvl,
                dollars: dollars,
                xp: xp
            }

            $.post('index.php?action=adventures.createAdventure', data, (data) => {
                if (data.status === "success") {
                    modal.closeModal();
                    this.displayAdventuresAdmin(data['adventure']);
                } else {
                    console.error(data.message);
                    new Modal(create('p', {text: `Une erreur est survenue`, class: 'info-message'}));
                }
            }, 'json');
        } else {
            new Modal(create('p', {text: `Erreur : valeurs incorrects`, class: 'info-message'}));
        }
    }

    /**
     * update an adventure and if successful, change it in the panel
     * @param adventureId
     * @param modal
     * @param adventureForm
     * @param nameInput
     * @param durationInput
     * @param requiredLvlInput
     * @param dollarsInput
     * @param xpInput
     * @param e
     */
    editAdventure(adventure, modal, adventureForm, nameInput, durationInput, requiredLvlInput, dollarsInput, xpInput, e) {
        e.preventDefault();

        let name = nameInput.value;
        let duration = durationInput.value;
        let requiredLvl = requiredLvlInput.value;
        let dollars = dollarsInput.value;
        let xp = xpInput.value;

        if (!(isNaN(duration) || isNaN(requiredLvl) || isNaN(dollars) || isNaN(xp))) {
            let data = {
                id: adventure.id,
                name: name,
                duration: duration,
                requiredLvl: requiredLvl,
                dollars: dollars,
                xp: xp
            }

            $.post('index.php?action=adventures.editAdventure', data, (data) => {
                if (data.status === "success") {
                    modal.closeModal();
                    // change the values of the adventure in the panel
                    adventure.nameElt.textContent = adventure.name = name;
                    adventure.durationElt.textContent = adventure.duration = duration;
                    adventure.requiredLvlElt.textContent = adventure.requiredLvl = requiredLvl;
                    adventure.dollarsElt.textContent = adventure.dollars = dollars;
                    adventure.xpElt.textContent = adventure.xp = xp;

                    new Modal(create('p', {text: `La modification a été effectuée avec succès`, class: 'info-message'}));
                } else {
                    console.error(data.message);
                    new Modal(create('p', {text: `Une erreur est survenue`, class: 'info-message'}));
                }
            }, 'json');
        } else {
            new Modal(create('p', {text: `Erreur : valeurs incorrects`, class: 'info-message'}));
        }
    }
}
