<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'kilometers',
    'event_type_id',
    'event_date',
    'observations',
])]
class Event extends Model
{
    protected static function booted(): void
    {
        static::created(function (self $entry): void {
            if ($entry->kilometers === null || $entry->user === null) {
                return;
            }

            $entry->user->updateKilometersFromOdometer((float) $entry->kilometers);
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
     * @return BelongsTo<EventType, $this>
     */
    public function eventType(): BelongsTo
    {
        return $this->belongsTo(EventType::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kilometers' => 'decimal:3',
            'event_date' => 'date',
        ];
    }
}
