<script setup lang="ts">
import { useI18n } from 'vue-i18n';
import ModalWrap from '@/components/ModalWrap.vue';
const { locale, t } = useI18n();

const open = defineModel<boolean>('open', {
    required: true
});

</script>

<template>
    <ModalWrap v-model:open="open">
        <template #header>Lab2Gpx</template>

        <div v-if="locale == 'de'">
            <p>Dieses Tool generiert eine GPX Datei mit Geocaching Adventure Labs für diverse Geocaching Apps und Garmin GPS Geräte.</p>
            <p>Im <a href="https://github.com/mirsch/lab2gpx/wiki" target="_blank">Wiki</a> findest du eine kurze Anleitung</p>
            <p>Bei Problemen oder Funktionswünschen kannst Du gern <a href="https://github.com/mirsch/lab2gpx/issues" target="_blank">ein Ticket schreiben</a>.</p>
            <p>Die Webseite speichert Einstellungen im Local Storage deines Browsers.</p>
        </div>
        <div v-else>
            <p>This tool generates a GPX file containing Geocaching Adventure Labs for use in several Geocaching Apps and Garmin GPS devices.</p>
            <p>In the <a href="https://github.com/mirsch/lab2gpx/wiki" target="_blank">wiki</a> you will find a short guide</p>
            <p>If you find bugs or have feature requests, feel free to <a href="https://github.com/mirsch/lab2gpx/issues" target="_blank">open an issue</a>.</p>
            <p>The Website stores settings in the local storage of your browser.</p>
        </div>

        <template #footer>
            <a class="button primary" @click="open = false">{{ t('ok') }}</a>
        </template>
    </ModalWrap>
</template>

<style scoped>
</style>
