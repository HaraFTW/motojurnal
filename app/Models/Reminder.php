<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'type',
    'custom_type',
    'starting_date',
    'ending_date',
    'observations',
    'solved',
    'active',
])]
class Reminder extends Model
{
    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param  Builder<Reminder>  $query
     * @return Builder<Reminder>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * @param  Builder<Reminder>  $query
     * @return Builder<Reminder>
     */
    public function scopeExpiringSoon(Builder $query): Builder
    {
        return $query
            ->where('active', true)
            ->where('solved', false)
            ->whereNotNull('ending_date')
            ->whereDate('ending_date', '<=', now()->addDays(20))
            ->whereDate('ending_date', '>=', now());
    }

    /**
     * @param  Builder<Reminder>  $query
     * @return Builder<Reminder>
     */
    public function scopeRecentlyExpired(Builder $query): Builder
    {
        return $query
            ->where('active', true)
            ->where('solved', false)
            ->whereNotNull('ending_date')
            ->whereDate('ending_date', '<', now())
            ->whereDate('ending_date', '>=', now()->subDays(20));
    }

    public function displayType(): string
    {
        if ($this->type === 'Altele' && filled($this->custom_type)) {
            return $this->custom_type;
        }

        return $this->type;
    }

    public function daysUntilExpiration(): ?int
    {
        if ($this->ending_date === null) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->ending_date->startOfDay(), false);
    }

    public function expirationToastMessage(): string
    {
        $days = $this->daysUntilExpiration() ?? 0;
        $date = $this->ending_date?->format('d.m.Y') ?? '';

        $expirationPhrase = match ($days) {
            0 => "expiră azi ({$date})",
            1 => "expiră mâine ({$date})",
            default => "expiră în {$days} zile ({$date})",
        };

        return "{$this->toastSubject()} {$expirationPhrase}";
    }

    public function expiredToastMessage(): string
    {
        $daysAgo = abs($this->daysUntilExpiration() ?? 0);
        $date = $this->ending_date?->format('d.m.Y') ?? '';

        $expirationPhrase = $daysAgo === 1
            ? "a expirat ieri ({$date})"
            : "a expirat de {$daysAgo} zile ({$date})";

        return "{$this->toastSubject()} {$expirationPhrase}";
    }

    private function toastSubject(): string
    {
        return match ($this->type) {
            'RCA' => 'Asigurarea RCA',
            'ITP' => 'ITP-ul',
            'Rovinieta' => 'Rovinieta',
            'Altele' => "Documentul {$this->displayType()}",
            default => "Documentul {$this->displayType()}",
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starting_date' => 'date',
            'ending_date' => 'date',
            'solved' => 'boolean',
            'active' => 'boolean',
        ];
    }
}
