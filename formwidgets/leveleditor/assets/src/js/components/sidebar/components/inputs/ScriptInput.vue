<template>
    <div class="relative mt-2">
        <label><slot></slot></label>
        <div class="shadow">
            <Codemirror v-model="code"
                        @change="$emit('update:script', $event)"
                        :autofocus="false"
                        :style="{ height: '300px' }"
                        :indent-with-tab="true"
                        :tab-size="4"
                        :extensions="extensions"
            ></Codemirror>
        </div>
    </div>
</template>
<script>
import { Codemirror } from 'vue-codemirror';
import { php } from '@codemirror/lang-php';
import { oneDark } from '@codemirror/theme-one-dark';

export default {
    props: ['script'],
    components: {Codemirror},
    computed: {
        code: {
            get() {
                return this.script;
            }
        }
    },
    data() {
        return {
            extensions: [php({plain: true}), oneDark],
        }
    }
}
</script>
