class StuffAdmin {
    /**
     * @param adminStuffContainerId
     */
    constructor(adminStuffContainerId, createStuffBtnId) {
        this.adminStuffContainer = $('#' + adminStuffContainerId);
        this.createStuffBtn = $('#' + createStuffBtnId);
        this.createStuffBtn.on('click', this.displayStuffForm.bind(this, null));

        this.getStuff((data) => {
            data.allStuff.forEach((stuff) => {
                this.displayStuff(stuff);
            });
        });
    }

    /**
     * get the stuff and execute the callback function if successful
     * @param callback
     */
    getStuff(callback) {
        $.get('index.php?action=stuff.getJSONStuff', (data) => {
            if (data.status === 'success') {
                callback(data);
            } else {
                console.error(data.message);
            }
        }, 'json');
    }

    /**
     * displays the stuff in the admin panel
     * @param stuff
     */
    displayStuff(stuff) {
        this.setRarityInfos(stuff);
        let stuffTr = create('tr', null, this.adminStuffContainer[0]);
        stuff.nameElt = create('td', {class: 'stuff-name', text: stuff.name}, stuffTr);
        stuff.typeElt = create('td', {class: 'stuff-type', text: stuff.type === 'sword' ? 'Épée' : 'Bouclier'}, stuffTr);
        stuff.requiredLvlElt = create('td', {class: 'stuff-lvl', text: stuff.requiredLvl}, stuffTr);
        stuff.statElt = create('td', {class: 'stuff-stat', text: stuff.stat}, stuffTr);
        stuff.rarityElt = create('td', {class: 'stuff-rarity', text: stuff.rarityName}, stuffTr);

        let editContainer = create('td', {class: 'edit', text: 'Modifier '}, stuffTr);
        create('i', {class: ['far', 'fa-edit']}, editContainer);
        $(editContainer).on('click', () => {
            this.displayStuffForm(stuff);
        });

        let deleteContainer = create('td', {class: 'delete', text: 'Supprimer '}, stuffTr);
        create('i', {class: ['far', 'fa-trash-alt']}, deleteContainer);
        $(deleteContainer).on('click', this.deleteStuff.bind(this, stuff));

        stuff.adminHTMLElt = stuffTr;
    }

    /**
     * @param stuff
     */
    setRarityInfos(stuff) {
        switch (stuff.rarity) {
            case 0:
                stuff.rarityName = 'commun';
                stuff.rarityClass = 'common';
                break;
            case 1:
                stuff.rarityName = 'rare';
                stuff.rarityClass = 'rare';
                break;
            case 2:
                stuff.rarityName = 'épique';
                stuff.rarityClass = 'epic';
                break;
            case 3:
                stuff.rarityName = 'légendaire';
                stuff.rarityClass = 'legendary';
                break;
        }
    }

    /**
     * @param stuff = null
     */
    displayStuffForm(stuff = null) {
        let stuffForm = create('form', null);
        let h2Elt = create('h2', {text: `Création de l'équipement`}, stuffForm);

        create('label', {for: 'stuff-name', text: `Nom de l'équipement :`}, stuffForm);
        let nameInput = create('input', {id: 'stuff-name', maxlength: 50, required: ''}, stuffForm);

        let typeSelect = {};
        typeSelect.select = create('select', null, stuffForm);
        typeSelect.swordOption = create('option', {text: 'Épée', value: 'sword'}, typeSelect.select);
        typeSelect.shieldOption = create('option', {text: 'Bouclier', value: 'shield'}, typeSelect.select);

        create('label', {for: 'stuff-required-lvl', text: `Niveau requis :`}, stuffForm);
        let requiredLvlInput = create('input', {id: 'stuff-required-lvl', type: 'text', required: ''}, stuffForm);

        create('label', {for: 'stuff-stat', text: `Stat de l'équipement :`}, stuffForm);
        let statInput = create('input', {id: 'stuff-stat', type: 'text', required: ''}, stuffForm);

        let raritySelect = {};
        raritySelect.select = create('select', null, stuffForm);
        raritySelect.commonOption = create('option', {text: 'Commun', value: '0'}, raritySelect.select);
        raritySelect.rareOption = create('option', {text: 'Rare', value: '1'}, raritySelect.select);
        raritySelect.epicOption = create('option', {text: 'Épique', value: '2'}, raritySelect.select);
        raritySelect.legendaryOption = create('option', {text: 'Légendaire', value: '3'}, raritySelect.select);

        let submitInput = create('input', {type: 'submit', value: 'Créer'}, stuffForm);

        let modal = new Modal(stuffForm);

        if (stuff !== null) {
            h2Elt.textContent = `Modification de l'équipement`;
            nameInput.value = stuff.name;
            typeSelect[stuff.type + 'Option'].setAttribute('selected', 'selected');
            requiredLvlInput.value = stuff.requiredLvl;
            statInput.value = stuff.stat;
            raritySelect[stuff.rarityClass + 'Option'].setAttribute('selected', 'selected');
            submitInput.value = 'Modifier';

            $(stuffForm).on('submit', this.editStuff.bind(this, stuff, modal, stuffForm, nameInput, typeSelect.select, requiredLvlInput, statInput, raritySelect.select));
        } else {
            $(stuffForm).on('submit', this.createStuff.bind(this, modal, stuffForm, nameInput, typeSelect.select, requiredLvlInput, statInput, raritySelect.select));
        }
    }

    /**
     * edit the values of the stuff
     * @param stuff
     * @param modal
     * @param stuffForm
     * @param nameInput
     * @param typeSelect
     * @param requiredLvlInput
     * @param statInput
     * @param raritySelect
     * @param e
     */
    editStuff(stuff, modal, stuffForm, nameInput, typeSelect, requiredLvlInput, statInput, raritySelect, e) {
        e.preventDefault();

        let name = nameInput.value;
        let type = typeSelect.value;
        let requiredLvl = requiredLvlInput.value;
        let stat = statInput.value;
        let rarityNb = raritySelect.value;

        if (!(isNaN(stat) || isNaN(requiredLvl))) {
            let data = {
                id: stuff.id,
                name: name,
                type: type,
                requiredLvl: requiredLvl,
                stat: stat,
                rarity: rarityNb
            }

            $.post('index.php?action=stuff.editStuff', data, (data) => {
                if (data.status === "success") {
                    modal.closeModal();
                    // change the values of the stuff in the panel
                    stuff.nameElt.textContent = stuff.name = name;
                    stuff.typeElt.textContent = type === 'sword' ? 'Épée' : 'Bouclier';
                    stuff.type = type;
                    stuff.requiredLvlElt.textContent = stuff.requiredLvl = requiredLvl;
                    stuff.statElt.textContent = stuff.stat = stat;
                    stuff.rarity = rarityNb;
                    this.setRarityInfos(stuff);
                    stuff.rarityElt.textContent = stuff.rarityName;

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

    /**
     * creates a stuff and adds it in the admin panel if successful
     * @param modal
     * @param stuffForm
     * @param nameInput
     * @param typeSelect
     * @param requiredLvlInput
     * @param statInput
     * @param raritySelect
     * @param e
     */
    createStuff(modal, stuffForm, nameInput, typeSelect, requiredLvlInput, statInput, raritySelect, e) {
        e.preventDefault();

        let name = nameInput.value;
        let type = typeSelect.value;
        let requiredLvl = requiredLvlInput.value;
        let stat = statInput.value;
        let rarityNb = raritySelect.value;

        if (!(isNaN(stat) || isNaN(requiredLvl))) {
            let data = {
                name: name,
                type: type,
                requiredLvl: requiredLvl,
                stat: stat,
                rarity: rarityNb
            }

            $.post('index.php?action=stuff.createStuff', data, (data) => {
                if (data.status === "success") {
                    modal.closeModal();
                    this.displayStuff(data['stuff']);
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
     * do an ajax request to delete the stuff with his id
     * @param stuff
     */
    deleteStuff(stuff) {
        if (confirm(`Êtes-vous sûr de vouloir supprimer cet équipement ? Cela le supprimera également de l'inventaire de chaque joueur qui le possède.`)) {
            $.post("index.php?action=stuff.deleteStuff", {stuffId: stuff.id}, (data) => {
                if (data.status === "success") {
                    new Modal(create('p', {text: `L'équipement a bien été supprimée`, class: 'info-message'}));
                    $(stuff.adminHTMLElt).remove();
                } else {
                    console.error(data.message);
                }
            }, "json");
        }
    }
}

