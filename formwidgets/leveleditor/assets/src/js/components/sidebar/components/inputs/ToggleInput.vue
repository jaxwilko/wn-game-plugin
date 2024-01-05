<template>
    <div class="flex items-center mt-6 mb-3">
        <span v-on:click="toggle = !toggle"
              :class="`${toggle ? 'bg-blue-600' : 'bg-gray-200'} relative inline-flex h-10 w-20 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 shadow-lg`"
              role="switch"
              aria-checked="false"
              aria-labelledby="annual-billing-label"
        >
            <span aria-hidden="true"
                  :class="`${toggle ? 'translate-x-10' : 'translate-x-0'} pointer-events-none inline-block h-10 w-10 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out`"
            ></span>
        </span>
        <span class="ml-3" id="annual-billing-label">
            <span class="font-medium text-gray-900"><slot></slot></span>
        </span>
    </div>
</template>
<script>
export default {
    props: ['number'],
    components: {},
    computed: {
        toggle: {
            get() {
                return this.value;
            },
            set(value) {
                this.value = value;
                this.$emit('update:toggle', value)
            }
        }
    },
    data: () => {
        return {
            value: false
        }
    }
}
</script>
