<script setup lang="ts">

import { defineProps } from 'vue';

const props = withDefaults(defineProps<{
    blocking?: boolean
}>(), {
    blocking: false
});

const open = defineModel<boolean>('open', {
    required: true,
});

function onBackdropClick(event: MouseEvent) {
    if (props.blocking) return;

    // only the background, otherwise it will close if we click on inputs
    if (event.target === event.currentTarget) {
        open.value = false;
    }
}
</script>

<template>
    <div v-if="open" class="modal-backdrop" @click="onBackdropClick">
        <div class="modal">
            <header v-if="$slots.header">
                <h4><slot name="header"></slot></h4>
            </header>

            <div class="modal__content">
                <slot></slot>
            </div>

            <footer class="is-right" v-if="$slots.footer">
                <slot name="footer"></slot>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.modal-backdrop {
    position: fixed;
    inset: 0;
    display: grid;
    place-items: center;
    background: rgba(0, 0, 0, 0.5);
    padding: 24px;
    z-index: 1200;
    overscroll-behavior: contain;
}

.modal {
    width: min(600px, 100%);
    max-height: 90vh;
    border-radius: 4px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #fff;
}

@media (max-width: 720px) {
    .modal-backdrop {
        padding: 0;
    }

    .modal {
        width: 100vw;
        max-height: 100dvh;
        border-radius: 0;
    }
}

header,
footer {
    padding: 16px 20px;
    background: #fff;
}

header {
    display: flex;
    align-items: center;
    gap: 12px;
    justify-content: space-between;
    border-bottom: 1px solid rgba(0, 0, 0, 0.08);
}

h4 {
    margin: 0;
}

.modal__content {
    padding: 16px 20px;
    overflow: auto;
    -webkit-overflow-scrolling: touch;
    flex: 1;
    min-height: 0;
    overscroll-behavior: contain;
}

footer {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    border-top: 1px solid rgba(0, 0, 0, 0.08);
}
</style>
