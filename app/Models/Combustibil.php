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

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Liters per 100 km, or null when it cannot be calculated.
     */
    public function fuelConsumptionPer100Km(): ?float
    {
        if ($this->kilometers === null || $this->liters === null) {
            return null;
        }

        $kilometers = (float) $this->kilometers;

        if ($kilometers <= 0) {
            return null;
        }

        return Decimal::round(((float) $this->liters / $kilometers) * 100);
    }

    /**
     * @return list<array{timestamp: string, consumption: float}>
     */
    public static function consumptionChartDataForUser(User $user): array
    {
        return $user->combustibil()
            ->whereNotNull('kilometers')
            ->whereNotNull('liters')
            ->where('kilometers', '>', 0)
            ->oldest()
            ->get()
            ->map(function (self $entry): ?array {
                $consumption = $entry->fuelConsumptionPer100Km();

                if ($consumption === null) {
                    return null;
                }

                return [
                    'timestamp' => $entry->created_at->format('d.m.Y H:i'),
                    'consumption' => $consumption,
                ];
            })
            ->filter()
            ->values()
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
            'total_price' => 'decimal:3',
            'price_per_liter' => 'decimal:3',
            'total_kilometers' => 'decimal:3',
        ];
    }
}
