<?php

declare(strict_types=1);

namespace App;

use Location\Coordinate;

class DecimalMinutes extends \Location\Formatter\Coordinate\DecimalMinutes
{
    public const UNITS_UTF8  = 'UTF-8';
    public const UNITS_ASCII = 'ASCII';

    protected $useCardinalLettersAsPrefix = true;

    public function useCardinalLettersAsPrefix(bool $value): DecimalMinutes
    {
        $this->useCardinalLettersAsPrefix = $value;

        return $this;
    }

    /**
     * @param float $lat
     *
     * @return string
     */
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

    /**
     * @param float $lng
     *
     * @return string
     */
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

    /**
     * @param float $lat
     *
     * @return string
     */
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

    /**
     * @param float $lng
     *
     * @return string
     */
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
