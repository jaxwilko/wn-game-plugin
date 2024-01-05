export default class Network {
    constructor($) {
        this.$ = $;
        this.messages = [];
        this.socket = null;
    }

    request(request, callback, options = {}) {
        options.success = callback;
        this.$.request(request, options);
    }

    openSocket() {
        this.socket = new WebSocket(`wss://${window.location.host}/ws/game`);
        return this;
    }

    bindSocketHandlers(handlers) {
        for (let key in handlers) {
            this.socket[key] = handlers[key];
        }
        return this;
    }

    socketIsOpen() {
        return this.socket.readyState === WebSocket.OPEN;
    }

    dispatchQueue() {
        if (!this.socketIsOpen()) {
            return;
        }

        this.messages.forEach((message) => {
            this.socket.send(message);
        });

        this.messages = [];
    }

    queue(action, data) {
        this.messages.push(this.pack(action, data));
    }

    pack(action, data) {
        return JSON.stringify({
            action: action,
            data: data
        });
    }
}
