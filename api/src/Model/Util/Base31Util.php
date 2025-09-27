<?php

declare(strict_types=1);

namespace App\Model\Util;

class Base31Util
{
    public static function convertToBase31(int $id): string
    {
        $base31 = '';
        $alphabet = '0123456789ABCDEFGHJKMNPQRTVWXYZ'; // Base 31 alphabet without 'ILOSU' (like GC Code)
        while ($id > 0) {
            $base31 = $alphabet[$id % 31] . $base31;
            $id = (int) ($id / 31);
        }

        return $base31;
    }
}
