export default class Quests {
    constructor(engine) {
        this.engine = engine;
        this.quests = new Proxy({}, {
            get() {
                return Reflect.get(...arguments);
            }
        });
        this.engine.network.request('onQuestDataProvider', (response) => {
            for (let i in response.quests) {
                this.quests[i] = response.quests[i];
            }
        });
    }

    get(quest) {
        return quest ? this.quests[quest] : this.quests;
    }
}
