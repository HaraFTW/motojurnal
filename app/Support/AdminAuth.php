<?php

namespace App\Support;

use Illuminate\Http\Request;

final class AdminAuth
{
    public const SESSION_GATE = 'admin.gate';

    public const SESSION_UNLOCKED = 'admin.unlocked';

    public static function gateIsConfigured(): bool
    {
        return config('admin.gate_key') !== '' && config('admin.gate_value') !== '';
    }

    public static function requestHasValidGate(Request $request): bool
    {
        if (! self::gateIsConfigured()) {
            return false;
        }

        $key = (string) config('admin.gate_key');

        return $request->query($key) === (string) config('admin.gate_value');
    }

    public static function requestHasWrongGate(Request $request): bool
    {
        if (! self::gateIsConfigured()) {
            return true;
        }

        $key = (string) config('admin.gate_key');

        return $request->query->has($key)
            && $request->query($key) !== (string) config('admin.gate_value');
    }

    public static function rememberGate(Request $request): void
    {
        $request->session()->put(self::SESSION_GATE, true);
    }

    public static function gateAllows(Request $request): bool
    {
        if (self::requestHasWrongGate($request)) {
            return false;
        }

        if (self::requestHasValidGate($request)) {
            self::rememberGate($request);

            return true;
        }

        return $request->session()->get(self::SESSION_GATE) === true;
    }

    public static function passwordMatches(mixed $attempt): bool
    {
        $expected = (string) config('admin.password');

        if ($expected === '' || ! is_string($attempt)) {
            return false;
        }

        return hash_equals($expected, $attempt);
    }

    public static function unlock(Request $request): void
    {
        $request->session()->put(self::SESSION_UNLOCKED, true);
    }

    public static function isUnlocked(Request $request): bool
    {
        return $request->session()->get(self::SESSION_UNLOCKED) === true;
    }
}
