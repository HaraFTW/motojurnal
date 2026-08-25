<?php

namespace App\Support;

final class PhpUploadLimit
{
    public static function bytes(): int
    {
        $upload = self::toBytes((string) ini_get('upload_max_filesize'));
        $post = self::toBytes((string) ini_get('post_max_size'));

        if ($post === 0) {
            return $upload;
        }

        if ($upload === 0) {
            return $post;
        }

        return min($upload, $post);
    }

    public static function formatted(): string
    {
        $bytes = self::bytes();
        $units = ['B', 'KB', 'MB', 'GB'];
        $value = (float) $bytes;
        $i = 0;

        while ($value >= 1024 && $i < count($units) - 1) {
            $value /= 1024;
            $i++;
        }

        $formatted = ($value >= 10 || $i === 0)
            ? (string) round($value)
            : number_format($value, 1, '.', '');

        return $formatted.' '.$units[$i];
    }

    public static function toBytes(string $value): int
    {
        $value = trim($value);

        if ($value === '') {
            return 0;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) substr($value, 0, -1);

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => (int) $value,
        };
    }
}
