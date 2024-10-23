import Console from "./var/console";
import Debug from "./utils/debug";
import Quests from "./var/quests";

export default class Engine
{
    constructor(canvas, camera, events, controls, network, statusUpdate) {
        this.canvas = canvas;
        this.camera = camera;
        this.events = events;
        this.controls = controls;
        this.network = network;
        this.statusUpdate = statusUpdate;

        this.console = new Console(this);
        this.quests = new Quests(this);

        this.ticks = {
            local: {},
            remote: {}
        };

        this.fps = {
            local: 0,
            remote: 0
        };

        this.stop = false;

        this.state = null;

        this.lastHadInput = false;

        Debug.set({
            enabled: false,
            objectPos: false,
            gridPos: false,
            drawFps: true,
            drawPos: true,
            drawCamera: false,
        })
    }

    init() {
        const _this = this;
        this.controls.registerKeyInput();

        this.network.openSocket().bindSocketHandlers({
            onopen: () => {
                this.network.queue("settings", {})
            },
            onclose: (e) => {
                console.error('Websocket closed', e);
                this.statusUpdate(false);
            },
            onmessage: (e) => {
                const time = (new Date()).getTime().toString().substr(0, 10);

                if (typeof this.ticks.remote[time] === "undefined") {
                    this.fps.remote = this.ticks.remote[Object.keys(this.ticks.remote)[0]];
                    this.ticks.remote = {};
                    this.ticks.remote[time] = 0;
                }

                this.ticks.remote[time]++;

                this.state = JSON.parse(e.data).state;
            }
        });

        this.statusUpdate(true);

        const localTick = () => {
            if (this.network.socketIsOpen() && this.controls.hasInput()) {
                this.lastHadInput = true;
                this.network.queue("controls", this.controls.active);
            }

            if (!this.controls.hasInput() && this.lastHadInput) {
                this.lastHadInput = false;
                this.network.queue("controls", this.controls.active);
            }

            this.network.dispatchQueue();
        };


        const tick = () => {
            if (this.stop) {
                return;
            }

            const time = (new Date()).getTime().toString().substr(0, 10);

            if (typeof this.ticks.local[time] === "undefined") {
                this.fps.local = this.ticks.local[Object.keys(this.ticks.local)[0]];
                this.ticks.local = {};
                this.ticks.local[time] = 0;
            }

            this.ticks.local[time]++;

            if (this.state) {
                if (this.state?.player?.settings?.camera?.vector) {
                    this.camera.adjust(this.state.player.settings.camera.vector);
                }
            }

            this.events.fire("tick", this);
            this.render();

            window.requestAnimationFrame(tick);
        };

        this.localTickInterval = setInterval(localTick, (1/128) * 1000);
        window.requestAnimationFrame(tick);
    }

    resize() {
        this.canvas.resize(this.camera);

        if (!this.state || this.state?.player?.settings?.camera?.size !== this.camera.size.toPoint()) {
            this.network.queue("settings", {
                "camera": {
                    size: this.camera.size.toPoint(),
                }
            });

            return;
        }

        this.network.queue("settings", {
            "camera": {
                size: this.camera.size.toPoint(),
                vector: this.camera.vector.toPoint()
            }
        });
    }

    render() {
        this.canvas.drawBackground(this.state?.world?.level?.void);

        if (!this.state) {
            return;
        }

        this.canvas.renderWorld(this.state.world, this.camera);

        if (Debug.get("drawFps")) {
            this.canvas.drawFps(this.fps);
        }

        if (Debug.get("drawPos")) {
            this.canvas.drawPos(this.state.player);
        }

        if (Debug.get("drawCamera")) {
            this.canvas.drawCamera(this.camera);
        }
    }
}
