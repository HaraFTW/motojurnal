<?php

namespace App\Models;

use App\Support\Decimal;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'kilometers',
    'liters',
    'total_price',
    'price_per_liter',
    'total_kilometers',
    'observations',
])]
class Combustibil extends Model
{
    protected $table = 'combustibil';

    protected static function booted(): void
    {
        static::saving(function (self $entry): void {
            $entry->consum = self::calculateConsum($entry->kilometers, $entry->liters);
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Liters per 100 km from the given distance and fuel amount, or null when it cannot be calculated.
     */
    public static function calculateConsum(float|string|null $kilometers, float|string|null $liters): ?float
    {
        if ($kilometers === null || $liters === null) {
            return null;
        }

        $kilometers = (float) $kilometers;

        if ($kilometers <= 0) {
            return null;
        }

        return Decimal::round(((float) $liters / $kilometers) * 100);
    }

    /**
     * Liters per 100 km for this entry, or null when it cannot be calculated.
     */
    public function fuelConsumptionPer100Km(): ?float
    {
        return self::calculateConsum($this->kilometers, $this->liters);
    }

    /**
     * @return list<array{timestamp: string, consumption: float}>
     */
    public static function consumptionChartDataForUser(User $user): array
    {
        return $user->combustibil()
            ->whereNotNull('consum')
            ->oldest()
            ->get()
            ->map(fn (self $entry): array => [
                'timestamp' => $entry->created_at->format('d.m.Y H:i'),
                'consumption' => (float) $entry->consum,
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kilometers' => 'decimal:3',
            'liters' => 'decimal:3',
            'consum' => 'decimal:3',
            'total_price' => 'decimal:3',
            'price_per_liter' => 'decimal:3',
            'total_kilometers' => 'decimal:3',
        ];
    }
}
