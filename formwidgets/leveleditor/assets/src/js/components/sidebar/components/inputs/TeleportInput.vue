<template>
    <div class="relative my-3">
        <label>Target</label>
        <div class="h-14 relative bg-white rounded-full shadow overflow-hidden">
            <input type="text" :value="target" placeholder="10,10" @input="$emit('update:target', $event.target.value)" class="px-6 w-full font-medium h-full border-0 rounded-full bg-transparent">
        </div>
    </div>
    <div class="relative mb-3">
        <label>Target Level</label>
        <div class="h-14 relative bg-white rounded-full shadow overflow-hidden">
            <input type="text" :value="level" placeholder="Leave blank for current level"  @input="$emit('update:level', $event.target.value)" class="px-6 w-full font-medium h-full border-0 rounded-full bg-transparent">
        </div>
    </div>
</template>
<script>
export default {
    props: ['level', 'target'],
    computed: {}
}
</script>
