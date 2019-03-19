class Adventures {
    constructor(adventuresContainerId, userObj) {
        this.user = userObj;
        this.adventuresContainer = $('#' + adventuresContainerId);
        this.getAdventures();
    }

    getAdventures() {
        $.get("index.php?action=adventures.getJSONAdventures", (data) => {
            if (data.status === "success") {
                data.adventures.forEach((adventure) => {
                    this.displayAdventure(adventure);
                });
            } else {
                console.error(data.message);
            }
        }, "json");
    }

    displayAdventure(adventure) {
        let adventureDiv = create('div', {class: 'adventure'}, this.adventuresContainer[0]);
        create('p', {text: `Nom: ${adventure.name}`}, adventureDiv);
        create('p', {text: `Durée: ${toHHMMSS(adventure.duration)}`}, adventureDiv);
        create('p', {text: `Récompenses: ${adventure.rewards}`}, adventureDiv);

        let startDiv = create('div', {class: 'start-adventure'}, adventureDiv);
        let startBtn = create('button', {text: 'Débuter'}, startDiv);

        let classes = ['required-lvl'];
        if (this.user.lvl < adventure.requiredLvl) classes.push('not-allowed');
        create('p', {class: classes, text: `niveau requis: ${adventure.requiredLvl}`}, startDiv);
    }
}
