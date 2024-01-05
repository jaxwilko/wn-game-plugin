import Debug from "../utils/debug";

export default class Console {
    constructor(engine) {
        this.engine = engine;
        this.container = null;

        this.engine.events.register('tick', (engine) => {
            if (engine.state?.world?.messages && Object.entries(engine.state?.world?.messages).length) {
                this.container.pushMessages(engine.state.world.messages);
            }
        })
    }

    bind(vue) {
        this.container = vue;
    }

    send(message) {
        if (message.indexOf('/debug') === 0) {
            Debug.set('enabled', !Debug.get('enabled'));
            return;
        }

        this.engine.network.queue('message', message);
    }
}
