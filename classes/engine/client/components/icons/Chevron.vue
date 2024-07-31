<template>
    <!-- right-0 right-1 right-2 right-6 left-0 left-1 left-2 left-6 -->
    <div :class="
            (folded ? '' : 'rotate-180')
            + ' '
            + (dir === 'left' ? 'left-' : 'right-') + (amount ? amount : '6')
            + ' inline-flex cursor-pointer absolute'
    ">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 bg-gray-100 rounded-full shadow-lg p-1">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </div>
</template>
<script>
export default {
    props: ['folded', 'dir', 'amount']
}
</script>
