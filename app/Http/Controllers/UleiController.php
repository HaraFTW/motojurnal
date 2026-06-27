<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesUserOwnership;
use App\Http\Controllers\Concerns\ConvertsDistanceInput;
use App\Support\Decimal;
use App\Models\TipUlei;
use App\Models\Ulei;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UleiController extends Controller
{
    use AuthorizesUserOwnership, ConvertsDistanceInput;

    public function index(): View
    {
        $entries = auth()->user()
            ->ulei()
            ->with('tipUlei')
            ->latest()
            ->get();

        $oilTypes = TipUlei::query()
            ->active()
            ->orderBy('priority')
            ->get();

        return view('oil.index', compact('entries', 'oilTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->convertDistanceFieldsToKm(
            $request->validate($this->validationRules()),
            ['total_kilometers'],
        );

        auth()->user()->ulei()->create([
            ...$validated,
            'oil_filter' => $request->boolean('oil_filter'),
            'gasket' => $request->boolean('gasket'),
        ]);

        return redirect()
            ->route('oil.index')
            ->with('success', 'Înregistrare salvată.');
    }

    public function update(Request $request, Ulei $ulei): RedirectResponse
    {
        $this->authorizeUserOwnership($ulei);

        $validator = Validator::make($request->all(), $this->validationRules());

        if ($validator->fails()) {
            return redirect()
                ->route('oil.index')
                ->withInput()
                ->with('editing_oil_id', $ulei->id)
                ->withErrors($validator);
        }

        $validated = $this->convertDistanceFieldsToKm(
            $validator->validated(),
            ['total_kilometers'],
        );

        $ulei->update([
            ...$validated,
            'oil_filter' => $request->boolean('oil_filter'),
            'gasket' => $request->boolean('gasket'),
        ]);

        return redirect()
            ->route('oil.index')
            ->with('success', 'Înregistrare actualizată.');
    }

    public function destroy(Ulei $ulei): RedirectResponse
    {
        $this->authorizeUserOwnership($ulei);

        $ulei->delete();

        return redirect()
            ->route('oil.index')
            ->with('success', 'Înregistrare ștearsă.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validationRules(): array
    {
        return [
            'total_kilometers' => Decimal::validationRule(),
            'oil_filter' => ['sometimes', 'boolean'],
            'gasket' => ['sometimes', 'boolean'],
            'oil_amount' => Decimal::validationRule(required: false),
            'oil_brand' => ['nullable', 'string', 'max:255'],
            'oil_type_id' => [
                'nullable',
                Rule::exists('tipuri_ulei', 'id')->where('active', true),
            ],
            'observations' => ['nullable', 'string', 'max:255'],
        ];
    }
}
