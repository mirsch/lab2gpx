<script setup lang="ts">
import { ref, toRef } from 'vue'
import SearchBar from './components/SearchBar.vue'
import SettingsModal from './components/SettingsModal.vue'
import { type Coordinates } from '@/interfaces/Coordinates.ts'
import MapView from '@/components/MapView.vue'
import { useSettings, hasStoredSettings } from '@/composables/useSettings.ts'
import DownloadModal from '@/components/DownloadModal.vue';
import { useDownload } from '@/composables/useDownload.ts';
import HelpModal from '@/components/HelpModal.vue';
import AwesomeIcon from '@/components/AwesomeIcon.vue';

const settings = useSettings();
const showSettings = ref(false);
const {showDownloader, triggerDownload } = useDownload();
const showHelpModal = ref(true);
if (hasStoredSettings()) {
    showHelpModal.value = false;
}

const coords = toRef(settings.value, 'coordinates');
const radius = toRef(settings.value, 'radius');

function updateFromMapClick(mapCoords: Coordinates) {
    settings.value.coordinates = mapCoords;
}

function onDownload() {
    triggerDownload(settings.value);
}

function onHelp() {
    showHelpModal.value = true;
}
</script>

<template>
    <div class="app-shell">
        <div class="search-container">
            <SearchBar v-model:coords="coords" v-model:radius="radius" />
        </div>

        <div class="left-toolbar">
            <button class="button outline icon-only" @click="showSettings = true">
                <AwesomeIcon icon="gear"/>
            </button>

            <button class="button outline icon-only" @click="onDownload">
                <AwesomeIcon icon="download"/>
            </button>

            <button class="button outline icon-only" @click="onHelp">
                <AwesomeIcon icon="question"/>
            </button>
        </div>

        <div class="map-wrap">
            <MapView :center="coords" :radius="radius" @map-click="updateFromMapClick" />
        </div>

        <SettingsModal
            v-model:open="showSettings"
            v-model:settings="settings"
        />

        <DownloadModal v-model:open="showDownloader"/>
        <HelpModal v-model:open="showHelpModal"/>
    </div>
</template>

<style scoped>
.app-shell {
    --position-offset: 12px;
    --search-container-height: 90px;

    height: 100%;
    width: 100%;
    position: relative;
}

.search-container {
    position: absolute;
    left: var(--position-offset);
    top: var(--position-offset);
    right: auto;
    z-index: 1100;
    max-width: 600px;
    width: 600px;
    border-radius: 4px;
    background: #fff;
    padding: 8px 16px 16px 16px;
    border: 1px solid #ccc;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
.map-wrap {
    width: 100%;
    height:100%;
}
@media (max-width: 720px) {
    .search-container {
        position: relative;
        left: 0;
        right: 0;
        top: 0;
        max-width: none;
        width: 100%;
        border: 0;
        box-shadow: none;
    }
    .app-shell {
        display: flex;
        flex-direction: column;
        gap:0;
    }
}

.left-toolbar {
    position: absolute;
    left: var(--position-offset);
    top: calc(var(--search-container-height) + 2 * var(--position-offset));
    display: flex;
    flex-direction: column;
    gap: var(--position-offset);
    z-index: 1000;
}

.left-toolbar button {
    width: 48px;
    height: 48px;
    padding: 0;
    border-radius: 4px;
    background: #fff;
    border: 1px solid #ccc;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    font-size: 24px;
    color: #333;
    cursor: pointer;
}

.left-toolbar button + button {
    margin-left: 0;
}
</style>
