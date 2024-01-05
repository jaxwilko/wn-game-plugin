<template>
    <div
        ref="window"
        v-on:DOMAttrModified="console.log(e)"
         class="absolute bg-gray-500/70 overflow-auto rounded-md shadow-lg"
         :style="(
             folded
                 ? `height: 34px; width: ${windowWidth}px; resize: horizontal;`
                 : `height: ${windowHeight}px; width: ${windowWidth}px; resize: both; min-height: 130px;`
             ) + ` top: ${windowTop}px; left: ${windowLeft}px;`
             + (maxHeight ? `max-height: ${maxHeight}px;` : '')
             + (maxWidth ? `max-width: ${maxWidth}px;` : '')
        "
    >
        <div class="relative h-full w-full">
            <div
                v-on:mousedown="this.dragging = true"
                v-on:mousemove="drag"
                class="bg-gray-600/70 select-none w-full p-2 cursor-grab absolute top-0 left-0 shadow-lg"
            >
                <span class="text-white">{{title}}</span>
                <Chevron v-on:click="folded = !folded" :folded="folded" amount="2"></Chevron>
            </div>
            <div :class="(folded ? 'hidden' : '') + ' p-2 pt-16 overflow-auto h-full w-full'">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script>
import Chevron from "../icons/Chevron.vue";

export default {
    name: "Window",
    components: {Chevron},
    props: ['title', 'top', 'left', 'width', 'height', 'maxWidth', 'maxHeight'],
    data: function () {
        return {
            dragging: false,
            folded: false,
            windowHeight: parseInt(this.height ? this.height : 200),
            windowWidth: parseInt(this.width ? this.width : 400),
            windowTop: parseInt(this.top ? this.top : 0),
            windowLeft: parseInt(this.left ? this.left : 0)
        };
    },
    methods: {
        drag(e) {
            if (this.dragging) {
                this.windowTop += e.movementY;

                if (this.windowTop < 0) {
                    this.windowTop = 0;
                }

                if (this.windowTop + this.windowHeight > document.body.clientHeight) {
                    this.windowTop = document.body.clientHeight - this.windowHeight;
                }

                this.windowLeft += e.movementX;

                if (this.windowLeft < 0) {
                    this.windowLeft = 0;
                }

                if (this.windowLeft + this.windowWidth > document.body.clientWidth) {
                    this.windowLeft = document.body.clientWidth - this.windowWidth;
                }
            }
        },
        resize(e) {
            if (!this.folded) {
                this.windowHeight = e[0].contentRect.height;
            }

            this.windowWidth = e[0].contentRect.width;
        }
    },
    mounted() {
        new ResizeObserver(this.resize).observe(this.$refs.window);

        window.addEventListener('mouseup', () => {
            this.dragging = false;
        });
    }
};
</script>
