<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['oil_type', 'active'])]
class TipUlei extends Model
{
    protected $table = 'tipuri_ulei';

    /**
     * @return HasMany<Ulei, $this>
     */
    public function ulei(): HasMany
    {
        return $this->hasMany(Ulei::class, 'oil_type_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
