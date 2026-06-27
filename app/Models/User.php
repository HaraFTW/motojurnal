<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\DistanceUnit;
use App\Support\Decimal;
use App\Support\Distance;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['plate_number', 'distance_unit', 'name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /** @var array<string, string> */
    private const ROMANIAN_DIACRITIC_MAP = [
        'Ă' => 'A', 'ă' => 'a',
        'Â' => 'A', 'â' => 'a',
        'Î' => 'I', 'î' => 'i',
        'Ș' => 'S', 'ș' => 's',
        'Ş' => 'S', 'ş' => 's',
        'Ț' => 'T', 'ț' => 't',
        'Ţ' => 'T', 'ţ' => 't',
    ];

    public static function normalizePlateNumber(string $value): string
    {
        $value = strtr($value, self::ROMANIAN_DIACRITIC_MAP);

        return strtoupper(preg_replace('/[\s\-]+/', '', $value) ?? '');
    }

    /**
     * @return Attribute<string, string>
     */
    protected function plateNumber(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => self::normalizePlateNumber($value),
        );
    }

    /**
     * @return HasMany<Combustibil, $this>
     */
    public function combustibil(): HasMany
    {
        return $this->hasMany(Combustibil::class);
    }

    /**
     * @return HasMany<Ulei, $this>
     */
    public function ulei(): HasMany
    {
        return $this->hasMany(Ulei::class);
    }

    /**
     * @return HasMany<Event, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function usesMiles(): bool
    {
        return ($this->distance_unit ?? DistanceUnit::Km) === DistanceUnit::Miles;
    }

    public function distanceFieldLabel(string $field): string
    {
        return match ($field) {
            'kilometers' => $this->usesMiles() ? 'Mile' : 'Kilometri',
            'total_kilometers' => $this->usesMiles() ? 'Total mile' : 'Total kilometri',
            default => throw new \InvalidArgumentException("Unknown distance field [{$field}]."),
        };
    }

    public function kmToDisplay(?float $kilometers): ?float
    {
        if ($kilometers === null) {
            return null;
        }

        if ($this->usesMiles()) {
            return Decimal::round($kilometers / Distance::KM_PER_MILE);
        }

        return Decimal::round($kilometers);
    }

    public function displayToKm(float $value): float
    {
        if ($this->usesMiles()) {
            return Decimal::round($value * Distance::KM_PER_MILE);
        }

        return Decimal::round($value);
    }

    public function formatDistance(?float $kilometers): string
    {
        if ($kilometers === null) {
            return '';
        }

        return Decimal::format($this->kmToDisplay($kilometers));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'distance_unit' => DistanceUnit::class,
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
