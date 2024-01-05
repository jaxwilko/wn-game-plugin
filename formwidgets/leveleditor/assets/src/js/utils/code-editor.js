export const bindEditor = (id, toggleId) => {
    const editor = ace.edit(id);
    editor.$blockScrolling = Infinity;

    window.jack = editor;

    // @TODO: Implement the following methods:
    // editor.getValue()
    // editor.setValue()

    editor.isOpen = () => {
        return document.getElementById(toggleId).checked;
    };

    editor.toggleEditorAddEventListener = (event, callback) => {
        document.getElementById(toggleId).addEventListener(event, callback);
    };

    return {
        editor: () => {
            return editor;
        },
        winterEditorPlugin: {
            install(app, options) {
                app.editor = editor;
                app.config.globalProperties.$editor = editor;
            }
        }
    }
};
