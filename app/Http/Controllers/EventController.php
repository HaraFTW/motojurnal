<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesUserOwnership;
use App\Http\Controllers\Concerns\ConvertsDistanceInput;
use App\Support\Decimal;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventController extends Controller
{
    use AuthorizesUserOwnership, ConvertsDistanceInput;

    public function index(): View
    {
        $eventTypes = EventType::query()
            ->active()
            ->orderBy('event_name')
            ->get();

        $entries = auth()->user()
            ->events()
            ->with('eventType')
            ->latest()
            ->get();

        return view('events.index', compact('eventTypes', 'entries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->convertDistanceFieldsToKm(
            $this->validatedEventData($request),
            ['kilometers'],
        );

        auth()->user()->events()->create($validated);

        return redirect()
            ->route('events.index')
            ->with('success', 'Înregistrare salvată.');
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $this->authorizeUserOwnership($event);

        $validator = Validator::make($request->all(), $this->validationRules());

        if ($validator->fails()) {
            return redirect()
                ->route('events.index')
                ->withInput()
                ->with('editing_event_id', $event->id)
                ->withErrors($validator);
        }

        $event->update($this->convertDistanceFieldsToKm(
            $this->applyDefaultEventDate($validator->validated()),
            ['kilometers'],
        ));

        return redirect()
            ->route('events.index')
            ->with('success', 'Înregistrare actualizată.');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $this->authorizeUserOwnership($event);

        $event->delete();

        return redirect()
            ->route('events.index')
            ->with('success', 'Înregistrare ștearsă.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedEventData(Request $request): array
    {
        return $this->applyDefaultEventDate($request->validate($this->validationRules()));
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function applyDefaultEventDate(array $validated): array
    {
        if (empty($validated['event_date'])) {
            $validated['event_date'] = now()->toDateString();
        }

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function validationRules(): array
    {
        return [
            'event_type_id' => [
                'required',
                Rule::exists('event_types', 'id')->where('active', true),
            ],
            'kilometers' => Decimal::validationRule(),
            'event_date' => ['nullable', 'date'],
            'observations' => ['nullable', 'string', 'max:255'],
        ];
    }
}
