<?php

declare(strict_types=1);

namespace App\Model\Dto;

class Coordinates
{
    public function __construct(public float $lat, public float $lon)
    {
    }
}
