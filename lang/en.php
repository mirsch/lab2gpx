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

$LANG['INVALID_COORDINATES'] = 'Invalid coordinate format.';
$LANG['INVALID_RADIUS_HIGH'] = 'Radius must not be greater than 20000 km.';
$LANG['INVALID_RADIUS_LOW'] = 'Radius must not be lower than 1 km.';
$LANG['TAG_LINEAR'] = 'linear';
$LANG['HEADER_QUESTION'] = 'Question';
$LANG['HEADER_WAYPOINT_DESCRIPTION'] = 'Waypoint Description';
$LANG['HEADER_LAB_DESCRIPTION'] = 'Adventure Lab Descriptions';
$LANG['HEADER_AWARD'] = 'Award Information';
$LANG['HEADER_AWARD_MESSAGE'] = 'Award Message';
$LANG['ERROR_ZIP_FAILED'] = 'Sorry, some thing went wrong. File not created.';
$LANG['INTRO'] = '
    <p>This tool generates a GPX file containing Geocaching Adventure Labs for use in Geocaching apps and Garmin GPS devices.</p>
    <p>Tested on iOS using Cachly App and Garmin Oregon 450.</p>
    <p>This software is in alpha state and may not work as expected.</p>
    <p>The Website stores your settings (except your finds) in a cookie. So don\'t delete this cookie if you want to keep your settings for the next time you use this site.</p>
    <p>If you find bugs or have feature requests, feel free to <a href="https://github.com/mirsch/lab2gpx/issues" target="_blank">open an issue</a>.</p>';
$LANG['LEGEND_GENERAL'] = 'General';
$LANG['LEGEND_DESCRIPTION'] = 'Description';
$LANG['LEGEND_EXCLUDE'] = 'Exclude';
$LANG['LABEL_COORDINATES'] = 'Coordinates e.g. 50.83593, 12.92329 or N50° 50.156 E012° 55.397';
$LANG['LABEL_MAP'] = 'Or click on the map to select a location.';
$LANG['LABEL_RADIUS'] = 'Radius in km';
$LANG['LABEL_TAKE'] = 'Max Caches (1 to 500)';
$LANG['LABEL_CACHE_TYPE'] = 'Cache Type';
$LANG['LABEL_HINT_CACHE_TYPE'] = 'Garmin GPS devices do not support Adventure Labs. You probably want to export them as virtual caches.';
$LANG['LABEL_INCLUDE_QUESTION'] = 'Include Waypoint question in Cache description';
$LANG['LABEL_INCLUDE_DESCRIPTION'] = 'Include description of the Waypoint in Cache description';
$LANG['LABEL_INCLUDE_CACHE_DESCRIPTION'] = 'Include the description of the Adventure Lab in the Cache description';
$LANG['LABEL_INCLUDE_AWARD'] = 'Include Award Message in Cache description <i>(may contain spoilers)</i>';
$LANG['LABEL_EXCLUDE_OWNER'] = 'Exclude Owner name (one per Line)';
$LANG['LABEL_EXCLUDE_FINDS'] = 'Exclude your finds, copy & paste source code of your finds page';
$LANG['LABEL_HINT_EXCLUDE_FINDS'] = '
    Go to <a href="https://labs.geocaching.com/logs" target="_blank">https://labs.geocaching.com/logs</a>, log in so you can see your logs.
    Select "view source code" in your browser and copy & paste the entire source code into this field.';
$LANG['LABEL_DOWNLOAD_GPX'] = 'Download GPX';
$LANG['LABEL_HINT_DOWNLOAD_GPX'] = 'This can take some time. Please be patient and only click once.';
$LANG['NO_CACHES_FOUND'] = '<p>Sorry, no caches found in this area.</p><p><a href="/lab2gpx/">Go back</a></p>';
$LANG['CURRENT_POSITION'] = 'find my current position';
$LANG['BROWSER_NO_LOCATION'] = 'Geolocation is not supported by your browser.';
$LANG['LABEL_INCLUDE_FINDS'] = 'Do not exclude my finds but mark as found';
