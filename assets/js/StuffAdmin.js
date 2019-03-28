class StuffAdmin {
    /**
     * @param adminStuffContainerId
     */
    constructor(adminStuffContainerId) {
        this.adminStuffContainer = $('#' + adminStuffContainerId);

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
        this.setRarityName(stuff);
        let stuffTr = create('tr', null, this.adminStuffContainer[0]);
        stuff.nameElt = create('td', {class: 'stuff-name', text: stuff.name}, stuffTr);
        stuff.typeElt = create('td', {class: 'stuff-type', text: stuff.type}, stuffTr);
        stuff.requiredLvlElt = create('td', {class: 'stuff-lvl', text: stuff.requiredLvl}, stuffTr);
        stuff.stat = create('td', {class: 'stuff-stat', text: stuff.stat}, stuffTr);
        stuff.rarity = create('td', {class: 'stuff-rarity', text: stuff.rarityName}, stuffTr);

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
    setRarityName(stuff) {
        switch (stuff.rarity) {
            case 0:
                stuff.rarityName = 'commun';
                break;
            case 1:
                stuff.rarityName = 'rare';
                break;
            case 2:
                stuff.rarityName = 'épique';
                break;
            case 3:
                stuff.rarityName = 'légendaire';
                break;
        }
    }

    displayStuffForm(stuff) {

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

