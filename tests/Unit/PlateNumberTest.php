<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\TestCase;

class PlateNumberTest extends TestCase
{
    public function test_normalize_strips_spaces_and_dashes_and_uppercases(): void
    {
        $this->assertSame('B123ABC', User::normalizePlateNumber('b 12-3 abc'));
        $this->assertSame('B123ABC', User::normalizePlateNumber('B-123 ABC'));
    }

    public function test_normalize_replaces_romanian_diacritics(): void
    {
        $this->assertSame('B123SIB', User::normalizePlateNumber('B 123 ȘIB'));
        $this->assertSame('B123SIB', User::normalizePlateNumber('B 123 ŞIB'));
        $this->assertSame('B123ABC', User::normalizePlateNumber('B 123 ÂBC'));
        $this->assertSame('B123ABC', User::normalizePlateNumber('B 123 ĂBC'));
        $this->assertSame('IS123', User::normalizePlateNumber('ÎS 1-2-3'));
        $this->assertSame('TM12ABC', User::normalizePlateNumber('ȚM 12 ÂBC'));
        $this->assertSame('TM12ABC', User::normalizePlateNumber('ŢM 12 ĂBC'));
    }
}
