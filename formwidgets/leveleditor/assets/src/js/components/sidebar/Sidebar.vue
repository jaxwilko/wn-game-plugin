<template>
    <div class="overflow-x-auto">
        <div>
            <div class="p-6 bg-blue-100 cursor-pointer relative" v-on:click="foldedSettings = !foldedSettings">
                Level Settings
                <Chevron :folded="foldedSettings"></Chevron>
            </div>
            <div v-if="!foldedSettings" class="flex flex-col p-6 font-lg">
                <div class="grid grid-cols-2 gap-x-6 gap-y-3 text-gray-700">
                    <ColourInput v-model:colour="voidColour">Void Colour</ColourInput>
                    <ColourInput v-model:colour="worldColour">World Colour</ColourInput>
                    <NumberInput v-model:number="worldX">World X</NumberInput>
                    <NumberInput v-model:number="worldY">World Y</NumberInput>
                </div>
            </div>
        </div>
        <div>
            <div class="p-6 bg-blue-100 cursor-pointer relative" v-on:click="foldedLayers = !foldedLayers">
                Layers
                <Chevron :folded="foldedLayers"></Chevron>
            </div>
            <div class="editor-panel flex flex-col font-lg">
                <div v-if="!foldedLayers" v-for="(label, id) in layers">
                    <div class="relative p-6 bg-blue-200 cursor-pointer" v-on:click="showLayers[label] = (typeof showLayers[label] === 'undefined' ? true : !showLayers[label])">
                        {{label}}
                        <span v-if="getLayer(id)?.length" class="inline-flex items-center rounded-md bg-pink-50 px-2 py-1 text-sm align-end font-medium text-pink-700 ring-1 ring-inset ring-pink-700/10">{{getLayer(id)?.length}}</span>
                        <Chevron :folded="!showLayers[label]"></Chevron>
                    </div>
                    <div v-if="showLayers[label]" class="flex flex-col p-6 bg-gray-100 font-lg">
                        <div v-for="(object, index) in getLayer(id)">
                            <Object :object="object" :layer="id" :index="index"></Object>
                        </div>
                        <span v-on:click="addLayerItem(id)" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 text-center cursor-pointer rounded-full">Add item</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import Object from "./components/Object.vue";
import ColourInput from "./components/inputs/ColourInput.vue";
import NumberInput from "./components/inputs/NumberInput.vue";
import Chevron from "./components/icons/Chevron.vue";

export default {
    components: {Chevron, NumberInput, ColourInput, Object},
    data: () => {
        return {
            layers: {
                0: 'Background',
                1: 'Blocks',
                2: 'Props',
                3: 'Triggers',
                4: 'Markers',
                5: 'Actors',
                6: 'Sprites',
                7: 'Props Top',
            },
            foldedSettings: false,
            foldedLayers: false,
            showLayers: {}
        }
    },
    computed: {
        voidColour: {
            get() {
                return this.$store.getters.voidColour;
            },
            set(value) {
                this.$store.commit("setVoidColour", value)
            }
        },
        worldColour: {
            get() {
                return this.$store.getters.worldColour;
            },
            set(value) {
                this.$store.commit("setWorldColour", value)
            }
        },
        worldX: {
            get() {
                return this.$store.getters.worldX;
            },
            set(value) {
                this.$store.commit("setWorldX", value)
            }
        },
        worldY: {
            get() {
                return this.$store.getters.worldY;
            },
            set(value) {
                this.$store.commit("setWorldY", value)
            }
        },
        activeObject: {
            get() {
                return this.$store.getters.activeObject;
            }
        },
    },
    methods: {
        getLayer(id) {
            return this.$store.getters.layer(id);
        },
        disablePropertiesDisplay() {
            this.$store.commit("setPropertiesDisplay", false);
        },
        enablePropertiesDisplay() {
            this.$store.commit("setPropertiesDisplay", true);
        },
        clearSearch() {
            this.search = null;
        },
        addField(field) {
            this.$store.dispatch("addField", field);
        },
        addLayerItem(layer) {
            this.$store.dispatch("createLayerObject", layer);
        }
    },
}
</script>
