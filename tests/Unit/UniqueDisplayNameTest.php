<?php

namespace Tests\Unit;

use App\Models\AdminFile;
use App\Support\UniqueDisplayName;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniqueDisplayNameTest extends TestCase
{
    use RefreshDatabase;

    public function test_keeps_name_when_it_is_available(): void
    {
        $this->assertSame('report.pdf', UniqueDisplayName::make('report.pdf'));
    }

    public function test_appends_windows_style_counter_before_the_extension(): void
    {
        AdminFile::factory()->create(['name' => 'report.pdf']);

        $this->assertSame('report (1).pdf', UniqueDisplayName::make('report.pdf'));
    }

    public function test_increments_until_a_free_name_is_found(): void
    {
        AdminFile::factory()->create(['name' => 'report.pdf']);
        AdminFile::factory()->create(['name' => 'report (1).pdf']);

        $this->assertSame('report (2).pdf', UniqueDisplayName::make('report.pdf'));
    }

    public function test_treats_dotfiles_as_the_full_base_name(): void
    {
        AdminFile::factory()->create(['name' => '.env']);

        $this->assertSame('.env (1)', UniqueDisplayName::make('.env'));
    }

    public function test_strips_directory_components_from_uploaded_names(): void
    {
        $this->assertSame('notes.txt', UniqueDisplayName::make('folder/notes.txt'));
    }
}
