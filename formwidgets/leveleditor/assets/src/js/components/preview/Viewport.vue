<template>
   <canvas ref="drawer" width="100%" style="min-height: 800px"></canvas>
</template>
<script>
import Canvas from '../../../../../../../classes/engine/client/src/renderer/canvas';
import Vector from "../../../../../../../classes/engine/client/src/utils/vector";

export default {
    components: {},
    computed: {
    },
    data: () => {
        return {
            canvas: null,
            camera: {
                size: new Vector(0, 0),
                vector: new Vector(0, 0),
            }
        }
    },
    mounted() {
        this.canvas = new Canvas(this.$refs.drawer);
        this.camera.size.x = this.canvas.canvas.width;
        this.camera.size.y = this.canvas.canvas.height;
        this.camera.vector.x = (this.canvas.canvas.width / 2) - (this.$store.getters.world.level.size[1][0] / 2);
        this.camera.vector.y = (this.canvas.canvas.height / 2) - (this.$store.getters.world.level.size[1][1] / 2);

        const scaleCanvas = () => {
            this.canvas.resize(this.camera);
            this.canvas.canvas.style.width = '100%';
            this.canvas.canvas.style.height = '100%';
            this.canvas.canvas.width = this.canvas.canvas.offsetWidth;
            this.canvas.canvas.height = this.canvas.canvas.offsetHeight;
        };

        new ResizeObserver(scaleCanvas).observe(this.$refs.drawer);
        scaleCanvas();

        const tick = () => {
            this.canvas.drawBackground(this.$store.getters.voidColour);
            this.canvas.renderWorld(this.$store.getters.world, this.camera);

            const activeObject = this.$store.getters.activeObject;

            if (activeObject) {
                this.canvas.renderHighlight(this.$store.getters.world, activeObject, this.camera)
            }

            window.requestAnimationFrame(tick);
        };

        tick();

        let dragging = false;
        let mouseMoved = false;

        this.$refs.drawer.addEventListener('mousedown', (e) => {
            dragging = true;
        });

        this.$refs.drawer.addEventListener('mouseup', (e) => {
            if (dragging && !mouseMoved) {
                const activeObject = this.$store.getters.activeObject;

                if (!activeObject || mouseMoved) {
                    return;
                }

                const world = this.$store.getters.world;
                const r = this.canvas.canvas.getBoundingClientRect();

                activeObject.vector.x = Math.round((e.clientX - r.left) - (world.level.size[0][0] - this.camera.vector.x));
                activeObject.vector.y = Math.round((e.clientY - r.top) - (world.level.size[0][1] - this.camera.vector.y));
            }

            dragging = false;
            mouseMoved = false;
        })

        this.$refs.drawer.addEventListener('mousemove', (e) => {
            if (dragging) {
                mouseMoved = true;
                this.camera.vector.x -= e.movementX
                this.camera.vector.y -= e.movementY
            }
        });

        const keyMap = {
            ArrowUp: "up",
            ArrowDown: "down",
            ArrowLeft: "left",
            ArrowRight: "right",
            Escape: "deselect",
        };
        //
        // window.addEventListener("keydown", function (e) {
        //     const activeObject = this.$store.getters.activeObject;
        //     if (activeObject && keyMap[e.code]) {
        //         _this.active[keyMap[e.code]] = true;
        //     }
        // });
        window.addEventListener("keydown", (e) => {
            const activeObject = this.$store.getters.activeObject;
            if (!activeObject || !keyMap[e.code] || document.activeElement.nodeName !== 'BODY') {
                return;
            }

            e.preventDefault();

            switch (keyMap[e.code]) {
                case 'up':
                    activeObject.vector.y--;
                    break;
                case 'left':
                    activeObject.vector.x--;
                    break;
                case 'right':
                    activeObject.vector.x++;
                    break;
                case 'down':
                    activeObject.vector.y++;
                    break;
                case 'deselect':
                    this.$store.commit('setActiveObject', null);
                    break;
            }
        });
    }
}
</script>
