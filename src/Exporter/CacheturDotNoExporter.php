<?php

namespace App\Exporter;

use App\DecimalMinutes;
use Location\Coordinate;

class CacheturDotNoExporter extends AbstractExporter
{
    public function export(array $fetchedLabs, array $values, array $ownersToSkip): string
    {
        $cacheturDotNo = '';
        foreach ($fetchedLabs as $cache) {
            $cache = $this->getCache($cache['Id']);
            if (! $this->includeCache($cache, $values, $ownersToSkip)) {
                continue;
            }

            $stage = 1;
            foreach ($cache['GeocacheSummaries'] as $wpt) {
                if (in_array($wpt['Id'], $values['uuidsToExclude'])) {
                    $stage++;
                    continue;
                }

                $found = $this->isFound($wpt);
                if ($found && ! $values['includeFinds']) {
                    $stage++;
                    continue;
                }

                $lat = $wpt['Location']['Latitude'];
                $lon = $wpt['Location']['Longitude'];

                $waypointTitle = $this->getWaypointTitle($cache, $values, $wpt, $stage);
                $code = $this->getCode($cache, $values, $stage);

                $coordinate = new Coordinate($lat, $lon);
                $formatter = new DecimalMinutes();
                $cacheturDotNo .= $code . ';lab;' . $coordinate->format($formatter) . ';' . $waypointTitle . "\n";

                $stage++;

                if ($cache['IsLinear'] && $values['linear'] === 'first') {
                    break;
                }
            }
        }

        return $cacheturDotNo;
    }
}
