<template>
    <div :class="`${!running ? 'flex' : 'hidden'} h-screen flex items-center justify-center bg-gray-900`">
        <div class="bg-gray-200 text-gray-700 p-6 rounded-lg shadow-xl">
            The game server is <span class="text-red-600">offline</span>, you can start it by running:
            <pre class="my-3 bg-white border border-gray-300 shadow rounded-lg"><span class="text-green-600">./artisan</span> game:serve -f -m LEVEL_NAME</pre>
        </div>
    </div>
    <div :class="running ? '' : 'hidden'">
        <canvas ref="canvas" width="640" height="480"></canvas>
        <div class="fixed flex bottom-1 w-full">
            <Player></Player>
        </div>
        <Window title="Chat" top="15" left="15" width="400" height="200">
            <Console></Console>
        </Window>

        <Window v-for="dialog in dialogs" :title="dialog.settings.name" top="225" left="15" width="455" height="135">
            <template v-for="quest in dialog.quests">
                <Dialog v-if="availableQuests.indexOf(quest) > -1" :quest="quest" :status="questStatues[quest] || 0"></Dialog>
            </template>
        </Window>

        <Window v-for="container in containers" :title="container.containerName" top="500" left="15" width="300" max-width="300" height="300" max-height="300">
            <Inventory :id="container.id" :items="container.inventory"></Inventory>
        </Window>

        <Window title="Inventory" :top="inventoryDefault" left="15" width="300" max-width="300" height="300" max-height="300">
            <Inventory id="player" :items="inventory"></Inventory>
        </Window>
    </div>
</template>

<script>
import Console from "./ui/Console.vue";
import Player from "./ui/Player.vue";
import Inventory from "./ui/Inventory.vue";
import Window from "./ui/Window.vue";
import layers from '../src/utils/layers';
import Dialog from "./ui/Dialog.vue";

export default {
    name: "Game",
    components: {Dialog, Window, Player, Inventory, Console},
    props: [],
    computed: {
        inventoryDefault: {
            get() {
                return window.innerHeight - 315;
            }
        }
    },
    data: () => {
        return {
            running: false,
            containers: {},
            dialogs: {},
            inventory: {},
            availableQuests: [],
            questStatues: {}
        };
    },
    created() {
        this.$nextTick(() => {
            this.$createEngine(this.$refs.canvas, (status) => this.running = status);

            this.$engine.events.register('tick', (engine) => {
                if (!engine.state?.player || !engine.state?.world.layers[layers.LAYER_TRIGGERS]) {
                    return;
                }

                if (engine?.state?.player?.inventory) {
                    // delete inv items not in inventory
                    for (let i in this.inventory) {
                        if (typeof engine.state.player.inventory[i] === 'undefined') {
                            delete this.inventory[i];
                        }
                    }
                    // create / update items
                    for (let i in engine.state.player.inventory) {
                        if (this.inventory[i]) {
                            this.inventory[i].quantity = engine.state.player.inventory[i].quantity;
                            continue;
                        }

                        this.inventory[i] = engine.state.player.inventory[i];
                    }
                }

                if (engine?.state?.player?.availableQuests) {
                    this.availableQuests = engine?.state?.player?.availableQuests;
                }

                if (engine?.state?.player?.questStatues) {
                    this.questStatues = engine?.state?.player?.questStatues;
                }

                if (engine.state.world.layers[layers.LAYER_TRIGGERS]) {
                    engine.state.world.layers[layers.LAYER_TRIGGERS].forEach((object) => {
                        if (
                            typeof object.inventory !== 'undefined'
                            && typeof object.players !== 'undefined'
                            && object.players.length
                            && object.players.indexOf(engine.state?.player.id) > -1
                        ) {
                            this.containers[object.id] = object;
                        } else if (this.containers[object.id]) {
                            delete this.containers[object.id];
                        }
                    });
                }

                const availableDialogs = {};
                if (engine.state.world.layers[layers.LAYER_ACTORS]) {
                    engine.state.world.layers[layers.LAYER_ACTORS].forEach((object) => {
                        if (
                            typeof object.players !== 'undefined'
                            && object.players.length
                            && object.players.indexOf(engine.state?.player.id) > -1
                        ) {
                            availableDialogs[object.id] = object;
                        }
                    });
                }
                // remove dialogs not available
                for (let i in this.dialogs) {
                    if (typeof availableDialogs[i] === "undefined") {
                        delete this.dialogs[i];
                    }
                }
                // fill in available dialogs
                for (let i in availableDialogs) {
                    if (typeof this.dialogs[i] === "undefined") {
                        this.dialogs[i] = availableDialogs[i];
                    }
                }
            });
        });
    }
};
</script>
