import { ref, watch } from 'vue'
import { type Settings } from '@/interfaces/Settings.ts'
import { CompletionStatus } from '@/interfaces/Settings.ts'

const SETTINGS_KEY = 'lab2gpx-settings';

export function hasStoredSettings(): boolean
{
    return localStorage.getItem(SETTINGS_KEY) !== null;
}

export function useSettings() {
    const settings = ref<Settings>({
        version: 2,
        locale: 'en',
        radius: 15,
        coordinates: { lat: 52.520008, lon: 13.404954 },
        limit: 300,

        cacheType: 'Lab Cache',
        linear: 'default',

        prefix: 'LC',
        stageSeparator: true,
        customCodeTemplate: null,

        userGuid: null,
        completionStatuses: [
            CompletionStatus.COMPLETED,
            CompletionStatus.PARTIAL_COMPLETED,
            CompletionStatus.NOT_STARTED,
        ],

        includeQuestion: true,
        includeWaypointDescription: true,
        includeCacheDescription: true,

        excludeOwner: null,
        excludeNames: null,
        excludeUuids: null,

        quirksL4Ctype: false,
        quirksBomForCsv: false,

        outputFormat: 'zippedgpx',
    })

    try {
        const storedSettingsJson = localStorage.getItem(SETTINGS_KEY)
        if (storedSettingsJson) {
            const storedSettings = JSON.parse(storedSettingsJson) as Settings
            settings.value = { ...settings.value, ...storedSettings }
        }
    } catch {}

    watch(
        () => settings.value,
        () => {
            try {
                localStorage.setItem(SETTINGS_KEY, JSON.stringify(settings.value))
            } catch (error) {
                console.error(`Error persisting settings in localStorage: ${error}`)
            }
        },
        { deep: true },
    )

    return settings
}
