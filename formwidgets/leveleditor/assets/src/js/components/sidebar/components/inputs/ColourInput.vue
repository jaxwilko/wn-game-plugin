<template>
    <div class="relative">
        <label><slot></slot></label>
        <div class="h-14 relative bg-white rounded-full shadow overflow-hidden">
            <input type="text" disabled :value="colour" class="px-6 pl-16 w-full font-medium h-full border-0 rounded-full bg-transparent">
            <span class="absolute left-3 w-9 h-9 top-1/2 transform -translate-y-1/2 rounded-full overflow-hidden" style="box-shadow: rgb(0 0 0 / 20%) 0px 0px 0px 2px">
                <input type="color" :value="colour" @input="$emit('update:colour', $event.target.value)" class="border-none cursor-pointer scale-[2]" style="-webkit-appearance:none;">
            </span>
        </div>
    </div>
</template>
<script>
export default {
    props: ['colour'],
    components: {},
    computed: {
    }
}
</script>
