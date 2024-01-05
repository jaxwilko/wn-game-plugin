import { createApp } from "vue";
import Game from "./components/Game.vue";

import Canvas from "./src/renderer/canvas";
import Camera from "./src/utils/camera";
import Engine from "./src/engine";
import Controls from "./src/var/controls";
import Events from "./src/var/events";
import Network from "./src/var/network";

const client = createApp(Game);

client.use({
    install(app, options) {
        app.config.globalProperties.$createEngine = (canvas, statusUpdate) => {
            const engine = new Engine(
                new Canvas(canvas),
                new Camera(),
                new Events(),
                new Controls(),
                new Network($),
                statusUpdate
            );

            engine.resize();

            window.addEventListener("resize", function () {
                engine.resize();
            });

            engine.init();

            app.config.globalProperties.$engine = engine;
        }
    }
});

client.mount("#jax-game");
