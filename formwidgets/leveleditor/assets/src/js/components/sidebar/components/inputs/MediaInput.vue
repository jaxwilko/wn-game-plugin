<template>
    <div class="relative">
        <label><slot></slot></label>
        <div class="h-14 relative bg-white rounded-full shadow overflow-hidden">
            <input type="text" readonly v-on:click="pick" :value="fileName" class="px-6 w-full font-medium h-full border-0 rounded-full whitespace-nowrap bg-transparent">
        </div>
    </div>
</template>
<script>
export default {
    props: ['media'],
    computed: {
        fileName: {
            get() {
                return this.media.replace(/^.*[\\/]/, '');
            }
        }
    },
    methods: {
        pick() {
            const popup = new $.wn.mediaManager.popup({
                alias: 'ocmediamanager',
                cropAndInsertButton: false,
                onInsert: (items) => {
                    if (!items.length || items.length > 1) {
                        return;
                    }

                    this.$emit('update:media', items[0].publicUrl);

                    popup.hide();
                }
            });
        }
    }
}
</script>
