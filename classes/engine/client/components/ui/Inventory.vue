<template>
    <div class="grid grid-cols-5 gap-3">
        <div v-for="(item, key) in items"
             v-on:click="action(key)"
             v-on:contextmenu.prevent="drop(key)"
             class="relative border border-gray-300 rounded-xl shadow-lg aspect-square p-2 cursor-pointer select-none"
             :title="item.description"
        >
            <img :src="item.icon" class="w-full h-full align-middle">
            <span class="absolute right-0 bottom-0 text-gray-900 font-bold">{{item.quantity}}</span>
        </div>
        <div v-for="count in (20 - itemCount)" v-on:contextmenu.prevent="" class="border border-gray-300 rounded-xl shadow-lg aspect-square p-2 select-none"></div>
    </div>
</template>

<script>
export default {
    name: "Inventory",
    props: ['id', 'items'],
    computed: {
        itemCount: {
            get() {
                return this.items ? Object.entries(this.items).length : 0;
            }
        }
    },
    methods: {
        action(item) {
            this.$engine.network.queue('itemUse', {
                id: this.id,
                item: item
            });
        },
        drop(item) {
            this.$engine.network.queue('itemDrop', {
                id: this.id,
                item: item
            });
        }
    }
};
</script>
