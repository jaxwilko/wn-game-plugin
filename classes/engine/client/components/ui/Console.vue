<template>
    <div class="relative w-full h-full">
        <div ref="chatPanel" class="chat-panel flex text-gray-200 flex-col space-y-4 p-3 pb-24 overflow-y-scroll h-full scrollbar-thumb-blue scrollbar-thumb-rounded scrollbar-track-blue-lighter scrollbar-w-2 scrolling-touch">
            <div v-for="message in messages" class="chat-message">
                <div class="flex items-end">
                    <div class="flex flex-col space-y-2 text-md mx-2 order-2 items-start">
                        <div>
                            [{{message.humanTime}}] {{ message.user }}: {{ message.content }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 w-full px-2 pt-4 mb-2">
            <div class="relative flex">
                <input ref="input"
                       type="text"
                       placeholder="Message"
                       v-on:keyup.enter="send"
                       v-on:keyup.esc="deselect"
                       v-on:keyup.up="setHistoric(-1)"
                       v-on:keyup.down="setHistoric(1)"
                       class="w-full focus:outline-none focus:placeholder-gray-200 text-gray-600 placeholder-gray-600 pl-3 bg-gray-200 rounded-md shadow-lg py-3 mr-6"
                >
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: "Console",
    computed: {
        sortedMessages: {
            get() {
                return this.messages.sort((a, b) => a.time >= b.time ? 1 : 0);
            }
        }
    },
    data: () => {
        return {
            messages: {},
            sent: [],
            index: 0
        };
    },
    methods: {
        send() {
            if (!this.$refs.input.value) {
                return;
            }

            this.$engine.console.send(this.$refs.input.value);
            this.sent.push(this.$refs.input.value);
            this.$refs.input.value = '';

            this.index = this.sent.length;
        },
        deselect(e) {
            e.target.blur();
        },
        setHistoric(dir) {
            this.index += dir;

            if (this.index < 0) {
                this.index = 0;
            }

            this.$refs.input.value = this.sent[this.index] || '';
        },
        pushMessages(messages) {
            for (const message in messages) {
                this.messages[message] = messages[message];
            }

            this.$refs.chatPanel.scrollTo(0, this.$refs.chatPanel.scrollHeight, {
                behavior: 'smooth',
            });
        }
    },
    created() {
        this.$nextTick(() => {
            this.$engine.console.bind(this);
        });

        window.addEventListener("keydown", (e) => {
            if (e.target.nodeName === "BODY" && e.key === 'Enter') {
                this.$refs.input.focus();
            }
        });
    }
};
</script>
