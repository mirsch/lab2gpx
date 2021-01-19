<?php
/**
 * Lab2Gpx
 *
 * Copyright (C) 2021  mirsch <https://gcutils.de/lab2gpx/>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

use Location\Coordinate;
use Location\Factory\CoordinateFactory;

// ini_set('display_errors', 'on');
// error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

$dataDir = __DIR__ . '/data';
$logFile = __DIR__ . '/lab2gpx.log';
$tmpDir = sys_get_temp_dir();

function fetch(string $url): string
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Adventures/1.2.27 (2192) (ios/14.1)');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Accept' => 'application/json']);

    $data = curl_exec($ch);
    if ($data === false) {
        throw new UnexpectedValueException(curl_error($ch));
    }
    curl_close($ch);

    return $data;
}

function gpxEncode(string $s): string
{
    return htmlentities($s, ENT_XML1);
}

function fetchLabs(Coordinate $coordinates, array $values, array &$fetchedLabs, $skip = 0)
{
    global $LANG, $dataDir;

    $max = $values['take'];
    $take = $max;
    if ($take > 500) {
        $take = 500;
    }
    $url = 'https://labs-api.geocaching.com/Api/Adventures/SearchV3?origin.latitude=' . $coordinates->getLat() . '&origin.longitude=' . $coordinates->getLng() . '&radiusMeters=' . ($values['radius'] * 1000) . '&skip=' . $skip . '&take=' . $take;
    $labCachesJson = fetch($url);
    $labCaches = json_decode($labCachesJson, true);
    if (! $labCaches || ! is_array($labCaches) || ! $labCaches['Items'] > 0) {
        echo $LANG['NO_CACHES_FOUND'];
        exit;
    }

    foreach ($labCaches['Items'] as $cache) {
        if (count($fetchedLabs) >= $max) {
            return;
        }
        $file = $dataDir . '/' . $cache['Id'] . '.json';
        if (file_exists($file)) { // @todo we should refetch from time to time because there may be changes
            $fetchedLabs[] = $cache;
            continue;
        }
        @set_time_limit(10);
        $url = 'https://labs-api.geocaching.com/Api/Adventures/' . $cache['Id'];
        $details = fetch($url);
        file_put_contents($file, json_encode(json_decode($details, true), JSON_PRETTY_PRINT));
        $fetchedLabs[] = $cache;
    }

    $total = (int) $labCaches['TotalCount'];
    if ($max <= $total && ($skip + 1) * $take < $max && count($fetchedLabs) < $max) {
        fetchLabs($coordinates, $values, $fetchedLabs, $skip + $take);
    }
}

if (! is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}

header('Vary: Accept-Language');
$LANG = [];
require __DIR__ . '/lang/en.php';
$knownLangs = ['en', 'de'];
$lang = 'en';
if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $userPrefLangs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    foreach ($userPrefLangs as $idx => $browserLang) {
        $browserLang = substr($lang, 0, 2);
        if (in_array($browserLang, $knownLangs)) {
            require __DIR__ . '/lang/' . $browserLang . '.php';
            $lang = $browserLang;
            break;
        }
    }
}
header('Content-Language: ' . $lang);

$errors = [];
$cacheTypes = [
    'Lab Cache',
    'Virtual Cache',
];
$values = [
    'coordinates' => 'N50° 50.156 E012° 55.398',
    'radius' => 15,
    'take' => 300,
    'cacheType' => $cacheTypes[0],

    'includeQuestion' => true,
    'includeWaypointDescription' => true,
    'includeCacheDescription' => true,
    'includeAwardMessage' => false,

    'excludeOwner' => '',
    'findsHtml' => '',
    'includeFinds' => false,
];
$coordinates = CoordinateFactory::fromString($values['coordinates']);

$cookieName = 'lab2gpx_settings_v03';
if (isset($_COOKIE[$cookieName])) {
    $cookieValues = json_decode($_COOKIE[$cookieName], true);
    if ($cookieValues) {
        $values = array_merge($values, $cookieValues);
        try {
            $cookieCoordinates = CoordinateFactory::fromString($values['coordinates']);
            $coordinates = $cookieCoordinates;
        } catch (Throwable $throwable) {
            $values['coordinates'] = $coordinates->getLat() . ', ' . $coordinates->getLng();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = array_merge($values, $_POST);

    try {
        $coordinates = CoordinateFactory::fromString($values['coordinates']);
    } catch (Throwable $throwable) {
        $errors['coordinates'] = $LANG['INVALID_COORDINATES'];
    }

    $values['radius'] = (int) $values['radius'];
    if ($values['radius'] > 20000) {
        $errors['radius'] = $LANG['INVALID_RADIUS_HIGH'];
    }
    if ($values['radius'] < 1) {
        $errors['radius'] = $LANG['INVALID_RADIUS_LOW'];
    }

    $values['take'] = (int) $values['take'];
    if ($values['take'] > 1000) {
        $values['take'] = 1000;
    }
    if ($values['take'] < 1) {
        $values['take'] = 1;
    }

    if (! in_array($values['cacheType'], $cacheTypes)) {
        $values['cacheType'] = $cacheTypes[0];
    }

    if (! $errors) {
        $cookieValues = $values;
        unset($cookieValues['findsHtml']);
        setcookie($cookieName, json_encode($cookieValues), time() + 999999);

        // file_put_contents($logFile, (new DateTimeImmutable())->format('Y-m-d H:i:s') . "\t" . $_SERVER['REMOTE_ADDR'] . "\t" . json_encode($cookieValues) . "\n", FILE_APPEND);

        // fetch data
        $fetchedLabs = [];
        fetchLabs($coordinates, $values, $fetchedLabs);

        // generate GPX
        $ownersToSkip = [];
        $ownerText = str_replace("\r\n", "\n", $values['excludeOwner']);
        $ownerText = str_replace("\r", "\n", $ownerText);
        $ownersToSkip = explode("\n", $ownerText);
        $ownersToSkip = array_map('trim', $ownersToSkip);
        $ownersToSkip = array_unique($ownersToSkip);

        $finds = [];
        if ($values['findsHtml'] !== '') {
            preg_match_all('/<li data-adv-id="([0-9a-z-]*)" class="deletable"(.*)<span class="cache-title">(.*)<\/span>/msU', $values['findsHtml'], $matches);
            $finds = array_unique($matches[1]);
            foreach ($matches[1] as $idx => $cacheId) {
                $foundTitle = html_entity_decode(trim($matches[3][$idx]));
                // @see user notes at https://www.php.net/manual/de/function.html-entity-decode.php
                $foundTitle = preg_replace_callback("/(&#[0-9]+;)/", function ($m) {
                    return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES");
                }, $foundTitle);
                $finds[$cacheId][] = $foundTitle;
            }
        }

        $xml = '<?xml version="1.0" encoding="utf-8"?>
                <gpx xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1.0" creator="Groundspeak Pocket Query" xsi:schemaLocation="http://www.topografix.com/GPX/1/0 http://www.topografix.com/GPX/1/0/gpx.xsd http://www.groundspeak.com/cache/1/0/1 http://www.groundspeak.com/cache/1/0/1/cache.xsd" xmlns="http://www.topografix.com/GPX/1/0">
                    <name>Adventure Labs</name>
                ';
        $id = -1;
        $usedCodes = [];
        foreach ($fetchedLabs as $cache) {
            $file = $dataDir . '/' . $cache['Id'] . '.json';
            $cache = json_decode(file_get_contents($file), true);
            if (! $cache) {
                continue;
            }

            if (in_array($cache['OwnerUsername'], $ownersToSkip)) {
                continue;
            }

            $stage = 1;
            foreach ($cache['GeocacheSummaries'] as $wpt) {

                $found = false;
                if (isset($finds[$cache['Id']])) {
                    if (in_array(trim($wpt['Title']), $finds[$cache['Id']])) {
                        $found = true;
                        if (! $values['includeFinds']) {
                            $stage++;
                            continue;
                        }
                    }
                }

                $lat = $wpt['Location']['Latitude'];
                $lon = $wpt['Location']['Longitude'];

                $description = '<h3>' . $cache['Title'] . '</h3>';
                $description .= '<h4>' . $wpt['Title'] . '</h4>';
                if ($cache['IsLinear']) {
                    $description .= '<p><span style="background:#990000;color:#fff;border-radius:5px;padding:3px 5px;">' . $LANG['TAG_LINEAR'] . '</span></p>';
                }
                $description .= '<p><a href="' . $cache['DeepLink'] . '">' . $cache['DeepLink'] . '</a></p>';

                if ($values['includeQuestion']) {
                    $description .= '<p>' . $LANG['HEADER_QUESTION'] . ':<br />' . $wpt['Question'] . '</p>';
                }

                if ($values['includeWaypointDescription']) {
                    $description .= '<hr />';
                    $description .= '<h5>' . $LANG['HEADER_WAYPOINT_DESCRIPTION'] . '</h5>';
                    $description .= '<p><img src="' . $wpt['KeyImageUrl'] . '" /></p>';
                    $description .= '<p>' . $wpt['Description'] . '</p>';
                }

                if ($values['includeCacheDescription']) {
                    $description .= '<hr />';
                    $description .= '<h5>' . $LANG['HEADER_LAB_DESCRIPTION'] . '</h5>';
                    $description .= '<p><img src="' . $cache['KeyImageUrl'] . '" /></p>';
                    $description .= '<p>' . $cache['Description'] . '</p>';
                }

                if ($values['includeAwardMessage']) {
                    if ($wpt['AwardImageUrl'] || $wpt['CompletionAwardMessage']) {
                        $description .= '<hr />';
                        $description .= '<h5>' . $LANG['HEADER_AWARD'] . '</h5>';
                    }
                    if ($wpt['AwardImageUrl']) {
                        $description .= '<p><img src="' . $wpt['AwardImageUrl'] . '" /></p>';
                    }
                    if ($wpt['CompletionAwardMessage']) {
                        $description .= '<p>' . $LANG['HEADER_AWARD_MESSAGE'] . ':<br />' . $wpt['CompletionAwardMessage'] . '</p>';
                    }
                }

                $displayStage = str_pad((string) $stage, count($cache['GeocacheSummaries']) >= 10 ? 2 : 1, '0', STR_PAD_LEFT);

                $waypointTitle = gpxEncode($cache['Title']) . ' : S' . $displayStage . ' ' . gpxEncode($wpt['Title']);
                $code = null;
                $codeCnt = 0;
                // the firebase link contains upper and lower case letters so there may be collisions if we convert it to upper case
                while (! $code || (in_array($code, $usedCodes) && $codeCnt < 16)) {
                    $code = 'LC' . strtoupper(str_replace('https://adventurelab.page.link/', '', $cache['FirebaseDynamicLink'])) . ($codeCnt ? $codeCnt : '') . str_pad((string) $stage, 2, '0', STR_PAD_LEFT);
                    $codeCnt++;
                }
                $usedCodes[] = $code;

                $xml .= '<wpt lat="' . $lat . '" lon="' . $lon . '">
                    <time>' . $cache['PublishedUtc'] . '</time>
                    <name>' . $code . '</name>
                    <desc>' . gpxEncode($wpt['Title']) . '</desc>
                    <url>' . $cache['DeepLink'] . '</url>
                    <urlname>S' . $displayStage . ' ' . gpxEncode($cache['Title']) . '</urlname>
                    <sym>Geocache' . ($found ? ' Found' : '') . '</sym>
                    <type>Geocache|' . $values['cacheType'] . '</type>
                    <groundspeak:cache id="' . $id . '" available="True" archived="False" xmlns:groundspeak="http://www.groundspeak.com/cache/1/0/1">
                        <groundspeak:name>' . $waypointTitle . '</groundspeak:name>
                        <groundspeak:placed_by>' . gpxEncode($cache['OwnerUsername']) . '</groundspeak:placed_by>
                        <groundspeak:owner>' . gpxEncode($cache['OwnerUsername']) . '</groundspeak:owner>
                        <groundspeak:type>' . $values['cacheType'] . '</groundspeak:type>
                        <groundspeak:container>Virtual</groundspeak:container>
                        <groundspeak:attributes />
                        <groundspeak:difficulty>1</groundspeak:difficulty>
                        <groundspeak:terrain>1</groundspeak:terrain>
                        <groundspeak:country />
                        <groundspeak:state />
                        <groundspeak:short_description html="True" />
                        <groundspeak:long_description html="True">' . gpxEncode($description) . '</groundspeak:long_description>
                        <groundspeak:encoded_hints />
                        <groundspeak:logs />
                        <groundspeak:travelbugs />
                    </groundspeak:cache>
                </wpt>';
                $stage++;
                $id--;
            }
        }

        $xml .= '</gpx>';

        $zip = new ZipArchive;
        $tmpFile = tempnam($tmpDir, 'lab2gpx');
        if (! $zip->open($tmpFile, ZipArchive::CREATE)) {
            echo $LANG['ERROR_ZIP_FAILED'];
            exit;
        }
        $zip->addFromString('labs2gpx.gpx', $xml);
        $zip->close();
        header("Content-type: application/zip");
        header("Content-Disposition: attachment; filename=labs2gpx.zip");
        header("Content-length: " . filesize($tmpFile));
        header("Pragma: no-cache");
        header("Expires: 0");
        readfile($tmpFile);
        exit;
    }
}
?>
<html lang="<?php echo $lang; ?>">
<!DOCTYPE html>
<head>
    <title>Lab2Gpx</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, height=device-height, user-scalable=no">
    <link rel="apple-touch-icon" sizes="180x180" href="images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="images/favicon-16x16.png">
    <link rel="manifest" href="images/site.webmanifest">
    <meta name="description" content="<?php echo $LANG['META_DESCRIPTION'] ?>"/>
    <meta property="og:title" content="Lab2Gpx"/>
    <meta property="og:description" content="<?php echo $LANG['META_DESCRIPTION'] ?>"/>
    <meta property="og:image" content="https://gcutils.de/lab2gpx/images/geocaching.png"/>
    <meta property="og:url" content="https://gcutils.de/lab2gpx/"/>
    <meta property="og:type" content="website"/>

    <link rel="stylesheet" href="https://npmcdn.com/leaflet@1.0.0-rc.2/dist/leaflet.css"/>
    <style type="text/css">
        html, body {
            font-family: sans-serif;
        }

        .page {
            margin: 0 auto;
            max-width: 800px;
            padding-bottom: 10em;
        }

        label {
            display: block;
            width: 100%;
            margin-bottom: 0.2em;
        }

        fieldset {
            margin-bottom: 1em;
        }

        .form-row {
            margin-bottom: 1em;
        }

        .form-row p {
            margin: 0;
            font-size: 80%;
        }

        .form-row p.error {
            color: #990000;
        }

        input[type=text] {
            width: 100%;
        }

        textarea {
            width: 100%;
        }

        #mapDiv {
            height: 300px;
            margin-bottom: 1em;
        }
    </style>
</head>
<body>
<div class="page">
    <?php echo $LANG['INTRO']; ?>
    <form method="post" onsubmit="downloadButton.disabled = true; return true;">
        <fieldset>
            <legend><?php echo $LANG['LEGEND_GENERAL']; ?></legend>
            <div class="form-row<?php echo(isset($errors['coordinates']) ? ' error' : ''); ?>">
                <label for="coordinates"><?php echo $LANG['LABEL_COORDINATES']; ?>:</label>
                <input type="text" id="coordinates" name="coordinates" value="<?php echo htmlspecialchars((string) $values['coordinates']); ?>"/>
                <?php if (isset($errors['coordinates'])) {
                    echo '<p class="error">' . $errors['coordinates'] . '</p>';
                } ?>
            </div>

            <p><?php echo $LANG['LABEL_MAP']; ?></p>
            <p style="text-align: right"><a href="#" id="posLink" onclick="getLocation()"><?php echo $LANG['CURRENT_POSITION']; ?></a></p>
            <div id="mapDiv"></div>

            <div class="form-row<?php echo(isset($errors['radius']) ? ' error' : ''); ?>">
                <label for="radius"><?php echo $LANG['LABEL_RADIUS']; ?>:</label>
                <input type="text" id="radius" name="radius" value="<?php echo htmlspecialchars((string) $values['radius']); ?>"/>
                <?php if (isset($errors['radius'])) {
                    echo '<p class="error">' . $errors['radius'] . '</p>';
                } ?>
            </div>

            <div class="form-row<?php echo(isset($errors['take']) ? ' error' : ''); ?>">
                <label for="take"><?php echo $LANG['LABEL_TAKE']; ?>:</label>
                <input type="text" id="take" name="take" value="<?php echo htmlspecialchars((string) $values['take']); ?>"/>
                <?php if (isset($errors['take'])) {
                    echo '<p class="error">' . $errors['take'] . '</p>';
                } ?>
            </div>

            <div class="form-row">
                <label for="cacheType"><?php echo $LANG['LABEL_CACHE_TYPE']; ?></label>
                <select name="cacheType" id="cacheType">
                    <?php
                    foreach ($cacheTypes as $type) {
                        echo '<option value="' . $type . '"' . ($values['cacheType'] === $type ? ' selected="selected"' : '') . '>' . $type . '</option>';
                    }
                    ?>
                </select>
                <p><?php echo $LANG['LABEL_HINT_CACHE_TYPE']; ?></p>
            </div>
        </fieldset>

        <fieldset>
            <legend><?php echo $LANG['LEGEND_DESCRIPTION']; ?></legend>
            <div class="form-row">
                <label>
                    <input type="hidden" name="includeQuestion" value="0">
                    <input type="checkbox" name="includeQuestion"<?php echo($values['includeQuestion'] ? ' checked="checked"' : ''); ?> /> <?php echo $LANG['LABEL_INCLUDE_QUESTION']; ?>
                </label>
            </div>
            <div class="form-row">
                <label>
                    <input type="hidden" name="includeWaypointDescription" value="0">
                    <input type="checkbox" name="includeWaypointDescription"<?php echo($values['includeWaypointDescription'] ? ' checked="checked"' : ''); ?> /> <?php echo $LANG['LABEL_INCLUDE_DESCRIPTION']; ?>
                </label>
            </div>
            <div class="form-row">
                <label>
                    <input type="hidden" name="includeCacheDescription" value="0">
                    <input type="checkbox" name="includeCacheDescription"<?php echo($values['includeCacheDescription'] ? ' checked="checked"' : ''); ?> /> <?php echo $LANG['LABEL_INCLUDE_CACHE_DESCRIPTION']; ?>
                </label>
            </div>
            <div class="form-row">
                <label>
                    <input type="hidden" name="includeAwardMessage" value="0">
                    <input type="checkbox" name="includeAwardMessage"<?php echo($values['includeAwardMessage'] ? ' checked="checked"' : ''); ?> /> <?php echo $LANG['LABEL_INCLUDE_AWARD']; ?>
                </label>
            </div>
        </fieldset>

        <fieldset>
            <legend><?php echo $LANG['LEGEND_EXCLUDE']; ?></legend>
            <div class="form-row">
                <label for="excludeOwner"><?php echo $LANG['LABEL_EXCLUDE_OWNER']; ?>:</label>
                <textarea id="excludeOwner" name="excludeOwner" rows="3"><?php echo htmlspecialchars($values['excludeOwner']); ?></textarea>
            </div>

            <div class="form-row">
                <label for="findsHtml"><?php echo $LANG['LABEL_EXCLUDE_FINDS']; ?>:</label>
                <textarea id="findsHtml" name="findsHtml" rows="10"><?php echo htmlspecialchars($values['findsHtml']); ?></textarea>
                <p><?php echo $LANG['LABEL_HINT_EXCLUDE_FINDS']; ?></p>
            </div>

            <div class="form-row">
                <label>
                    <input type="hidden" name="includeFinds" value="0">
                    <input type="checkbox" name="includeFinds"<?php echo($values['includeFinds'] ? ' checked="checked"' : ''); ?> /> <?php echo $LANG['LABEL_INCLUDE_FINDS']; ?>
                </label>
            </div>
        </fieldset>

        <input type="submit" name="downloadButton" id="downloadButton" value="<?php echo $LANG['LABEL_DOWNLOAD_GPX']; ?>"/> <strong><?php echo $LANG['LABEL_HINT_DOWNLOAD_GPX']; ?></strong>
    </form>
</div>

<script src="https://npmcdn.com/leaflet@1.0.0-rc.2/dist/leaflet.js"></script>
<script type="text/javascript">

    let coords = [<?php echo $coordinates->getLat(); ?>, <?php echo $coordinates->getLng(); ?>];
    const map = L.map('mapDiv', {doubleClickZoom: false}).setView(coords, 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        minZoom: 2,
        maxZoom: 19,
    }).addTo(map);

    let marker = L.marker(coords).addTo(map);

    map.on('click', function (e) {
        if (marker) {
            marker.remove();
        }
        const lat = e.latlng.lat.toFixed(5);
        const lon = e.latlng.lng.toFixed(5);
        marker = L.marker([lat, lon]).addTo(map);
        document.getElementById('coordinates').value = lat + ', ' + lon;
    });

    function getLocation () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(showPosition);
        } else {
            alert('<?php echo $LANG['BROWSER_NO_LOCATION'] ?>');
        }
    }

    function showPosition (position) {
        document.getElementById('coordinates').value = position.coords.latitude + ', ' + position.coords.longitude;
        if (marker) {
            marker.remove();
        }
        marker = L.marker([position.coords.latitude, position.coords.longitude]).addTo(map);
        map.setView([position.coords.latitude, position.coords.longitude], 16);
    }
</script>

</body>
</html>
