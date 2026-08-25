<?php

namespace App\Models;

use Database\Factories\AdminFileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'stored_path',
    'extra',
    'size',
    'mime_type',
])]
class AdminFile extends Model
{
    /** @use HasFactory<AdminFileFactory> */
    use HasFactory;

    /** @var list<string> */
    private const PREVIEWABLE_EXTENSIONS = [
        'pdf',
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'avif', 'ico',
        'mp4', 'webm', 'ogg', 'ogv',
        'mp3', 'wav', 'm4a', 'aac', 'oga', 'flac',
        'txt',
    ];

    /** @var list<string> */
    private const BLOCKED_EXTENSIONS = [
        'svg', 'html', 'htm', 'xhtml', 'js', 'php',
    ];

    /** @var list<string> */
    private const PREVIEWABLE_MIMES = [
        'application/pdf',
        'text/plain',
        'video/mp4',
        'video/webm',
        'video/ogg',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $file): void {
            if ($file->stored_path !== '') {
                Storage::disk('local')->delete($file->stored_path);
            }
        });
    }

    public function isBrowserPreviewable(): bool
    {
        $extension = strtolower(pathinfo($this->name, PATHINFO_EXTENSION));

        if (in_array($extension, self::BLOCKED_EXTENSIONS, true)) {
            return false;
        }

        if (in_array($extension, self::PREVIEWABLE_EXTENSIONS, true)) {
            return true;
        }

        $mime = strtolower((string) $this->mime_type);

        if ($mime === '' || str_contains($mime, 'svg') || str_contains($mime, 'html')) {
            return false;
        }

        if (in_array($mime, self::PREVIEWABLE_MIMES, true) || str_starts_with($mime, 'image/') || str_starts_with($mime, 'audio/')) {
            return true;
        }

        return false;
    }
}
