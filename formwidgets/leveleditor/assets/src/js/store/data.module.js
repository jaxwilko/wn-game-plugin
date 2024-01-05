export default {
    state: {
        layers: [],
        activeObject: null,
        propertiesDisplay: false,
        worldColour: '#284a18',
        voidColour: '#161616',
        worldX: '300',
        worldY: '300',
        objects: {},
        objectOptions: {}
    },
    getters: {
        activeObject(state) {
            return state.activeObject && state.layers[state.activeObject[0]][state.activeObject[1]] || null;
        },
        objects(state) {
            return state.objects;
        },
        objectOptions(state) {
            return state.objectOptions;
        },
        worldColour(state) {
            return state.worldColour;
        },
        voidColour(state) {
            return state.voidColour;
        },
        worldX(state) {
            return state.worldX;
        },
        worldY(state) {
            return state.worldY;
        },
        layer: (state) => (id) => {
            return state.layers[id]
        },
        world(state) {
            return {
                background: state.worldColour,
                void: state.voidColour,
                level: {
                    size: [
                        [0, 0],
                        [state.worldX, state.worldY]
                    ]
                },
                layers: state.layers
            };
        }
    },
    mutations: {
        setYamlPreview(state, markup) {
            state.yamlPreview = markup;
        },
        setWorldColour(state, value) {
            state.worldColour = value;
        },
        setActiveObject(state, active) {
            state.activeObject = active;
        },
        setVoidColour(state, value) {
            state.voidColour = value;
        },
        setWorldX(state, value) {
            state.worldX = value;
        },
        setWorldY(state, value) {
            state.worldY = value;
        },
        setLayers(state, value) {
            state.layers = value;
        },
        setObjects(state, value) {
            state.objects = value;
        },
        setObjectOptions(state, value) {
            state.objectOptions = value;
        },
        deleteObject(state, address) {
            state.layers[address[0]].splice(address[1], 1);
        },
        copyObject(state, address) {
            state.layers[address[0]].splice(address[1], 0, JSON.parse(JSON.stringify(state.layers[address[0]][address[1]])));
        }
    },
    actions: {
        setActiveField({ state, commit }, field) {
            // Display the properties form
            commit('setPropertiesDisplay', true);

            // We can just skip this if the user has double-clicked the field
            if (field === state.activeField) {
                return;
            }

            state.activeField = field;
            state.propertiesMarkup = null;
        },
        createLayerObject({ state, commit }, layer) {
            if (!state.layers[layer]) {
                state.layers[layer] = [];
            }

            commit('setActiveObject', [
                layer,
                state.layers[layer].push({
                    "settings": {
                        "colour": "#1FC0C8",
                    },
                    "class": "",
                    "vector": {
                        "x": (state.worldX / 2) - 16,
                        "y": (state.worldY / 2) - 16
                    },
                    "size": {
                        "x": 32,
                        "y": 32
                    }
                })
            ]);
        },
        decodeWorld({ commit }, world) {
            if (world.background) {
                commit('setWorldColour', world.background);
            }

            if (world.void) {
                commit('setVoidColour', world.void);
            }

            if (world.level.size[1][0]) {
                commit('setWorldX', world.level.size[1][0]);
            }

            if (world.level.size[1][1]) {
                commit('setWorldY', world.level.size[1][1]);
            }

            if (world.layers) {
                commit('setLayers', world.layers);
            }
        },
        onEditorUpdate({ dispatch }, value) {
            if (!value) {
                value = {};
            }

            if (typeof value === 'string') {
                value = JSON.parse(value);
            }

            dispatch('decodeWorld', value);
        },
        onRegisterObjects({ commit }, complete) {
            this.request("onRegisterObjects", {
                success: (response) => {
                    if (response.objects) {
                        commit('setObjects', response.objects);
                    }
                    if (response.objectOptions) {
                        commit('setObjectOptions', response.objectOptions);
                    }
                    complete();
                }
            });
        },
    }
};
