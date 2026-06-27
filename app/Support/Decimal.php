<?php

namespace App\Support;

final class Decimal
{
    public const SCALE = 3;

    public const INPUT_STEP = '0.001';

    public static function format(float|string|null $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return number_format((float) $value, self::SCALE, '.', '');
    }

    public static function round(float $value): float
    {
        return round($value, self::SCALE);
    }

    public static function validationRule(bool $required = true): array
    {
        $rules = ['decimal:0,'.self::SCALE, 'min:0'];

        if ($required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return $rules;
    }
}
