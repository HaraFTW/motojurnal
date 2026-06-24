<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;

trait AuthorizesUserOwnership
{
    protected function authorizeUserOwnership(Model $model): void
    {
        abort_unless($model->user_id === auth()->id(), 403);
    }
}
