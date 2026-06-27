<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'total_kilometers',
    'oil_filter',
    'gasket',
    'oil_amount',
    'oil_brand',
    'oil_type_id',
    'observations',
])]
class Ulei extends Model
{
    protected $table = 'ulei';

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<TipUlei, $this>
     */
    public function tipUlei(): BelongsTo
    {
        return $this->belongsTo(TipUlei::class, 'oil_type_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_kilometers' => 'decimal:3',
            'oil_filter' => 'boolean',
            'gasket' => 'boolean',
            'oil_amount' => 'decimal:3',
        ];
    }
}
