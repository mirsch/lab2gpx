<?php

declare(strict_types=1);

namespace App\Model\Dto;

enum Linear: string
{
    case DEFAULT = 'default';
    case FIRST = 'first';
    case MARK = 'mark';
    case CORRECTED = 'corrected';
    case IGNORE = 'ignore';
}
