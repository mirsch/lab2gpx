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

$LANG['INVALID_COORDINATES'] = 'Ungültiges Koordinatenformat.';
$LANG['INVALID_RADIUS_HIGH'] = 'Der Radius darf nicht größer als 20.000 km sein.';
$LANG['INVALID_RADIUS_LOW'] = 'Der Radius nicht kleiner als 1 km sein.';
$LANG['TAG_LINEAR'] = 'linear';
$LANG['HEADER_QUESTION'] = 'Frage';
$LANG['HEADER_WAYPOINT_DESCRIPTION'] = 'Beschreibung der Station';
$LANG['HEADER_LAB_DESCRIPTION'] = 'Beschreibung des Adventure Labs';
$LANG['HEADER_AWARD'] = 'Award Information';
$LANG['HEADER_AWARD_MESSAGE'] = 'Award Text';
$LANG['ERROR_ZIP_FAILED'] = 'Oh, da ging was schief. Datei konnte nicht erzeugt werden.';
$LANG['INTRO'] = '
    <p>Dieses Tool generiert eine GPX Datei mit Geocaching Adventure Labs für Geocaching Apps und Garmin GPS Geräte.</p>
    <p>Getestet unter iOS mit der Cachly App und Garmin Oregon 450.</p>
    <p>Diese Software befindet sich im Alpha Status und es kann sein, dass nicht alles wie erwartet funktioniert.</p>
    <p>Die Webseite speichert Deine Einstellungen in einem Cookie (außer Deine Funde). Du solltest das Cookie nicht löschen wenn Du Deine Einstellungen für das nächste Mal behalten willst.</p>
    <p>Bei Problemen oder Funktionswünschen kannst Du gern <a href="https://github.com/mirsch/lab2gpx/issues" target="_blank">ein Ticket schreiben</a>.</p>';
$LANG['LEGEND_GENERAL'] = 'Allgemein';
$LANG['LEGEND_DESCRIPTION'] = 'Beschreibung';
$LANG['LEGEND_EXCLUDE'] = 'Ausschließen';
$LANG['LABEL_COORDINATES'] = 'Koordinaten z.B. 50.83593, 12.92329 oder N50° 50.156 E012° 55.397';
$LANG['LABEL_MAP'] = 'Oder klicke auf die Karte um eine Position zu wählen.';
$LANG['LABEL_RADIUS'] = 'Radius in km';
$LANG['LABEL_TAKE'] = 'Max. Anzahl Caches (1 bis 500)';
$LANG['LABEL_CACHE_TYPE'] = 'Cache Typ';
$LANG['LABEL_HINT_CACHE_TYPE'] = 'Garmin GPS Geräte unterstützen keine Adventure Labs. Vielleicht willst Du sie lieber als virtuelle Caches exportieren.';
$LANG['LABEL_INCLUDE_QUESTION'] = 'Frage zur Station in Beschreibung einfügen';
$LANG['LABEL_INCLUDE_DESCRIPTION'] = 'Beschreibung der Station in Cache Beschreibung einfügen';
$LANG['LABEL_INCLUDE_CACHE_DESCRIPTION'] = 'Beschreibung des Adventure Labs in Cache Beschreibung einfügen';
$LANG['LABEL_INCLUDE_AWARD'] = 'Award Text in Cache Beschreibung einfügen <i>(kann Spoiler enthalten)</i>';
$LANG['LABEL_EXCLUDE_OWNER'] = 'Owner Name ausschließen (ein Name pro Zeile)';
$LANG['LABEL_EXCLUDE_FINDS'] = 'Deine Funde ausschließen, Source Code der Seite Deiner Funde kopieren & einfügen';
$LANG['LABEL_HINT_EXCLUDE_FINDS'] = '
    Gehe zu <a href="https://labs.geocaching.com/logs" target="_blank">https://labs.geocaching.com/logs</a> und logge Dich ein, so dass Du Deine Logs sehen kannst.
    Wähle in Deinem Browser "Quelltext anzeigen" und kopiere den gesamten Quelltext in dieses Feld.';
$LANG['LABEL_DOWNLOAD_GPX'] = 'Download GPX';
$LANG['LABEL_HINT_DOWNLOAD_GPX'] = 'Das kann eine Weile dauern. Bitte habe Geduld und klicke nur ein mal.';
$LANG['NO_CACHES_FOUND'] = '<p>Ooops, an den Koordinaten wurden keine Caches gefunden.</p><p><a href="/lab2gpx/">zurück</a></p>';
$LANG['CURRENT_POSITION'] = 'meine aktuelle Position';
$LANG['BROWSER_NO_LOCATION'] = 'Geolocation wird von Deinem Browser nicht unterstützt.';
$LANG['LABEL_INCLUDE_FINDS'] = 'Funde nicht ausschließen sondern als gefunden markieren';
$LANG['META_DESCRIPTION'] = 'Generiert GPX Dateien (PQ) für Geocaching Adventure Labs';
