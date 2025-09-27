<?php

declare(strict_types=1);

namespace App\Model\Dto;

enum OutputFormat: string
{
    case ZIPPED_GPX = 'zippedgpx';
    case GPX = 'gpx';
    case ZIPPED_GPX_WPT = 'zippedgpxwpt';
    case GPX_WPT = 'gpxwpt';
    case CACHETURDOTNO = 'cacheturdotno';
}
