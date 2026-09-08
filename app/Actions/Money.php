<?php

declare(strict_types=1);

namespace App\Actions;

use InvalidArgumentException;
use OverflowException;

class Money
{
    public const MaxMinor = 9_007_199_254_740_991;

    public static function parse(string $decimal, int $precision = 2): int
    {
        if ($precision < 0 || $precision > 4 || ! preg_match('/\A([+-]?)([0-9]+)(?:\.([0-9]+))?\z/', $decimal, $parts)) {
            throw new InvalidArgumentException('Use a plain decimal amount and precision between zero and four.');
        }

        $fraction = $parts[3] ?? '';

        if (strlen($fraction) > $precision) {
            throw new InvalidArgumentException('The amount has more decimal places than the currency supports.');
        }

        $digits = ltrim($parts[2].str_pad($fraction, $precision, '0'), '0');
        $limit = (string) self::MaxMinor;

        if (strlen($digits) > strlen($limit) || (strlen($digits) === strlen($limit) && strcmp($digits, $limit) > 0)) {
            throw new OverflowException('The amount exceeds the supported range.');
        }

        return ($parts[1] === '-' ? -1 : 1) * (int) $digits;
    }

    public static function add(int $left, int $right): int
    {
        if (abs($left) > self::MaxMinor || abs($right) > self::MaxMinor ||
            ($right > 0 && $left > self::MaxMinor - $right) ||
            ($right < 0 && $left < -self::MaxMinor - $right)) {
            throw new OverflowException('The total exceeds the supported range.');
        }

        return $left + $right;
    }
}
