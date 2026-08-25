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

    protected static function booted(): void
    {
        static::deleting(function (self $file): void {
            if ($file->stored_path !== '') {
                Storage::disk('local')->delete($file->stored_path);
            }
        });
    }
}
