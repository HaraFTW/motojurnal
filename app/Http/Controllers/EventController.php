<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesUserOwnership;
use App\Models\Event;
use App\Models\EventType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EventController extends Controller
{
    use AuthorizesUserOwnership;

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
        $validated = $request->validate($this->validationRules());

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

        $event->update($validator->validated());

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
    private function validationRules(): array
    {
        return [
            'event_type_id' => [
                'required',
                Rule::exists('event_types', 'id')->where('active', true),
            ],
            'kilometers' => ['required', 'decimal:0,1', 'min:0'],
            'observations' => ['nullable', 'string', 'max:255'],
        ];
    }
}
