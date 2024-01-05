import { createApp } from "vue";
import VueTippy from "vue-tippy";
import LevelEditor from "./components/LevelEditor.vue";
import store from "./store";
import { bindJq } from "./utils/winter-jq";
import { request, winterRequestPlugin } from "./utils/winter-request";


window.addEventListener('load', () => {
    const app = createApp({
        components: {LevelEditor}
    });

    const { jq, winterJqPlugin } = bindJq(jQuery);

    app.use(VueTippy);
    app.use(store);
    app.use(winterRequestPlugin);
    app.use(winterJqPlugin);

    store.request = request;
    store.jq = jq;

    app.mount("#level-editor");
});
