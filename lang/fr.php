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

/** 
  * Traduction Verturin 04-2022
*/

$LANG['INVALID_COORDINATES'] = 'Format de coordonnées invalide.';
$LANG['INVALID_RADIUS_HIGH'] = 'Le rayon ne doit pas être supérieur à 20 000 km.';
$LANG['INVALID_RADIUS_LOW'] = 'Le rayon ne doit pas être inférieur à 1 km.';
$LANG['TAG_LINEAR'] = 'linéaire';
$LANG['HEADER_QUESTION'] = 'Question';
$LANG['HEADER_WAYPOINT_DESCRIPTION'] = 'Waypoint Description';
$LANG['HEADER_LAB_DESCRIPTION'] = 'Adventure Lab Descriptions';
$LANG['HEADER_AWARD'] = 'Award Information';
$LANG['HEADER_AWARD_MESSAGE'] = 'Award Message';
$LANG['ERROR_ZIP_FAILED'] = 'Désolé, quelque chose s'est mal passé. Fichier non créé.';
$LANG['INTRO'] = '
    <p>Cet outil génère un fichier GPX contenant Geocaching Adventure Labs à utiliser dans plusieurs applications de géocaching et appareils GPS Garmin.</p>
    <p>Le site Web stocke vos paramètres (à l'exception de vos trouvailles) dans un cookie. Ne supprimez donc pas ce cookie si vous souhaitez conserver vos paramètres pour la prochaine fois que vous utiliserez ce site.</p>
    <p>Si vous trouvez des bugs ou avez des demandes de fonctionnalités, n'hésitez pas à <a href="https://github.com/mirsch/lab2gpx/issues" target="_blank">ouvrir un problème</a>.</ p>
    <p>Pour d'autres demandes, vous pouvez utiliser l'adresse e-mail suivante : lab2gpx@gcutils.de
     (Les questions sur l'utilisation, les rapports d'erreur ou les demandes de fonctionnalités ne recevront pas de réponse par e-mail)</p>
    ';
$LANG['LEGEND_GENERAL'] = 'Général';
$LANG['LEGEND_DESCRIPTION'] = 'Description';
$LANG['LEGEND_EXCLUDE'] = 'Exclure';
$LANG['LEGEND_DOWNLOAD'] = 'Télécharger';
$LANG['LABEL_COORDINATES'] = 'Coordonnées par ex. 50.83593, 12.92329 ou N50° 50.156 E012° 55.397';
$LANG['LABEL_MAP'] = 'Ou cliquez sur la carte pour sélectionner un emplacement.';
$LANG['LABEL_RADIUS'] = 'Rayon en km';
$LANG['LABEL_TAKE'] = 'Max Caches (1 to 500)';
$LANG['LABEL_CACHE_TYPE'] = 'Cache Type';
$LANG['LABEL_HINT_CACHE_TYPE'] = 'Les appareils GPS Garmin ne prennent pas en charge Adventure Labs. Vous souhaitez probablement les exporter en tant que caches virtuels.';
$LANG['LABEL_INCLUDE_QUESTION'] = 'Inclure la question du Waypoint dans la description du cache';
$LANG['LABEL_INCLUDE_DESCRIPTION'] = 'Inclure la description du waypoint dans la description du cache';
$LANG['LABEL_INCLUDE_CACHE_DESCRIPTION'] = 'Inclure la description de l'Adventure Lab dans la description du cache';
$LANG['LABEL_INCLUDE_AWARD'] = 'Inclure le message de récompense dans la description du cache <i>(peut contenir des spoilers)</i>';
$LANG['LABEL_EXCLUDE_OWNER'] = 'Exclure le nom du propriétaire (un par ligne)';
$LANG['LABEL_EXCLUDE_FINDS'] = 'Excluez vos trouvailles, copiez et collez le code source de votre page de trouvailles';
$LANG['LABEL_HINT_EXCLUDE_FINDS'] = '
    Allez sur <a href="https://labs.geocaching.com/logs" target="_blank">https://labs.geocaching.com/logs</a>, connectez-vous pour voir vos logs.
     Sélectionnez "Afficher le code source" dans votre navigateur et copiez-collez l'intégralité du code source dans ce champ.';
$LANG['LABEL_DOWNLOAD'] = 'Télécharger';
$LANG['LABEL_HINT_DOWNLOAD'] = 'Cela peut prendre un certain temps. Soyez patient et ne cliquez qu'une seule fois.';
$LANG['NO_CACHES_FOUND'] = '<p>Désolé, aucune cache trouvée dans cette zone.</p><p><a href="/lab2gpx/">Retourner</a></p>';
$LANG['CURRENT_POSITION'] = 'trouver mon position actuel';
$LANG['BROWSER_NO_LOCATION'] = 'La géolocalisation n'est pas prise en charge par votre navigateur.';
$LANG['LABEL_INCLUDE_FINDS'] = 'Ne pas exclure mes trouvailles mais marquer comme trouvé';
$LANG['LABEL_UUIDS_TO_EXCLUDE'] = 'UUID à exclure (<a href="https://github.com/mirsch/lab2gpx/issues/44" target="_blank">#44</a>)';
$LANG['META_DESCRIPTION'] = 'Génère des fichiers GPX (PQ) pour Geocaching Adventure Labs';
$LANG['INVALID_PREFIX'] = 'Le préfixe doit comporter 2 à 3 caractères';
$LANG['LABEL_PREFIX'] = 'Code prefixe';
$LANG['LABEL_CODE_IS_CASE_SENSITIVE'] = 'Ne pas convertir le code en majuscule (<a href="https://github.com/mirsch/lab2gpx/issues/30" target="_blank">#30</a>)';
$LANG['LABEL_LINEAR'] = 'Adventure Labs linéaire';
$LANG['LINEAR_TYPE_DEFAULT'] = 'Défaut';                                                   
$LANG['LINEAR_TYPE_FIRST'] = 'Inclure uniquement la première/prochaine station';
$LANG['LINEAR_TYPE_MARK'] = 'Marquez en utilisant [L] dans le titre';
$LANG['LINEAR_TYPE_CORRECTED'] = 'Comme coordonnées corrigées';
$LANG['LINEAR_TYPE_IGNORE'] = 'Ignore';
$LANG['LABEL_OUTPUT_FORMAT'] = 'Format de sortie';
$LANG['OUTPUT_ZIPPED_GPX'] = 'GPX zippé';
$LANG['OUTPUT_GPX'] = 'GPX';
$LANG['OUTPUT_GPX_WPT'] = 'GPX avec waypoints';
$LANG['OUTPUT_ZIPPED_GPX_WPT'] = 'GPX zippé avec waypoints';
$LANG['OUTPUT_CACHETUR_DOT_NO'] = 'Cachetur.no Ajout en masse de waypoints';
