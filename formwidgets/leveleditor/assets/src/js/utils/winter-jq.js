export const bindJq = ($) => {
    return {
        jq: () => {
            return $(...arguments)
        },
        winterJqPlugin: {
            install(app, options) {
                app.jq = $;
                app.config.globalProperties.$jq = $;
            }
        }
    };
};
