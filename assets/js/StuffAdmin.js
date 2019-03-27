class StuffAdmin {
    constructor(adminStuffContainerId) {
        this.adminStuffContainer = $('#' + adminStuffContainerId);

        this.getStuff((data) => {
            data.allStuff.forEach((stuff) => {
                this.displayStuff(stuff);
            });
        });
    }

    getStuff(callback) {
        $.get('index.php?action=stuff.getJSONStuff', (data) => {
            if (data.status === 'success') {
                callback(data);
            } else {
                console.error(data.message);
            }
        }, 'json');
    }

    displayStuff(stuff) {
        this.setRarityInfos(stuff);
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

    displayStuffForm(stuff) {

    }

    deleteStuff(stuff) {

    }
}

