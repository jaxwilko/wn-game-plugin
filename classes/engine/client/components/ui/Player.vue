<template>
    <div class="p:2 sm:p-6 m-auto flex flex-col w-2/6">
        <div class="mb-3">
            <div class="flex justify-between mb-1">
                <span class="text-base font-medium text-white">Health</span>
                <span class="text-sm font-medium text-white">{{ health }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div class="bg-blue-600 h-2.5 rounded-full" :style="'width:' + health + '%'"></div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Player",
    props: [],
    data: () => {
        return {
            health: 100
        };
    },
    created() {
        this.$nextTick(() => {
            this.$engine.events.register('tick', (engine) => {
                if (engine.state?.player) {
                    this.health = engine.state.player.health;
                }
            })
        });
    }
};
</script>
