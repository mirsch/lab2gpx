<script setup lang="ts">
import { ref, computed, defineEmits, defineProps } from 'vue'
import { useI18n } from 'vue-i18n'
import { LMap, LTileLayer, LMarker, LCircle, LControlZoom } from '@vue-leaflet/vue-leaflet'
import type { LatLngExpression, LeafletMouseEvent, PointExpression } from 'leaflet'
import 'leaflet/dist/leaflet.css'
import type { Coordinates } from '@/interfaces/Coordinates.ts'

type Props = {
    center: Coordinates
    radius: number
}
const props = defineProps<Props>()
const emit = defineEmits<{
    (e: 'map-click', v: Coordinates): void
}>()

const { locale } = useI18n()

const zoom = ref(10)
// tile.openstreetmap.org renders labels in the local language only; the German
// OSM style (openstreetmap.de) shows German names with the local name in brackets
const tileUrl = computed(() =>
    locale.value === 'de'
        ? 'https://tile.openstreetmap.de/{z}/{x}/{y}.png'
        : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
)
const tileAttribution = '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>'

function onClick(e: LeafletMouseEvent) {
    const normalizedCoords = e.latlng.wrap();
    emit('map-click', { lat: normalizedCoords.lat, lon: normalizedCoords.lng })
}
</script>

<template>
    <LMap
        style="height: 100%; width: 100%"
        :use-global-leaflet="false"
        :zoom="zoom"
        :center="[props.center.lat, props.center.lon] as PointExpression"
        :options="{ zoomControl: false }"
        @click="onClick"
    >
        <LTileLayer :key="tileUrl" :url="tileUrl" :attribution="tileAttribution" />

        <LControlZoom position="topright" />

        <LMarker :lat-lng="[props.center.lat, props.center.lon] as LatLngExpression" />
        <LCircle
            :lat-lng="[props.center.lat, props.center.lon] as LatLngExpression"
            :radius="Math.ceil(props.radius * 1000)"
            :color="'#2B82CB'"
            :weight="2"
            :fillColor="'#2B82CB'"
            :fillOpacity="0.15"
        />
    </LMap>
</template>
