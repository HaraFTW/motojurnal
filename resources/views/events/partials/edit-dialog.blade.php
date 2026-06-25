@php
    $inputClass = 'block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30';
@endphp

<dialog
    id="event-edit-{{ $entry->id }}"
    class="fuel-chart-dialog w-full max-w-none rounded-none border-x-0 border-zinc-800 bg-zinc-900 p-0 text-zinc-100 shadow-xl open:flex open:max-h-dvh open:flex-col"
    data-close-on-backdrop
>
    <div class="flex items-center justify-between gap-3 border-b border-zinc-800 px-5 py-4">
        <h2 class="text-lg font-semibold text-zinc-100">Editează înregistrare</h2>
        <button
            type="button"
            data-dialog-close
            class="inline-flex items-center justify-center rounded-lg p-2 text-zinc-400 transition hover:bg-zinc-800 hover:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
            aria-label="Închide"
        >
            <x-fa-icon name="xmark" class="size-5" />
        </button>
    </div>

    <form method="POST" action="{{ route('events.update', $entry) }}" class="space-y-4 overflow-y-auto px-4 py-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="event_type_id" value="{{ old('event_type_id', $entry->event_type_id) }}">

        <div class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
            @if ($entry->eventType)
                <div>
                    <p class="mb-1 text-sm font-medium text-zinc-300">Tip eveniment</p>
                    <p class="text-base text-zinc-100">{{ $entry->eventType->event_name }}</p>
                </div>
            @endif

            <div>
                <label for="event_edit_date_{{ $entry->id }}" class="mb-2 block text-sm font-medium text-zinc-300">
                    Data <span class="font-normal text-zinc-500">(optional)</span>
                </label>
                <input
                    id="event_edit_date_{{ $entry->id }}"
                    name="event_date"
                    type="date"
                    value="{{ old('event_date', $entry->event_date->format('Y-m-d')) }}"
                    class="{{ $inputClass }} [color-scheme:dark]"
                >
            </div>

            <x-distance-input
                name="kilometers"
                field="kilometers"
                :id="'event_edit_kilometers_'.$entry->id"
                :km-value="$entry->kilometers"
                :input-class="$inputClass"
                required
            />

            <div>
                <label for="event_edit_observations_{{ $entry->id }}" class="mb-2 block text-sm font-medium text-zinc-300">
                    Observatii <span class="font-normal text-zinc-500">(optional)</span>
                </label>
                <textarea
                    id="event_edit_observations_{{ $entry->id }}"
                    name="observations"
                    rows="3"
                    maxlength="255"
                    class="{{ $inputClass }} resize-none"
                >{{ old('observations', $entry->observations) }}</textarea>
            </div>
        </div>

        <button
            type="submit"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3.5 text-base font-semibold text-zinc-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 active:bg-amber-600"
        >
            <x-fa-icon name="floppy-disk" class="size-5" />
            Salvează modificările
        </button>
    </form>
</dialog>
