import { convert } from 'geo-coordinates-parser'
import type { Coordinates } from '@/interfaces/Coordinates.ts'
import { useSettings } from '@/composables/useSettings.ts';

const NOMINATIM = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=5&q='

interface NominatimSearchResult {
    display_name: string
    lat: string
    lon: string
}

const settings = useSettings();

export function useGeocoding() {
    function normalizeCoords(input: string): Coordinates | null {
        try {
            const result = convert(input)
            return {
                lat: result.decimalLatitude,
                lon: result.decimalLongitude,
            }
        } catch {
            return null
        }
    }

    async function geoCode(q: string): Promise<{ display: string; coords: Coordinates }[]> {
        const url = `${NOMINATIM}${encodeURIComponent(q)}`
        const res = await fetch(url, {
            headers: {
                'Accept-Language': settings.value.locale,
                'User-Agent': 'lab2gpx/2.0',
            },
        })
        const data = (await res.json()) as NominatimSearchResult[]
        return data.map((x) => ({
            display: x.display_name,
            coords: { lat: parseFloat(x.lat), lon: parseFloat(x.lon) },
        }))
    }

    return { geoCode, normalizeCoords }
}
