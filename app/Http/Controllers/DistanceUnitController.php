<?php

namespace App\Http\Controllers;

use App\Enums\DistanceUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DistanceUnitController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'distance_unit' => ['required', Rule::enum(DistanceUnit::class)],
        ]);

        $request->user()->update([
            'distance_unit' => $validated['distance_unit'],
        ]);

        return back();
    }
}
