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
$LANG['INVALID_GUID'] = 'GUID must only contain letters a-f, numbers 0-9 and hyphen (-)';
$LANG['TAG_LINEAR'] = 'linear';
$LANG['HEADER_QUESTION'] = 'Question';
$LANG['HEADER_WAYPOINT_DESCRIPTION'] = 'Waypoint Description';
$LANG['HEADER_LAB_DESCRIPTION'] = 'Adventure Lab Descriptions';
$LANG['HEADER_AWARD'] = 'Award Information';
$LANG['HEADER_AWARD_MESSAGE'] = 'Award Message';
$LANG['ERROR_ZIP_FAILED'] = 'Sorry, some thing went wrong. File not created.';
$LANG['INTRO'] = '
    <p>This tool generates a GPX file containing Geocaching Adventure Labs for use in several Geocaching Apps and Garmin GPS devices.</p>
    <p>The Website stores your settings (except your finds) in a cookie. So don\'t delete this cookie if you want to keep your settings for the next time you use this site.</p>
    <p>If you find bugs or have feature requests, feel free to <a href="https://github.com/mirsch/lab2gpx/issues" target="_blank">open an issue</a>.</p>
    <p><strong>Update to API V4</strong>. You can access the old version using API V3 by clicking <a href="https://gcutils.de/lab2gpx/apiv3/">here</a>.</p>
    ';
$LANG['LEGEND_GENERAL'] = 'General';
$LANG['LEGEND_DESCRIPTION'] = 'Description';
$LANG['LEGEND_EXCLUDE'] = 'Exclude';
$LANG['LEGEND_DOWNLOAD'] = 'Download';
$LANG['LABEL_COORDINATES'] = 'Coordinates e.g. 50.83593, 12.92329 or N50° 50.156 E012° 55.397';
$LANG['LABEL_MAP'] = 'Or click on the map to select a location.';
$LANG['LABEL_RADIUS'] = 'Radius in km';
$LANG['LABEL_TAKE'] = 'Max Caches (1 to 500)';
$LANG['LABEL_CACHE_TYPE'] = 'Cache Type';
$LANG['LABEL_HINT_CACHE_TYPE'] = 'Garmin GPS devices do not support Adventure Labs. You probably want to export them as virtual caches.';
$LANG['LABEL_INCLUDE_QUESTION'] = 'Include Waypoint question in Cache description';
$LANG['LABEL_INCLUDE_DESCRIPTION'] = 'Include description of the Waypoint in Cache description';
$LANG['LABEL_INCLUDE_CACHE_DESCRIPTION'] = 'Include the description of the Adventure Lab in the Cache description';
$LANG['LABEL_EXCLUDE_OWNER'] = 'Exclude Owner name (one per Line)';
$LANG['LABEL_USERGUID'] = "Calling user's public GUID";
$LANG['LABEL_HINT_USER_GUID'] = '
    Go to <a href="https://www.geocaching.com/find/default.aspx" target="_blank">https://www.geocaching.com/find/default.aspx</a> and look up your own username to find this. 
    The GUID is included at the end of the URL of the resulting page. (...?guid=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxx)';
$LANG['LABEL_INCLUDE_FOUND'] = 'Include fully completed labs';
$LANG['LABEL_INCLUDE_PARTIAL'] = 'Include partially completed labs';
$LANG['LABEL_INCLUDE_UNFOUND'] = 'Include unstarted labs';
$LANG['LABEL_DOWNLOAD'] = 'Download';
$LANG['LABEL_HINT_DOWNLOAD'] = 'This can take some time. Please be patient and only click once.';
$LANG['NO_CACHES_FOUND'] = '<p>Sorry, no caches found in this area.</p><p><a href="/lab2gpx/">Go back</a></p>';
$LANG['CURRENT_POSITION'] = 'find my current position';
$LANG['BROWSER_NO_LOCATION'] = 'Geolocation is not supported by your browser.';
$LANG['LABEL_UUIDS_TO_EXCLUDE'] = 'UUIDs to exclude (<a href="https://github.com/mirsch/lab2gpx/issues/44" target="_blank">#44</a>)';
$LANG['META_DESCRIPTION'] = 'Generates GPX files (PQ) for Geocaching Adventure Labs';
$LANG['INVALID_PREFIX'] = 'The prefix must be 2-3 characters long';
$LANG['LABEL_PREFIX'] = 'Code prefix ("AL" if using c:geo)';
$LANG['LABEL_LINEAR'] = 'Linear Adventure Labs';
$LANG['LINEAR_TYPE_DEFAULT'] = 'Default';
$LANG['LINEAR_TYPE_FIRST'] = 'Include only first/next station';
$LANG['LINEAR_TYPE_MARK'] = 'Mark using [L] in title';
$LANG['LINEAR_TYPE_CORRECTED'] = 'As corrected coordinates';
$LANG['LINEAR_TYPE_IGNORE'] = 'Ignore';
$LANG['LABEL_OUTPUT_FORMAT'] = 'Output format';
$LANG['OUTPUT_ZIPPED_GPX'] = 'Zipped GPX';
$LANG['OUTPUT_GPX'] = 'GPX';
$LANG['OUTPUT_GPX_WPT'] = 'GPX with Waypoints';
$LANG['OUTPUT_ZIPPED_GPX_WPT'] = 'Zipped GPX with Waypoints';
$LANG['OUTPUT_CACHETUR_DOT_NO'] = 'Cachetur.no Bulk Add Waypoints';
