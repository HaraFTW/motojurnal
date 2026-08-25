<?php

namespace Database\Factories;

use App\Models\AdminFile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AdminFile>
 */
class AdminFileFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extension = 'pdf';
        $uuid = Str::uuid()->toString();

        return [
            'name' => fake()->unique()->bothify('file-####.'.$extension),
            'stored_path' => 'admin-files/'.$uuid.'.'.$extension,
            'extra' => fake()->optional()->sentence(),
            'size' => fake()->numberBetween(100, 10_000),
            'mime_type' => 'application/pdf',
        ];
    }
}
