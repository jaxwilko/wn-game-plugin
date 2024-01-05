export const request = (handler, options) => {
    $.request(handler, options)
}

export const winterRequestPlugin = {
    install(app, options) {
        app.request = request;
        app.config.globalProperties.$request = request;
    }
};
