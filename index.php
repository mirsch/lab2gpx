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

use App\Exporter\CacheturDotNoExporter;
use App\Exporter\GpxExporter;
use App\Exporter\GpxWaypointExporter;
use Location\Coordinate;
use Location\Factory\CoordinateFactory;

// ini_set('display_errors', 'on');
// error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';
const IN_APP = true;
$config['CONSUMER_KEY'] = 'THE-TOP-SECRET-CONSUMER-KEY';
$config['enable_logging'] = false;
require_once __DIR__ . '/config.local.php';

$dataDir = __DIR__ . '/data';
$logFile = __DIR__ . '/lab2gpx.log';
$tmpDir = sys_get_temp_dir();
const CACHE_LIFE_TIME_IN_SECONDS = 24 * 60 * 60;

function fetch(string $url): string
{
    global $config;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Adventures/1.3.4 (2408) (ios/14.6)');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'x-consumer-key: ' . $config['CONSUMER_KEY'],
    ]);

    $data = curl_exec($ch);

    if ($data === false) {
        throw new UnexpectedValueException(curl_error($ch));
    }
    curl_close($ch);

    return $data;
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
    $file = $dataDir . '/search_' . md5($url) . '.json';
    file_put_contents($file, json_encode($labCaches, JSON_PRETTY_PRINT));

    foreach ($labCaches['Items'] as $cache) {
        if (count($fetchedLabs) >= $max) {
            return;
        }
        $file = $dataDir . '/' . $cache['Id'] . '.json';
        if (file_exists($file) && filemtime($file) > time() - CACHE_LIFE_TIME_IN_SECONDS) {
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
if (isset($_GET['L']) && in_array($_GET['L'], $knownLangs)) {
    require __DIR__ . '/lang/' . $_GET['L'] . '.php';
    $lang = $_GET['L'];
}
if (! isset($_GET['L']) && isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $userPrefLangs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    foreach ($userPrefLangs as $idx => $browserLang) {
        $browserLang = substr($browserLang, 0, 2);
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
$linearTypes = [
    'default' => $LANG['LINEAR_TYPE_DEFAULT'],
    'first' => $LANG['LINEAR_TYPE_FIRST'],
    'mark' => $LANG['LINEAR_TYPE_MARK'],
    'corrected' => $LANG['LINEAR_TYPE_CORRECTED'],
    'ignore' => $LANG['LINEAR_TYPE_IGNORE'],
    'emoji' => $LANG['LINEAR_TYPE_EMOJI'],
];
$outputFormats = [
    'zippedgpx' => $LANG['OUTPUT_ZIPPED_GPX'],
    'gpx' => $LANG['OUTPUT_GPX'],
    'zippedgpxwpt' => $LANG['OUTPUT_ZIPPED_GPX_WPT'],
    'gpxwpt' => $LANG['OUTPUT_GPX_WPT'],
    'cacheturdotno' => $LANG['OUTPUT_CACHETUR_DOT_NO'],
];
$values = [
    'coordinates' => 'N50° 50.156 E012° 55.398',
    'radius' => 15,
    'take' => 300,
    'cacheType' => $cacheTypes[0],
    'prefix' => 'LC',
    'codeIsCaseSensitive' => false,
    'linear' => 'default',

    'includeQuestion' => true,
    'includeWaypointDescription' => true,
    'includeCacheDescription' => true,
    'includeAwardMessage' => false,

    'excludeOwner' => '',
    'findsHtml' => '',
    'includeFinds' => false,
    'uuidsToExclude' => [],

    'outputFormat' => 'zippedgpx',
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

    $values['includeAwardMessage'] = false;

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

    if (strlen($values['prefix']) > 3 || strlen($values['prefix']) < 2) {
        $errors['prefix'] = $LANG['INVALID_PREFIX'];
    }

    if (! array_key_exists($values['linear'], $linearTypes)) {
        $values['linear'] = 'default';
    }

    if (! array_key_exists($values['outputFormat'], $outputFormats)) {
        $values['linear'] = 'zippedgpx';
    }

    $values['uuidsToExclude'] = str_replace(',', "\n", $values['uuidsToExclude']);
    $values['uuidsToExclude'] = str_replace(';', "\n", $values['uuidsToExclude']);
    $values['uuidsToExclude'] = str_replace("\r", "\n", $values['uuidsToExclude']);
    $values['uuidsToExclude'] = array_map('trim', array_filter(explode("\n", $values['uuidsToExclude'])));

    if (function_exists('debug_values')) {
        debug_values($values);
    }

    if (isset($_FILES['findsHtmlFile']) && $_FILES['findsHtmlFile']['error'] === UPLOAD_ERR_OK) {
        $values['findsHtml'] = file_get_contents($_FILES['findsHtmlFile']['tmp_name']);

        if(preg_match('/<a.*class="username".*title="(.*)">/msU', $values['findsHtml'], $matches) === 1) {
            $values['username'] = $matches[1];
            $file = $dataDir . '/' . $values['username'] . '.html';
            move_uploaded_file($_FILES['findsHtmlFile']['tmp_name'], $file);
        }
    } else if (array_key_exists('username',$values) && !empty($values['username']) && file_exists($file = $dataDir . '/' . $values['username'] . '.html') && filemtime($file) > time() - CACHE_LIFE_TIME_IN_SECONDS) {
        $values['findsHtml'] = file_get_contents($file);
    }

    if (! $errors) {
        $cookieValues = $values;
        unset($cookieValues['findsHtml']);
        setcookie($cookieName, json_encode($cookieValues), time() + 999999);

        if ($config['enable_logging']) {
            file_put_contents($logFile, (new DateTimeImmutable())->format('Y-m-d H:i:s') . "\t" . $_SERVER['REMOTE_ADDR'] . "\t" . json_encode($cookieValues) . "\n", FILE_APPEND);
        }

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

        switch ($values['outputFormat']) {
            case 'zippedgpx':
            case 'zippedgpxwpt':
                if ($values['outputFormat'] === 'zippedgpxwpt') {
                    $exporter = new GpxWaypointExporter($dataDir, $LANG);
                } else {
                    $exporter = new GpxExporter($dataDir, $LANG);
                }
                $zip = new ZipArchive;
                $tmpFile = tempnam($tmpDir, 'lab2gpx');
                if (! $zip->open($tmpFile, ZipArchive::CREATE)) {
                    echo $LANG['ERROR_ZIP_FAILED'];
                    @unlink($tmpFile);
                    exit;
                }
                $xml = $exporter->export($fetchedLabs, $values, $ownersToSkip, $finds);
                $zip->addFromString('labs2gpx.gpx', $xml);
                $zip->close();
                header("Content-type: application/zip");
                header("Content-Disposition: attachment; filename=labs2gpx.zip");
                header("Content-length: " . filesize($tmpFile));
                header("Pragma: no-cache");
                header("Expires: 0");
                readfile($tmpFile);
                @unlink($tmpFile);
                exit;
            case 'gpx':
            case 'gpxwpt':
                if ($values['outputFormat'] === 'gpxwpt') {
                    $exporter = new GpxWaypointExporter($dataDir, $LANG);
                } else {
                    $exporter = new GpxExporter($dataDir, $LANG);
                }
                $xml = $exporter->export($fetchedLabs, $values, $ownersToSkip, $finds);
                header("Content-type: application/gpx+xml");
                header("Content-Disposition: attachment; filename=labs2gpx.gpx");
                header("Content-length: " . strlen($xml));
                header("Pragma: no-cache");
                header("Expires: 0");
                echo $xml;
                exit;
            case 'cacheturdotno':
                $exporter = new CacheturDotNoExporter($dataDir, $LANG);
                $cacheturDotNo = $exporter->export($fetchedLabs, $values, $ownersToSkip, $finds);
                header("Content-type: text/csv");
                header("Content-Disposition: attachment; filename=labs2gpx.csv");
                header("Content-length: " . strlen($cacheturDotNo));
                header("Pragma: no-cache");
                header("Expires: 0");
                echo $cacheturDotNo;
                exit;
            default:
                throw new RuntimeException('Unknown output format.');
        }
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
    <form enctype="multipart/form-data" method="post">
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

            <div class="form-row<?php echo(isset($errors['prefix']) ? ' error' : ''); ?>">
                <label for="take"><?php echo $LANG['LABEL_PREFIX']; ?>:</label>
                <input type="text" id="prefix" name="prefix" value="<?php echo htmlspecialchars((string) $values['prefix']); ?>"/>
                <?php if (isset($errors['prefix'])) {
                    echo '<p class="error">' . $errors['prefix'] . '</p>';
                } ?>
            </div>

            <div class="form-row">
                <label>
                    <input type="hidden" name="codeIsCaseSensitive" value="0">
                    <input type="checkbox" name="codeIsCaseSensitive"<?php echo($values['codeIsCaseSensitive'] ? ' checked="checked"' : ''); ?> /> <?php echo $LANG['LABEL_CODE_IS_CASE_SENSITIVE']; ?>
                </label>
            </div>

            <div class="form-row">
                <label for="cacheType"><?php echo $LANG['LABEL_LINEAR']; ?></label>
                <select name="linear" id="linear">
                    <?php
                    foreach ($linearTypes as $type => $label) {
                        echo '<option value="' . $type . '"' . ($values['linear'] === $type ? ' selected="selected"' : '') . '>' . $label . '</option>';
                    }
                    ?>
                </select>
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
            <!--
            <div class="form-row">
                <label>
                    <input type="hidden" name="includeAwardMessage" value="0">
                    <input type="checkbox" name="includeAwardMessage"<?php echo($values['includeAwardMessage'] ? ' checked="checked"' : ''); ?> /> <?php echo $LANG['LABEL_INCLUDE_AWARD']; ?>
                </label>
            </div>
            -->
        </fieldset>

        <fieldset>
            <legend><?php echo $LANG['LEGEND_EXCLUDE']; ?></legend>
            <div class="form-row">
                <label for="excludeOwner"><?php echo $LANG['LABEL_EXCLUDE_OWNER']; ?>:</label>
                <textarea id="excludeOwner" name="excludeOwner" rows="3"><?php echo htmlspecialchars($values['excludeOwner']); ?></textarea>
            </div>

            <div class="form-row">
                <label for="findsHtml"><?php echo $LANG['LABEL_EXCLUDE_FINDS']; ?>:</label>
                <textarea id="findsHtml" name="findsHtml" rows="10"><?php echo htmlspecialchars($values['findsHtml']); ?></textarea><br />
                <input type="file" name="findsHtmlFile" />
                <p><?php echo $LANG['LABEL_HINT_EXCLUDE_FINDS']; ?></p>
            </div>

            <div class="form-row">
                <label>
                    <input type="hidden" name="includeFinds" value="0">
                    <input type="checkbox" name="includeFinds"<?php echo($values['includeFinds'] ? ' checked="checked"' : ''); ?> /> <?php echo $LANG['LABEL_INCLUDE_FINDS']; ?>
                </label>
            </div>

            <div class="form-row">
                <label for="uuidsToExclude"><?php echo $LANG['LABEL_UUIDS_TO_EXCLUDE']; ?>:</label>
                <textarea id="uuidsToExclude" name="uuidsToExclude" rows="10"><?php echo htmlspecialchars(implode("\n", $values['uuidsToExclude'])); ?></textarea>
            </div>

        </fieldset>

        <fieldset>
            <legend><?php echo $LANG['LEGEND_DOWNLOAD']; ?></legend>
            <div class="form-row">
                <label for="outputFormat"><?php echo $LANG['LABEL_OUTPUT_FORMAT']; ?></label>
                <select name="outputFormat" id="outputFormat">
                    <?php
                    foreach ($outputFormats as $format => $label) {
                        echo '<option value="' . $format . '"' . ($values['outputFormat'] === $format ? ' selected="selected"' : '') . '>' . $label . '</option>';
                    }
                    ?>
                </select>
            </div>

            <input type="submit" name="downloadButton" id="downloadButton" value="<?php echo $LANG['LABEL_DOWNLOAD']; ?>"/> <strong><?php echo $LANG['LABEL_HINT_DOWNLOAD']; ?></strong>
        </fieldset>

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
