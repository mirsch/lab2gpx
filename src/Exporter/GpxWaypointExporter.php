<?php

namespace App\Exporter;

class GpxWaypointExporter extends GpxExporter
{
    protected function getCacheDescription(array $cache, array $values): string
    {
        $description = '<h3>' . $cache['Title'] . '</h3>';
        if ($cache['IsLinear']) {
            $description .= '<p><span style="background:#990000;color:#fff;border-radius:5px;padding:3px 5px;">' . $this->locale['TAG_LINEAR'] . '</span></p>';
        }
        $description .= '<p><a href="' . $cache['DeepLink'] . '">' . $cache['DeepLink'] . '</a></p>';

        if ($values['includeCacheDescription']) {
            $description .= '<hr />';
            $description .= '<h5>' . $this->locale['HEADER_LAB_DESCRIPTION'] . '</h5>';
            $description .= '<p><img src="' . $cache['KeyImageUrl'] . '" /></p>';
            $description .= '<p>' . $cache['Description'] . '</p>';
        }

        $stage = 1;
        foreach ($cache['GeocacheSummaries'] as $wpt) {
            $description .= '<hr />';
            $description .= '<h4>' . $this->getWaypointTitle($cache, $values, $wpt, $stage) . '</h4>';

            if ($values['includeQuestion']) {
                $description .= '<p>' . $this->locale['HEADER_QUESTION'] . ':<br />' . $wpt['Question'] . '</p>';
            }

            if ($values['includeWaypointDescription']) {
                $description .= '<hr />';
                $description .= '<h5>' . $this->locale['HEADER_WAYPOINT_DESCRIPTION'] . '</h5>';
                $description .= '<p><img src="' . $wpt['KeyImageUrl'] . '" /></p>';
                $description .= '<p>' . $wpt['Description'] . '</p>';
            }

            if ($values['includeAwardMessage']) {
                if ($wpt['AwardImageUrl'] || $wpt['CompletionAwardMessage']) {
                    $description .= '<hr />';
                    $description .= '<h5>' . $this->locale['HEADER_AWARD'] . '</h5>';
                }
                if ($wpt['AwardImageUrl']) {
                    $description .= '<p><img src="' . $wpt['AwardImageUrl'] . '" /></p>';
                }
                if ($wpt['CompletionAwardMessage']) {
                    $description .= '<p>' . $this->locale['HEADER_AWARD_MESSAGE'] . ':<br />' . $wpt['CompletionAwardMessage'] . '</p>';
                }
            }
            $stage++;
        }

        return $this->cleanupWaypointDescription($description);
    }

    protected function getWaypointTitle(array $cache, array $values, array $wpt, int $stage): string
    {
        $waypointTitle = 'S' . $this->getStageForDisplay($stage, $cache) . ' ' . $wpt['Title'];
        if ($cache['IsLinear'] && $values['linear'] === 'mark') {
            $waypointTitle = '[L] ' . $waypointTitle;
        }

        return $waypointTitle;
    }

    public function export(array $fetchedLabs, array $values, array $ownersToSkip): string
    {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
                <gpx xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1.0" creator="Groundspeak Pocket Query" xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd http://www.groundspeak.com/cache/1/0/1 http://www.groundspeak.com/cache/1/0/1/cache.xsd" xmlns="http://www.topografix.com/GPX/1/0">
                    <name>Adventure Labs</name>
                    <desc>(HasChildren)</desc>
                ';
        $id = -1;
        foreach ($fetchedLabs as $fetchedCache) {
            $cache = $this->getFileCache($fetchedCache);
            if (! $this->includeCache($cache, $values, $ownersToSkip)) {
                continue;
            }

            $lat = $cache['Location']['Latitude'];
            $lon = $cache['Location']['Longitude'];

            $code = $this->getCode($cache, $values, 0);

            $xml .= '<wpt lat="' . $lat . '" lon="' . $lon . '">
                    <time>' . $cache['PublishedUtc'] . '</time>
                    <name>' . $code . '</name>
                    <desc>' . $this->gpxEncode($cache['Title']) . '</desc>
                    <url>' . $cache['DeepLink'] . '</url>
                    <urlname>' . $this->gpxEncode($cache['Title']) . '</urlname>
                    <sym>Geocache' . ($fetchedCache['IsComplete'] ? ' Found' : '') . '</sym>
                    <type>Geocache|' . $values['cacheType'] . '</type>';
            $xml .= '<gsak:wptExtension xmlns:gsak="http://www.gsak.net/xmlv1/5">
                        <gsak:Code>' . $code . '</gsak:Code>
                        <gsak:IsPremium>false</gsak:IsPremium>
                        <gsak:FavPoints>0</gsak:FavPoints>
                        <gsak:UserFlag>false</gsak:UserFlag>
                        <gsak:Guid>' . $cache['Id'] . '</gsak:Guid>
                        <gsak:DNF>false</gsak:DNF>
                        <gsak:FTF>false</gsak:FTF>';
            if ($values['linear'] === 'corrected' && $cache['IsLinear']) {
                $xml .= '
                        <gsak:LatBeforeCorrect>' . $lat . '</gsak:LatBeforeCorrect>
                        <gsak:LonBeforeCorrect>' . $lon . '</gsak:LonBeforeCorrect>';
            }
            $xml .= '
                    </gsak:wptExtension>';
            $xml .= '<groundspeak:cache id="' . $id . '" available="True" archived="False" xmlns:groundspeak="http://www.groundspeak.com/cache/1/0/1">
                        <groundspeak:name>' . $this->gpxEncode($cache['Title']) . '</groundspeak:name>
                        <groundspeak:placed_by>' . $this->gpxEncode($cache['OwnerUsername']) . '</groundspeak:placed_by>
                        <groundspeak:owner>' . $this->gpxEncode($cache['OwnerUsername']) . '</groundspeak:owner>
                        <groundspeak:type>' . $values['cacheType'] . '</groundspeak:type>
                        <groundspeak:container>Virtual</groundspeak:container>
                        <groundspeak:attributes />
                        <groundspeak:difficulty>1</groundspeak:difficulty>
                        <groundspeak:terrain>1</groundspeak:terrain>
                        <groundspeak:country />
                        <groundspeak:state />
                        <groundspeak:short_description html="True" />
                        <groundspeak:long_description html="True">' . $this->gpxEncode($this->getCacheDescription($cache, $values)) . '</groundspeak:long_description>
                        <groundspeak:encoded_hints />
                        <groundspeak:logs />
                        <groundspeak:travelbugs />
                    </groundspeak:cache>
                </wpt>';

            $stage = 1;
            foreach ($cache['GeocacheSummaries'] as $wpt) {
                if (in_array($wpt['Id'], $values['uuidsToExclude'])) {
                    $stage++;
                    continue;
                }

                if ($wpt['IsComplete'] && !in_array('0', $values['completionStatuses'])) {
                    continue;
                }

                $lat = $wpt['Location']['Latitude'];
                $lon = $wpt['Location']['Longitude'];

                $wptCode = $this->getCode($cache, $values, $stage, true);
                $waypointTitle = $this->getStageForDisplay($stage, $cache) . ' ' . $wpt['Title'];

                $xml .= '<wpt lat="' . $lat . '" lon="' . $lon . '">
                            <time>' . $cache['PublishedUtc'] . '</time>
                            <name>' . $wptCode . '</name>
                            <cmt>' . $this->gpxEncode( $wpt['Question']) . '</cmt>
                            <url>' . $cache['DeepLink'] . '</url>
                            <desc>' . $this->gpxEncode($waypointTitle) . '</desc>
                            <sym>Virtual Stage</sym>
                            <type>Waypoint|Virtual Stage</type>
                            <gsak:wptExtension xmlns:gsak="http://www.gsak.net/xmlv1/4">
                                <gsak:Parent>' . $code . '</gsak:Parent>
                            </gsak:wptExtension>
                          </wpt>';

                $stage++;
                $id--;
            }
        }

        $xml .= '</gpx>';

        return $xml;
    }
}
