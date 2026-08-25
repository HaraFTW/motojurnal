<?php

namespace App\Support;

use App\Models\AdminFile;

final class UniqueDisplayName
{
    public static function make(string $original): string
    {
        $name = self::sanitize($original);

        if (! self::taken($name)) {
            return $name;
        }

        [$base, $extension] = self::split($name);
        $n = 1;

        do {
            $candidate = $extension === ''
                ? "{$base} ({$n})"
                : "{$base} ({$n}).{$extension}";
            $n++;
        } while (self::taken($candidate));

        return $candidate;
    }

    public static function sanitize(string $original): string
    {
        $name = str_replace(['\\', "\0"], ['/', ''], $original);
        $name = basename($name);
        $name = trim($name);

        if ($name === '' || $name === '.' || $name === '..') {
            return 'file';
        }

        if (mb_strlen($name) > 255) {
            return mb_substr($name, 0, 255);
        }

        return $name;
    }

    /**
     * @return array{0: string, 1: string}
     */
    public static function split(string $name): array
    {
        $filename = pathinfo($name, PATHINFO_FILENAME);
        $extension = pathinfo($name, PATHINFO_EXTENSION);

        if ($filename === '' && str_starts_with($name, '.')) {
            return [$name, ''];
        }

        return [$filename, $extension];
    }

    private static function taken(string $name): bool
    {
        return AdminFile::query()->where('name', $name)->exists();
    }
}
