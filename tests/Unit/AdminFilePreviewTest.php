<?php

namespace Tests\Unit;

use App\Models\AdminFile;
use Tests\TestCase;

class AdminFilePreviewTest extends TestCase
{
    public function test_pdf_and_images_open_in_the_browser(): void
    {
        $this->assertTrue((new AdminFile(['name' => 'scan.pdf']))->isBrowserPreviewable());
        $this->assertTrue((new AdminFile(['name' => 'photo.jpg']))->isBrowserPreviewable());
        $this->assertTrue((new AdminFile(['name' => 'photo.jpeg']))->isBrowserPreviewable());
        $this->assertTrue((new AdminFile(['name' => 'photo.png']))->isBrowserPreviewable());
        $this->assertTrue((new AdminFile(['name' => 'photo.webp']))->isBrowserPreviewable());
        $this->assertTrue((new AdminFile(['name' => 'clip.mp4']))->isBrowserPreviewable());
    }

    public function test_office_and_archives_are_downloaded(): void
    {
        $this->assertFalse((new AdminFile(['name' => 'notes.doc']))->isBrowserPreviewable());
        $this->assertFalse((new AdminFile(['name' => 'notes.docx']))->isBrowserPreviewable());
        $this->assertFalse((new AdminFile(['name' => 'sheet.xls']))->isBrowserPreviewable());
        $this->assertFalse((new AdminFile(['name' => 'sheet.xlsx']))->isBrowserPreviewable());
        $this->assertFalse((new AdminFile(['name' => 'pack.zip']))->isBrowserPreviewable());
    }
}
