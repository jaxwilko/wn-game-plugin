<template>
    <div class="relative">
        <label><slot></slot></label>
        <div class="h-14 relative bg-white rounded-full shadow overflow-hidden">
            <input type="number" :value="number" @input="$emit('update:number', $event.target.value)" class="px-6 w-full font-medium h-full border-0 rounded-full bg-transparent">
        </div>
    </div>
</template>
<script>
export default {
    props: ['number'],
    components: {},
    computed: {
    }
}
</script>
