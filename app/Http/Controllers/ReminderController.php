<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesUserOwnership;
use App\Models\Reminder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReminderController extends Controller
{
    use AuthorizesUserOwnership;

    /** @var list<string> */
    public const TYPES = ['RCA', 'ITP', 'Rovinieta', 'Altele'];

    public function index(): View
    {
        $reminders = auth()->user()
            ->reminders()
            ->active()
            ->latest()
            ->get();

        return view('reminders.index', [
            'reminders' => $reminders,
            'types' => self::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatedReminderData($request);

        auth()->user()->reminders()->create(
            collect($validated)->only(['type', 'custom_type', 'starting_date', 'ending_date', 'observations'])->all()
        );

        return redirect()
            ->route('reminders.index')
            ->with('success', 'Reminder salvat.');
    }

    public function toggleSolved(Reminder $reminder): RedirectResponse
    {
        $this->authorizeUserOwnership($reminder);

        $reminder->update([
            'solved' => ! $reminder->solved,
        ]);

        return redirect()
            ->route('reminders.index')
            ->with('success', $reminder->solved
                ? 'Reminder marcat ca rezolvat.'
                : 'Reminder marcat ca nerezolvat.');
    }

    public function destroy(Reminder $reminder): RedirectResponse
    {
        $this->authorizeUserOwnership($reminder);

        $reminder->update(['active' => false]);

        return redirect()
            ->route('reminders.index')
            ->with('success', 'Reminder șters.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validatedReminderData(Request $request): array
    {
        $validated = $request->validate($this->validationRules($request));

        if (
            filled($validated['starting_date'] ?? null)
            && filled($validated['ending_date'] ?? null)
            && $validated['ending_date'] < $validated['starting_date']
        ) {
            throw ValidationException::withMessages([
                'ending_date' => 'Data expirării trebuie să fie după data întocmirii.',
            ]);
        }

        return $validated;
    }

    /**
     * @return array<string, mixed>
     */
    private function validationRules(Request $request): array
    {
        return [
            'type' => ['required', Rule::in(self::TYPES)],
            'custom_type' => [
                Rule::requiredIf($request->input('type') === 'Altele'),
                'nullable',
                'string',
                'max:100',
                Rule::prohibitedIf($request->input('type') !== 'Altele'),
            ],
            'starting_date' => ['nullable', 'date'],
            'ending_date' => ['nullable', 'date'],
            'observations' => ['nullable', 'string', 'max:255'],
        ];
    }
}
