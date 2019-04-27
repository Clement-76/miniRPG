class User {

    constructor() {
        this.stats = {
            baseAttack: 0,
            baseDefense: 0
        }

        this.arenaObj = null;
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

        if (this.arenaObj !== null) {
            // if the user lvl up we reload the players in the arena
            if (actualLvl > this.lvl) this.arenaObj.displayAllPlayers();
        }

        this.lvl = actualLvl;
        this.missingXp = missingXp;
        this.totalXpNeededForThisLvl = 100 * Math.pow(actualLvl + 2, 2);
        this.xpPercentage = Math.floor(((this.totalXpNeededForThisLvl - this.missingXp) / this.totalXpNeededForThisLvl) * 100);
    }

    /**
     * instantiates an Inventory object and stores it in a property
     * @param inventory
     */
    set inventory(inventory) {
        this.inventoryObj = new Inventory(inventory, this);
    }

    set naturalAttack(attack) {
        this.stats.baseAttack = attack;
    }

    set naturalDefense(defense) {
        this.stats.baseDefense = defense;
    }

    set battles(nb) {
        this.remainingBattles = nb;
        this.arenaObj = new Arena('players', 'remaining-fights', 'battles-timer', this);
    }

    /**
     * retrieve user data with an ajax request
     */
    getUserFeatures() {
        $.get(baseUrl + 'Users/getJSONUser', (data) => {
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
            defense += this.inventoryObj.equippedStuff.shield.stat;
        }

        $('#dollars').html(`<i class="fas fa-dollar-sign"></i> ${this.dollars}`);
        $('#tenge').html(`<i class="fas fa-tenge"></i> ${this.T}`);
        $('.characteristics .pseudo').html(this.pseudo);
        $('.characteristics .lvl').text(`(lvl ${this.lvl})`);
        $('.user .lvl').text(`lvl ${this.lvl}`);
        $('.xp-bar .progress').css('width', `${this.xpPercentage}%`);
        $('.characteristics .progress').text(`${this.xpPercentage}%`);
        $('.characteristics .progress').css('width', `${this.xpPercentage}%`);
        $('.characteristics .life span').text(this.life);
        $('.characteristics .attack span').text(attack);
        $('.characteristics .defense span').text(defense);
    }
}
