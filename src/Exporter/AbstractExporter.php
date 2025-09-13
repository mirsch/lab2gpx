<?php

namespace App\Exporter;

use App\LabCode;

abstract class AbstractExporter
{
    protected array $usedCodes = [];

    public function __construct(
        private readonly string $cacheDir,
        private readonly string $databaseDir,
        protected readonly array $locale,
    ) {
    }

    abstract public function export(array $fetchedLabs, array $values, array $ownersToSkip): string;

    protected function getFileCache(array $fetchedCache): array
    {
        // partial labs not from cache
        if (isset($fetchedCache['CompletedGeocachesCount']) && isset($fetchedCache['TotalGeocachesCount'])) {
            if ($fetchedCache['CompletedGeocachesCount'] < $fetchedCache['TotalGeocachesCount']) {
                return $fetchedCache;
            }
        }


        $file = $this->cacheDir . '/' . $fetchedCache['Id'] . '.json';
        if (! file_exists($file)) {
            return $fetchedCache;
        }

        return json_decode(file_get_contents($file), true);
    }

    protected function includeCache(array $cache, array $values, array $ownersToSkip): bool
    {
        if (! $cache) {
            return false;
        }

        if ($cache['IsLinear'] && $values['linear'] === 'ignore') {
            return false;
        }

        if (in_array($cache['OwnerUsername'], $ownersToSkip)) {
            return false;
        }

        if (in_array($cache['Id'], $values['uuidsToExclude'])) {
            return false;
        }

        return true;
    }

    protected function getStageForDisplay(int $stage, array $cache): string
    {
        return str_pad((string) $stage, count($cache['GeocacheSummaries']) >= 10 ? 2 : 1, '0', STR_PAD_LEFT);
    }

    protected function getWaypointTitle(array $cache, array $values, array $wpt, int $stage): string
    {
        $waypointTitle = $cache['Title'] . ' : S' . $this->getStageForDisplay($stage, $cache) . ' ' . $wpt['Title'];
        if ($cache['IsLinear'] && $values['linear'] === 'mark') {
            $waypointTitle = '[L] ' . $waypointTitle;
        }

        return $waypointTitle;
    }

    protected function getCode(array $cache, array $values, int $stage, bool $useStageAsPrefix = false): string
    {
        $stage = $stage ? str_pad((string) $stage, 2, '0', STR_PAD_LEFT) : 0;
        $prefix = $values['prefix'];
        if ($useStageAsPrefix) {
            $stage = strtoupper(dechex((int) $stage));
            $prefix = 'S' . $stage;
            if ($cache['IsLinear']) {
                $prefix = 'L' . $stage;
            }
            $stage = 0;
        }

        $LabCode = new LabCode($this->databaseDir);
        $fixedPart = $LabCode->uuid2LabCode($cache['Id']);
        $sep = '-';
        if (!$values['stageSeparator']) {
            $sep = '';
        }

        return strtoupper($prefix) . $fixedPart . ($stage ? ($sep . $stage) : '');
    }
}
