<script setup lang="ts">
import { ref, watch } from 'vue';
import { useGeocoding } from '@/composables/useGeocoding';
import type { Coordinates } from '@/interfaces/Coordinates.ts';
import { useI18n } from 'vue-i18n';
import AwesomeIcon from '@/components/AwesomeIcon.vue';
const { t } = useI18n();

const coords = defineModel<Coordinates>('coords', {
    required: true,
});
const radius = defineModel<number>('radius', {
    required: true,
});

const searchInput = ref<string>(coordsToText(coords.value));
watch(
    () => coords.value,
    (v) => {
        searchInput.value = coordsToText(v);
    },
);

function coordsToText(coords: Coordinates): string {
    return `${coords.lat.toFixed(5)}, ${coords.lon.toFixed(5)}`;
}

function onLocate() {
    if (!navigator.geolocation) {
        alert(t('search.locate.not_available'));
        return;
    }
    navigator.geolocation.getCurrentPosition(
        (pos) => {
            coords.value = { lat: pos.coords.latitude, lon: pos.coords.longitude };
        },
        (err) => {
            alert(t('search.locate.no_position'));
            console.error(err);
        },
        { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 },
    );
}

const { geoCode, normalizeCoords } = useGeocoding();

async function onSearchChange() {
    const newCoords = normalizeCoords(searchInput.value);
    if (newCoords) {
        coords.value = newCoords;
        return;
    }

    if (searchInput.value.trim().length < 3) {
        return;
    }

    // @TODO sometimes slow, need loading state
    const results = await geoCode(searchInput.value.trim());
    if (results.length > 0) {
        // @TODO show list of results and let user choose
        const firstResult = results[0];
        coords.value = firstResult.coords;
    }
}
</script>

<template>
    <div class="grouped">
        <div class="search-wrap">
            <label for="search">{{ t('search.coordinates') }}</label>
            <div class="grouped">
                <input
                    id="search"
                    v-model="searchInput"
                    @change="onSearchChange"
                    class="search-input"
                    type="text"
                    placeholder="{{ t('search.coordinates_placeholder') }}"
                />
                <button
                    id="locate"
                    class="button outline icon-only"
                    @click="onLocate"
                    title="{{ t('search.locate.current_position') }}"
                >
                    <AwesomeIcon icon="location-crosshairs"/>
                </button>
            </div>
        </div>
        <div>
            <label for="radius">{{ t('search.radius') }}</label>
            <input
                id="radius"
                v-model="radius"
                type="number"
                class="radius-input"
                min="1"
                step="1"
            />
        </div>
    </div>
</template>

<style scoped>
.search-wrap {
    width: 100%;
}
.radius-input {
    max-width: 90px;
}
label {
    white-space: nowrap;
}
</style>
