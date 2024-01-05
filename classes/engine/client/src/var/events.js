export default class Events {
    constructor() {
        this.events = {};
    }

    register(event, callback) {
        if (typeof this.events[event] === "undefined") {
            this.events[event] = [];
        }

        this.events[event].push(callback);
    }

    fire(event, args) {
        if (!this.events[event]) {
            return;
        }
        let i;
        for (i in this.events[event]) {
            if (!this.events[event].hasOwnProperty(i)) {
                continue;
            }

            this.events[event][i](args);
        }
    }
}
