<template>
    <div class="relative my-2">
        <label><slot></slot></label>
        <div v-for="(quantity, code) in items" class="rounded-lg shadow-lg bg-gray-200 p-3 mb-6">
            <div class="flex justify-between">
                <span class="py-2">Item: <span class="font-bold">{{code}}</span></span>
                <span v-on:click="destroy(code)" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 text-center cursor-pointer rounded-full">Delete</span>
            </div>
            <div>
                <NumberInput v-model:number="items[code]">Quantity</NumberInput>
            </div>
        </div>
        <div>
            <div>
                <div class="flex rounded-full shadow">
                    <div class="h-14 relative bg-white rounded-l-full w-full overflow-hidden">
                        <input ref="itemCode" v-on:keyup.enter="create" type="text" placeholder="Item code" class="px-6 w-full font-medium h-full border-0 rounded-l-full bg-transparent">
                    </div>
                    <span v-on:click="create" class="bg-green-500 hover:bg-green-700 text-white font-bold align-middle py-2 px-4 text-center cursor-pointer rounded-r-full">Add</span>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import NumberInput from "./NumberInput.vue";

export default {
    props: ['inventory'],
    components: {NumberInput},
    data: function () {
        return {
            items: this.inventory || {}
        }
    },
    watch: {
        items: {
            handler(value) {
                this.$emit('update:inventory', value)
            },
            deep: true
        }
    },
    methods: {
        create() {
            if (
                !this.$refs.itemCode.value
                || (
                    typeof this.items[this.$refs.itemCode.value] !== 'undefined'
                    && this.items[this.$refs.itemCode.value]
                )
            ) {
                return;
            }

            this.items[this.$refs.itemCode.value] = 1;

            this.$refs.itemCode.value = '';
        },
        destroy(code) {
            delete this.items[code];
        }
    }
}
</script>
