<template>
    <div class="flex flex-col text-white">
        <div v-on:click="action(quest)" class="cursor-pointer select-none">
            <span :class="`${status === 1 ? 'text-blue-500' : ''} font-bold`">{{quests[quest].title}}:</span> {{quests[quest].description}}
        </div>
    </div>
</template>

<script>
export default {
    name: "Dialog",
    props: ['quest', 'status'],
    computed: {
        quests: {
            get() {
                return this.$engine.quests.get();
            }
        },
    },
    methods: {
        action(quest) {
            this.$engine.network.queue('questAction', quest);
        }
    }
};
</script>
