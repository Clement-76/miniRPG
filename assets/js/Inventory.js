class Inventory {
    constructor(inventory, userObj) {
        this.inventory = inventory;
        this.equippedBtn = $('#equipped');
        this.unequippedBtn = $('#unequipped');
        this.userObj = userObj;

        // default values
        this.equippedStuff = {
            sword: null,
            shield: null
        }

        if (this.inventory !== null) {
            this.nbStuff = this.inventory.length;

            // return the first stuff in the inventory that is not equipped if there is one
            let firstStuff = this.inventory.find(stuff => stuff.equipped === false);

            this.inventory.forEach((stuff) => {
                this.setRarityInfos(stuff);

                if (stuff !== firstStuff) {
                    this.displayStuff(stuff, false);
                } else {
                    this.displayStuff(stuff, true);
                }
            });
        } else {
            this.nbStuff = 0;
        }

        this.equippedBtn.on('click', this.equipStuff.bind(this));
        this.unequippedBtn.on('click', this.unequipStuff.bind(this));
    }

    /**
     * adds the new stuff in the inventory
     * @param stuff
     */
    addStuff(stuff) {
        this.nbStuff++;
        this.setRarityInfos(stuff);
        this.displayStuff(stuff, false);
        this.inventory.push(stuff);

        return stuff;
    }

    displayStuff(stuff, isFirstStuff) {
        let cssClasses = ['stuff', stuff.type, stuff.rarityClass];

        if (isFirstStuff) cssClasses.push('selected');

        let parentElt;

        if (stuff.equipped) {
            this.equippedStuff[stuff.type] = stuff;
            parentElt = $(`.currently-equipped .${stuff.type}`).removeClass('stuff')[0];
        } else {
            parentElt = $('.all-stuff')[0];
        }

        stuff.htmlElt = create('div', {
            class: cssClasses,
            innerHTML: `<i class="icon-${stuff.type}"></i><div class="stat">${stuff.stat}</div>`
        }, parentElt);

        if (isFirstStuff) this.viewStuffInfo(stuff);

        $(stuff.htmlElt).on('click', this.viewStuffInfo.bind(this, stuff));
    }

    /**
     * display the information of the selected stuff in the inventory
     * @param stuff
     * @param e = false per default
     */
    viewStuffInfo(stuff, e = false) {
        if (e !== false) {
            $('.inventory .selected').removeClass('selected');
            $(e.currentTarget).addClass('selected');
        }

        if (stuff.equipped) {
            this.equippedBtn.hide();
            this.unequippedBtn.show();
        } else {
            this.equippedBtn.show();
            this.unequippedBtn.hide();
        }

        // to delete the rarity class of the previous element
        if (this.selectedStuff) {
            $('.stuff-select .rarity').removeClass(this.selectedStuff.rarityClass)
        }

        $('.stuff-select .name').text(stuff.name);
        $('.stuff-select .type').text(stuff.type === 'sword' ? 'épée' : 'bouclier');
        $('.stuff-select .rarity').addClass(stuff.rarityClass).text(stuff.rarityName);
        $('.stuff-select .stats').text(stuff.stat);

        this.selectedStuff = stuff;
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

    equipStuff() {
        let stuffId = this.selectedStuff.id;

        $.post('index.php?action=stuff.equipStuff', {stuffId: stuffId}, (data) => {
            if (data['status'] === 'success') {

                let equippedStuff = this.equippedStuff[this.selectedStuff.type];

                // if stuff are not already equipped
                if (equippedStuff === null) {
                    let stuffLocation = $(`.${this.selectedStuff.type}.equipped`).removeClass('stuff');
                    stuffLocation.append(this.selectedStuff.htmlElt);
                } else {
                    equippedStuff.htmlElt.replaceWith(this.selectedStuff.htmlElt);
                    equippedStuff.equipped = false;

                    // adding the old stuff in the inventory
                    $('.all-stuff').append(equippedStuff.htmlElt);
                }

                this.selectedStuff.equipped = true;
                this.equippedStuff[this.selectedStuff.type] = this.selectedStuff;

                this.equippedBtn.hide();
                this.unequippedBtn.show();

                this.userObj.displayUserStats();
            } else {
                console.error(data['message']);
            }
        }, 'json');
    }

    unequipStuff() {
        let stuffId = this.selectedStuff.id;

        $.post('index.php?action=stuff.unequipStuff', {stuffId: stuffId}, (data) => {
            if (data['status'] === 'success') {
                this.selectedStuff.equipped = false;
                this.equippedStuff[this.selectedStuff.type] = null;

                $(`.${this.selectedStuff.type}.equipped`).addClass('stuff');
                $('.all-stuff').append(this.selectedStuff.htmlElt);

                this.equippedBtn.show();
                this.unequippedBtn.hide();

                this.userObj.displayUserStats();
            } else {
                console.error(data['message']);
            }
        }, 'json');
    }
}
