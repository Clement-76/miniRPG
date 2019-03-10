class User {

    constructor() {
        this.stats = {
            baseAttack: 0,
            baseDefense: 0
        }

        this.getUserFeatures();
    }

    /**
     * @param userFeatures an array
     */
    hydrate(userFeatures) {
        for (let key in userFeatures) {
            let value = userFeatures[key];
            this[key] = value;
        }
    }

    /**
     * call when we try to set a value to the xp property
     * @param actualXp
     */
    set xp(actualXp) {
        actualXp = Number(actualXp);
        let actualLvl;
        let xpTotal = 0;

        for (let lvl = 2; lvl <= 100; lvl++) {
            let xp = 100 * Math.pow(lvl + 1, 2);
            xpTotal += xp;

            if (xpTotal > actualXp) {
                actualLvl = lvl - 1;
                break;
            } else if (xpTotal === actualXp) {
                actualLvl = lvl;
                break;
            }
        }

        let missingXp = xpTotal - actualXp;

        if (actualXp > xpTotal) {
            actualLvl = 100;
            missingXp = 0;
        } else if (xpTotal === actualXp) {
            missingXp += 100 * Math.pow(actualLvl + 2, 2);
        }

        this.xpPercentage = actualXp / xpTotal * 100;
        this.lvl = actualLvl;
        this.missingXp = missingXp;
    }

    /**
     * instantiates an Inventory object and stores it in a property
     * @param inventory
     */
    set inventory(inventory) {
        this.inventoryObj = new Inventory(inventory, this);
    }

    set attack(attack) {
        this.stats.baseAttack = attack;
    }

    set defense(defense) {
        this.stats.baseDefense = defense;
    }

    /**
     * retrieve user data with an ajax request
     */
    getUserFeatures() {
        $.get('index.php?action=users.getJSONUser', (data) => {
            if (data.status === 'success') {
                this.hydrate(data.userFeatures);
                this.displayUserStats();
            } else {
                throw new Error(data.message);
            }
        }, 'json');
    }

    /**
     * display the user stats in the characteristics window
     */
    displayUserStats() {
        let attack = this.stats.baseAttack;
        let defense = this.stats.baseDefense;

        // if a sword is equipped
        if (this.inventoryObj.equippedStuff.sword !== null) {
            attack += this.inventoryObj.equippedStuff.sword.stat;
        }

        // if a shield is equipped
        if (this.inventoryObj.equippedStuff.shield !== null) {
            defense += this.inventoryObj.equippedStuff.shield.stat
        }

        $('.characteristics .pseudo').text(this.pseudo);
        $('.characteristics .lvl').text(`(lvl ${this.lvl})`);
        $('.characteristics .progress').text(`${this.xpPercentage}%`);
        $('.characteristics .progress').css('width', `${this.xpPercentage}%`);
        $('.characteristics .life span').text(this.life);
        $('.characteristics .attack span').text(attack);
        $('.characteristics .defense span').text(defense);
    }
}
