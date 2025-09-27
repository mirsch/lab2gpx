<?php

declare(strict_types=1);

namespace App\Model;

use Location\Formatter\Coordinate\DecimalMinutes as BaseDecimalMinutes;

class DecimalMinutes extends BaseDecimalMinutes
{
    protected bool $useCardinalLettersAsPrefix = true;

    protected function getLatPrefix(float $lat): string
    {
        if ($this->useCardinalLettersAsPrefix) {
            if ($lat >= 0) {
                return 'N ';
            }

            return 'S ';
        }

        if ($this->useCardinalLetters || $lat >= 0) {
            return '';
        }

        return '-';
    }

    protected function getLngPrefix(float $lng): string
    {
        if ($this->useCardinalLettersAsPrefix) {
            if ($lng >= 0) {
                return 'E ';
            }

            return 'W ';
        }

        if ($this->useCardinalLetters || $lng >= 0) {
            return '';
        }

        return '-';
    }

    protected function getLatSuffix(float $lat): string
    {
        if ($this->useCardinalLettersAsPrefix) {
            return '';
        }

        if (! $this->useCardinalLetters) {
            return '';
        }

        if ($lat >= 0) {
            return ' N';
        }

        return ' S';
    }

    protected function getLngSuffix(float $lng): string
    {
        if ($this->useCardinalLettersAsPrefix) {
            return '';
        }

        if (! $this->useCardinalLetters) {
            return '';
        }

        if ($lng >= 0) {
            return ' E';
        }

        return ' W';
    }
}
