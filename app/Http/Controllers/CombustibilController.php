<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesUserOwnership;
use App\Http\Controllers\Concerns\ConvertsDistanceInput;
use App\Support\Decimal;
use App\Models\Combustibil;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class CombustibilController extends Controller
{
    use AuthorizesUserOwnership, ConvertsDistanceInput;

    public function index(): View
    {
        $entries = auth()->user()
            ->combustibil()
            ->latest()
            ->get();

        $consumptionChartData = Combustibil::consumptionChartDataForUser(auth()->user());

        return view('fuel.index', compact('entries', 'consumptionChartData'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->convertDistanceFieldsToKm(
            $request->validate($this->validationRules()),
            ['kilometers', 'total_kilometers'],
        );

        auth()->user()->combustibil()->create($validated);

        return redirect()
            ->route('fuel.index')
            ->with('success', 'Înregistrare salvată.');
    }

    public function update(Request $request, Combustibil $combustibil): RedirectResponse
    {
        $this->authorizeUserOwnership($combustibil);

        $validator = Validator::make($request->all(), $this->validationRules());

        if ($validator->fails()) {
            return redirect()
                ->route('fuel.index')
                ->withInput()
                ->with('editing_fuel_id', $combustibil->id)
                ->withErrors($validator);
        }

        $combustibil->update($this->convertDistanceFieldsToKm(
            $validator->validated(),
            ['kilometers', 'total_kilometers'],
        ));

        return redirect()
            ->route('fuel.index')
            ->with('success', 'Înregistrare actualizată.');
    }

    public function destroy(Combustibil $combustibil): RedirectResponse
    {
        $this->authorizeUserOwnership($combustibil);

        $combustibil->delete();

        return redirect()
            ->route('fuel.index')
            ->with('success', 'Înregistrare ștearsă.');
    }

    /**
     * @return array<string, list<string>>
     */
    private function validationRules(): array
    {
        return [
            'kilometers' => Decimal::validationRule(),
            'liters' => Decimal::validationRule(),
            'total_price' => Decimal::validationRule(required: false),
            'price_per_liter' => Decimal::validationRule(required: false),
            'total_kilometers' => Decimal::validationRule(required: false),
            'observations' => ['nullable', 'string', 'max:255'],
        ];
    }
}
