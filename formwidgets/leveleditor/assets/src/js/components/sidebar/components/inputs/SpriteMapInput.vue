<template>
    <div class="relative my-2">
        <label><slot></slot></label>
        <div v-for="(settings, state) in sprite" class="rounded-lg shadow-lg bg-gray-200 p-3 mb-6">
            <div class="flex justify-between">
                <span class="py-2">State: <span class="font-bold">{{state}}</span></span>
                <span v-on:click="destroy(state)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 text-center cursor-pointer rounded-full">Delete</span>
            </div>
            <div v-if="settings.sheet" class="flex text-center w-full p-2 justify-center">
                <img :src="settings.sheet" class="max-w-full" :alt="state">
            </div>
            <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                <MediaInput v-model:media="settings.sheet">Sprite Sheet</MediaInput>
                <NumberInput v-model:number="settings.delay">Frame Delay</NumberInput>
                <NumberInput v-model:number="settings.align[0]">Align X</NumberInput>
                <NumberInput v-model:number="settings.align[1]">Align Y</NumberInput>
            </div>
        </div>
        <div>
            <div>
                <label for="hs-trailing-button-add-on" class="sr-only">Label</label>
                <div class="flex rounded-full shadow">
                    <div class="h-14 relative bg-white rounded-l-full w-full overflow-hidden">
                        <input ref="stateLabel" v-on:keyup.enter="create" type="text" placeholder="Add animation state" class="px-6 w-full font-medium h-full border-0 rounded-l-full bg-transparent">
                    </div>
                    <span v-on:click="create" class="bg-green-500 hover:bg-green-700 text-white font-bold align-middle py-2 px-4 text-center cursor-pointer rounded-r-full">Create</span>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import NumberInput from "./NumberInput.vue";
import MediaInput from "./MediaInput.vue";

export default {
    props: ['spriteMap'],
    components: {MediaInput, NumberInput},
    data: function () {
        return {
            sprite: this.spriteMap || {}
        }
    },
    watch: {
        sprite: {
            handler(value) {
                this.$emit('update:spriteMap', value)
            },
            deep: true
        }
    },
    methods: {
        create() {
            if (
                !this.$refs.stateLabel.value
                || (
                    typeof this.sprite[this.$refs.stateLabel.value] !== 'undefined'
                    && this.sprite[this.$refs.stateLabel.value]
                )
            ) {
                return;
            }

            this.sprite[this.$refs.stateLabel.value] = {
                sheet: '',
                align: [32, 32],
                delay: 20
            };

            this.$refs.stateLabel.value = '';
        },
        destroy(state) {
            delete this.sprite[state];
        }
    }
}
</script>
