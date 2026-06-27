<?php

namespace Tests\Feature;

use Tests\TestCase;

class ManifestTest extends TestCase
{
    public function test_manifest_is_available(): void
    {
        $response = $this->get('/manifest.webmanifest');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/manifest+json');
        $response->assertJsonFragment([
            'name' => config('app.name'),
            'display' => 'standalone',
            'theme_color' => '#09090b',
        ]);
        $response->assertJsonPath('icons.0.sizes', '192x192');
    }
}
