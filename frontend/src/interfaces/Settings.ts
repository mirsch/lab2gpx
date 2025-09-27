import type { Coordinates } from '@/interfaces/Coordinates.ts'

enum CompletionStatus {
    COMPLETED = '0',
    PARTIAL_COMPLETED = '1',
    NOT_STARTED = '2',
}

interface Settings {
    version: number;
    locale: string;

    radius: number;
    coordinates: Coordinates;
    limit: number;

    cacheType: 'Lab Cache' | 'Virtual Cache' | 'Mega-Event Cache';
    linear: 'default' | 'first' | 'mark' | 'corrected' | 'ignore';

    // code generation
    prefix: string;
    stageSeparator: boolean;
    customCodeTemplate: string|null;

    // filters/personalized search
    userGuid: string | null;
    completionStatuses: CompletionStatus[];

    // description generation
    includeQuestion: boolean;
    includeWaypointDescription: boolean;
    includeCacheDescription: boolean;

    // exclusions
    excludeOwner: string|null;
    excludeNames: string|null;
    excludeUuids: string|null;

    quirksL4Ctype: boolean;
    quirksBomForCsv: boolean;

    outputFormat: 'zippedgpx' | 'gpx' | 'zippedgpxwpt' | 'gpxwpt' | 'cacheturdotno';
}

export { type Settings, CompletionStatus }
