<template>
    <div v-if="!loaded">
        Loading...
    </div>
    <template v-else>
        <div class="grid md:grid-cols-2">
            <div v-on:click="viewMode = 'visual'" :class="`${viewMode === 'visual' ? 'bg-blue-400' : 'bg-blue-200'} text-center p-4 hover:bg-blue-500 text-white cursor-pointer`">Visual</div>
            <div v-on:click="viewMode = 'json'" :class="`${viewMode === 'json' ? 'bg-blue-400' : 'bg-blue-200'} text-center p-4 hover:bg-blue-500 text-white cursor-pointer`">JSON</div>
        </div>
        <div :class="`${viewMode === 'visual' ? '' : 'hidden'} flex flex-col md:flex-row`">
            <div class="w-full lg:w-3/8 lg:max-w-lg lg:min-w-[400px] pb-6 bg-gray-200 border border-gray-300">
                <Sidebar></Sidebar>
            </div>
            <div class="w-full bg-white border border-gray-300 border-l-0">
                <Viewport></Viewport>
            </div>
        </div>
        <div :class="`${viewMode === 'json' ? '' : 'hidden'} w-full`">
            <JsonEditorVue v-model="world" mode="text" class="jse-theme-dark"></JsonEditorVue>
        </div>
        <!-- End value container for the Winter form -->
        <textarea :id="id" :name="name" :value="JSON.stringify(world)" class="hidden"></textarea>
    </template>
</template>
<script>
import Sidebar from "./sidebar/Sidebar";
import Viewport from "./preview/Viewport";
import JsonEditorVue from 'json-editor-vue';

export default {
    components: {JsonEditorVue, Sidebar, Viewport},
    props: ['id', 'name', 'value'],
    computed: {
        world: {
            get() {
                return this.$store.getters.world;
            },
            set(value) {
                return this.$store.dispatch('onEditorUpdate', value);
            }
        }
    },
    data: () => {
        return {
            loaded: false,
            viewMode: 'visual'
        }
    },
    mounted() {
        if (this.value) {
            this.$store.dispatch('onEditorUpdate', this.value);
        }

        this.$store.dispatch('onRegisterObjects', () => {
            this.loaded = true;
        });
    }
}
</script>
