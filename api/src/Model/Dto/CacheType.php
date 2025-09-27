<?php

declare(strict_types=1);

namespace App\Model\Dto;

enum CacheType: string
{
    case LAB_CACHE = 'Lab Cache';
    case VIRTUAL_CACHE = 'Virtual Cache';
    case MEGA_EVENT_CACHE = 'Mega-Event Cache';
}
