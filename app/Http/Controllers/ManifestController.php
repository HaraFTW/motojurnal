<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

class ManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $appUrl = rtrim(config('app.url'), '/');
        $scopePath = parse_url($appUrl, PHP_URL_PATH) ?: '';
        $scope = ($scopePath !== '' ? rtrim($scopePath, '/') : '').'/';

        return response()->json([
            'name' => config('app.name'),
            'short_name' => config('app.name'),
            'description' => 'Jurnal motocicletă',
            'id' => $scope,
            'start_url' => route('dashboard', absolute: true),
            'scope' => $scope,
            'display' => 'standalone',
            'background_color' => '#09090b',
            'theme_color' => '#09090b',
            'lang' => str_replace('_', '-', app()->getLocale()),
            'icons' => [
                [
                    'src' => asset('icons/icon-192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('icons/icon-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('icons/icon-512-maskable.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ], headers: [
            'Content-Type' => 'application/manifest+json',
        ]);
    }
}
