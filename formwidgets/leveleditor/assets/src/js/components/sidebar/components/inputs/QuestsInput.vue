<template>
    <div class="relative my-2">
        <label><slot></slot></label>
        <div v-for="quest in quests" class="rounded-lg shadow-lg bg-gray-200 p-3 mb-6">
            <div class="flex justify-between">
                <span class="py-2 font-bold">{{quest}}</span>
                <span v-on:click="destroy(quest)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 text-center cursor-pointer rounded-full">Delete</span>
            </div>
        </div>
        <div>
            <div>
                <div class="flex rounded-full shadow">
                    <div class="h-14 relative bg-white rounded-l-full w-full overflow-hidden">
                        <input ref="itemCode" v-on:keyup.enter="create" type="text" placeholder="Quest code" class="px-6 w-full font-medium h-full border-0 rounded-l-full bg-transparent">
                    </div>
                    <span v-on:click="create" class="bg-green-500 hover:bg-green-700 text-white font-bold align-middle py-2 px-4 text-center cursor-pointer rounded-r-full">Add</span>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ['quests'],
    data: function () {
        return {
            items: this.quests || []
        }
    },
    watch: {
        items: {
            handler(value) {
                this.$emit('update:quests', value)
            },
            deep: true
        }
    },
    methods: {
        create() {
            if (!this.$refs.itemCode.value || this.items.indexOf(this.$refs.itemCode.value) > -1) {
                return;
            }

            this.items.push(this.$refs.itemCode.value);

            this.$refs.itemCode.value = '';
        },
        destroy(code) {
            const index = this.items.indexOf(code);
            if (index === -1) {
                return;
            }

            this.items.splice(index, 1);
        }
    }
}
</script>
