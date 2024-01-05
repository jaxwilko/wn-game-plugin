<template>
    <div v-if="folded" v-on:click="toggleFold" class="relative text-gray-700 my-3 bg-gray-300 p-4 shadow rounded block cursor-pointer">
        <span class="h-3 w-3 p-1 rounded-full shadow-lg border-1 border-white" :style="'background: ' + object.settings.colour">&nbsp;</span>
        <span class="ml-3 whitespace-wrap">{{className}}</span>
        <Chevron :folded="folded"></Chevron>
    </div>
    <div v-else class="text-gray-700 my-3 bg-gray-300 p-4 shadow rounded">
        <div class="relative flex justify-start mb-6">
            <Chevron v-on:click="toggleFold" :folded="folded" amount="2"></Chevron>
            <span v-if="object === this.$store.getters.activeObject" v-on:click="deselect" class="bg-green-800 hover:bg-green-900 text-white font-bold py-2 px-4 text-center cursor-pointer rounded-full shadow-lg">Deselect</span>
            <span v-else v-on:click="select" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 text-center cursor-pointer rounded-full shadow-lg">Select</span>
            <span v-on:click="copy" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 mx-3 text-center cursor-pointer rounded-full shadow-lg">Copy</span>
            <span v-on:click="destroy" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 text-center cursor-pointer rounded-full shadow-lg">Delete</span>
        </div>
        <div class="grid grid-cols-2 gap-x-6 gap-y-3 mb-2">
            <ColourInput v-model:colour="object.settings.colour">Colour</ColourInput>
            <div class="relative">
                <label>Type</label>
                <div class="h-14 relative bg-white rounded-full shadow overflow-hidden">
                    <select v-model="object.class" class="px-6 w-full font-medium h-full border-0 rounded-full bg-transparent">
                        <option v-for="(label, type) in types[layer]" :value="type">{{label}}</option>
                    </select>
                </div>
            </div>
            <NumberInput v-model:number="object.vector.x">Vector X</NumberInput>
            <NumberInput v-model:number="object.vector.y">Vector Y</NumberInput>
            <NumberInput v-model:number="object.size.x">Size X</NumberInput>
            <NumberInput v-model:number="object.size.y">Size Y</NumberInput>
        </div>
        <div>
            <template v-for="option in options">
                <TeleportInput v-if="option === 'teleport'" v-model:level="object.settings.level" v-model:target="object.settings.target"></TeleportInput>
                <SpriteMapInput v-if="option === 'spriteMap'" v-model:spriteMap="object.settings.spriteMap">Sprite Map</SpriteMapInput>
                <ToggleInput v-if="option === 'animationRandomDelay'" v-model:toggle="object.settings.animationRandomDelay">Add Animation Random Delay</ToggleInput>
                <ScriptInput v-if="option === 'script'" v-model:script="object.settings.script">Game Script</ScriptInput>
                <TextInput v-if="option === 'item'" v-model:text="object.settings.code">Item Code</TextInput>
                <TextInput v-if="option === 'inventory'" v-model:text="object.settings.containerName">Container Name</TextInput>
                <InventoryInput v-if="option === 'inventory'" v-model:inventory="object.settings.items">Inventory</InventoryInput>
                <QuestsInput v-if="option === 'quests'" v-model:quests="object.settings.quests">Quests</QuestsInput>
                <TextInput v-if="option === 'name'" v-model:text="object.settings.name">Name</TextInput>
                <ToggleInput v-if="option === 'invulnerable'" v-model:toggle="object.settings.invulnerable">Invulnerable</ToggleInput>
                <ToggleInput v-if="option === 'playersOnly'" v-model:toggle="object.settings.playersOnly">Players Only</ToggleInput>
            </template>
        </div>
    </div>
</template>
<script>
import ColourInput from "./inputs/ColourInput.vue";
import NumberInput from "./inputs/NumberInput.vue";
import SpriteMapInput from "./inputs/SpriteMapInput.vue";
import ScriptInput from "./inputs/ScriptInput.vue";
import Chevron from "./icons/Chevron.vue";
import ToggleInput from "./inputs/ToggleInput.vue";
import TeleportInput from "./inputs/TeleportInput.vue";
import TextInput from "./inputs/TextInput.vue";
import InventoryInput from "./inputs/InventoryInput.vue";
import QuestsInput from "./inputs/QuestsInput.vue";

export default {
    props: ['object', 'layer', 'index'],
    components: {
        QuestsInput,
        Chevron,
        ColourInput,
        InventoryInput,
        NumberInput,
        ScriptInput,
        SpriteMapInput,
        TeleportInput,
        TextInput,
        ToggleInput,
    },
    computed: {
        types: {
            get() {
                return this.$store.getters.objects;
            }
        },
        options: {
            get() {
                return this.$store.getters.objectOptions[this.object.class] || [];
            }
        },
        className: {
            get() {
                return this.object.class.replace(/^.*[\\/]/, '');
            }
        }
    },
    data: () => {
        return {
            type: null,
            folded: true
        }
    },
    methods: {
        destroy() {
            this.$store.commit('deleteObject', [this.layer, this.index]);
        },
        select() {
            this.$store.commit('setActiveObject', [this.layer, this.index]);
        },
        deselect() {
            this.$store.commit('setActiveObject', null);
        },
        copy() {
            this.$store.commit('copyObject', [this.layer, this.index]);
        },
        toggleFold() {
            this.folded = !this.folded;
        }
    }
}
</script>
