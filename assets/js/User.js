class User {

    constructor() {
        this.getUserFeatures();
    }

    hydrate(userFeatures) {
        for (let key in userFeatures) {
            let value = userFeatures[key];
            this[key] = value;
        }
    }

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

    displayUserStats() {
        $('.characteristics .pseudo').text(this.pseudo);
        $('.characteristics .lvl').text(`(lvl ${this.lvl})`);
        $('.characteristics .progress').text(`${this.xpPercentage}%`);
        $('.characteristics .progress').css('width', `${this.xpPercentage}%`);
        $('.characteristics .life span').text(this.life);
        $('.characteristics .attack span').text(this.attack);
        $('.characteristics .defense span').text(this.defense);
    }
}
