<?php

namespace App\Exporter;

abstract class AbstractExporter
{
    protected array $usedCodes = [];

    protected string $dataDir;

    protected array $locale;

    public function __construct(string $dataDir, array $locale)
    {
        $this->dataDir = $dataDir;
        $this->locale = $locale;
    }

    abstract public function export(array $fetchedLabs, array $values, array $ownersToSkip, array $finds): string;

    protected function getCache(string $id): array
    {
        $file = $this->dataDir . '/' . $id . '.json';

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

    protected function isFound(array $finds, array $cache, array $wpt): bool
    {
	if ($wpt['IsComplete'])
	    return true;

        if (isset($finds[$cache['Id']])) {
            if (in_array(trim($wpt['Title']), $finds[$cache['Id']])) {
                return true;
            }
        }

        return false;
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
        $code = '';
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
        $codeCnt = 0;
        // the firebase link contains upper and lower case letters so there may be collisions if we convert it to upper case
        $fixedPart = str_replace('https://adventurelab.page.link/', '', $cache['FirebaseDynamicLink']);
        if (! $values['codeIsCaseSensitive']) {
            $fixedPart = strtoupper($fixedPart);
        }
        while (! $code || (in_array($code, $this->usedCodes) && $codeCnt < 16)) {
            $code = strtoupper($prefix) . $fixedPart . ($codeCnt ? $codeCnt : '');
            if ($stage) {
                $code .= $stage;
            }
            $codeCnt++;
        }
        $this->usedCodes[] = $code;

        return $code;
    }
}
