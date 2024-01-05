<template>
    <div class="relative">
        <label><slot></slot></label>
        <div class="h-14 relative bg-white rounded-full shadow overflow-hidden">
            <input type="text" :value="text" @input="$emit('update:text', $event.target.value)" class="px-6 w-full font-medium h-full border-0 rounded-full bg-transparent">
        </div>
    </div>
</template>
<script>
export default {
    props: ['text'],
    components: {},
    computed: {
    }
}
</script>
