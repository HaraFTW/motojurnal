<?php

namespace Tests\Unit;

use App\Support\PhpUploadLimit;
use Tests\TestCase;

class PhpUploadLimitTest extends TestCase
{
    public function test_parses_ini_shorthand_sizes(): void
    {
        $this->assertSame(2 * 1024 * 1024, PhpUploadLimit::toBytes('2M'));
        $this->assertSame(512 * 1024, PhpUploadLimit::toBytes('512K'));
        $this->assertSame(8, PhpUploadLimit::toBytes('8'));
    }
}
