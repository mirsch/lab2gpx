<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import ModalWrap from '@/components/ModalWrap.vue';
import { onMounted, ref } from 'vue';
import { useSettings } from '@/composables/useSettings.ts';

const { t, messages } = useI18n();
const settings = useSettings();

const open = defineModel<boolean>('open', {
    required: true,
});

const rndTextArray:Array<string> = [];
const localeMessages = messages.value[settings.value.locale] as unknown as {downloader: {wait: Array<string>}};
const waitMessages = localeMessages.downloader.wait;
for(let i = 0; i < waitMessages.length; i++) {
    rndTextArray.push(t('downloader.wait.' + i));
}

const rndText = ref<string>('');

function randomText() {
    const rndIdx = Math.floor(Math.random() * rndTextArray.length);
    rndText.value = rndTextArray[rndIdx];
}
onMounted(() => {
    setInterval(randomText, 3000);
    randomText();
});

</script>

<template>
    <ModalWrap v-model:open="open" :blocking="true">
        <div class="loading-container">
            <div class="path">
                <div class="dot"></div>
            </div>
            <p class="message">{{ t('downloader.please_wait') }}</p>
            <p class="message">{{ rndText }}</p>
        </div>
    </ModalWrap>
</template>

<style scoped>
.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-top: 2rem;
}

.path {
    width: 200px;
    height: 8px;
    background: var(--color-lightGrey);
    border-radius: 4px;
    position: relative;
    overflow: hidden;
    margin-bottom: 1rem;
}

.dot {
    width: 16px;
    height: 16px;
    background: var(--color-primary);
    border-radius: 50%;
    position: absolute;
    top: -4px;
    left: 0;
    animation: moveDot 2s infinite ease-in-out;
}

@keyframes moveDot {
    0%   { left: 0; }
    50%  { left: calc(100% - 14px); }
    100% { left: 0; }
}

.message {
    width: 100%;
    text-align: center;
    margin:0;
}
</style>
