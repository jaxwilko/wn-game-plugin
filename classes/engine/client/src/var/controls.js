export default class Controls {
    constructor() {
        this.active = {
            "up": false,
            "down": false,
            "left": false,
            "right": false,
            "action": false,
            "attack": false,
        };

        this.keyMap = {
            KeyW: "up",
            KeyS: "down",
            KeyA: "left",
            KeyD: "right",
            ArrowUp: "up",
            ArrowDown: "down",
            ArrowLeft: "left",
            ArrowRight: "right",
            KeyE: "attack",
            KeyZ: "attack",
            KeyF: "action",
            Space: "attack"
        };
    }

    hasInput() {
        return Object.values(this.active).includes(true);
    }

    registerKeyInput() {
        window.addEventListener("keydown", (e) => {
            if (e.target.nodeName === "BODY" && this.keyMap[e.code]) {
                this.active[this.keyMap[e.code]] = true;
            }
        });
        window.addEventListener("keyup", (e) => {
            if (this.keyMap[e.code] && this.active[this.keyMap[e.code]]) {
                this.active[this.keyMap[e.code]] = false;
            }
        });
    }
}
