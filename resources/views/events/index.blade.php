@extends('layouts.app')

@section('title', 'Alte evenimente — ' . config('app.name'))

@push('scripts')
    @vite(['resources/js/evenimente.js'])
@endpush

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-50">Alte evenimente</h1>

            @if ($entries->isNotEmpty())
                <button
                    type="button"
                    id="events-history-open"
                    class="inline-flex shrink-0 items-center justify-center rounded-xl border border-zinc-700 bg-zinc-900 p-2.5 text-amber-400 transition hover:border-amber-500/50 hover:bg-zinc-800 hover:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                    aria-label="Afișează istoric"
                >
                    <x-fa-icon name="clock-rotate-left" class="size-5" />
                </button>
            @endif
        </div>

        @if ($entries->isNotEmpty())
            <dialog
                id="events-history-dialog"
                class="fuel-chart-dialog w-full max-w-none rounded-none border-x-0 border-zinc-800 bg-zinc-900 p-0 text-zinc-100 shadow-xl open:flex open:max-h-dvh open:flex-col"
            >
                <div class="flex items-center justify-between gap-3 border-b border-zinc-800 px-5 py-4">
                    <h2 class="text-lg font-semibold text-zinc-100">Istoric</h2>
                    <button
                        type="button"
                        data-events-history-close
                        class="inline-flex items-center justify-center rounded-lg p-2 text-zinc-400 transition hover:bg-zinc-800 hover:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                        aria-label="Închide"
                    >
                        <x-fa-icon name="xmark" class="size-5" />
                    </button>
                </div>

                <div class="space-y-3 overflow-y-auto px-4 py-4">
                    @foreach ($entries as $entry)
                        <article class="rounded-2xl border border-zinc-800 bg-zinc-950 p-4">
                            <div class="mb-3 flex items-start justify-between gap-3">
                                <p class="text-xs text-zinc-500">
                                    {{ $entry->event_date->format('d.m.Y') }}
                                </p>
                                <x-history-actions
                                    :edit-dialog-id="'event-edit-'.$entry->id"
                                    :delete-url="route('events.destroy', $entry)"
                                />
                            </div>

                            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                @if ($entry->eventType)
                                    <div>
                                        <dt class="text-zinc-500">Tip</dt>
                                        <dd class="font-medium text-zinc-100">{{ $entry->eventType->event_name }}</dd>
                                    </div>
                                @endif
                                @if ($entry->kilometers !== null)
                                    <div>
                                        <dt class="text-zinc-500">Kilometri</dt>
                                        <dd class="font-medium text-zinc-100"><x-formatted-decimal :value="$entry->kilometers" /></dd>
                                    </div>
                                @endif
                            </dl>

                            @if ($entry->observations)
                                <p class="mt-3 text-sm text-zinc-400">{{ $entry->observations }}</p>
                            @endif
                        </article>
                    @endforeach
                </div>
            </dialog>

            @foreach ($entries as $entry)
                @include('events.partials.edit-dialog', ['entry' => $entry])
            @endforeach
        @endif

        @if (session('editing_event_id'))
            <div id="events-editing-entry" data-history-dialog="events-history-dialog" data-edit-dialog="event-edit-{{ session('editing_event_id') }}" hidden></div>
        @endif

        @if (session('success'))
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-3">
            @foreach ($eventTypes as $eventType)
                <button
                    type="button"
                    data-event-type-id="{{ $eventType->id }}"
                    data-event-type-button
                    class="event-type-button w-full rounded-2xl border border-zinc-800 bg-zinc-900 px-5 py-4 text-left text-base font-semibold text-zinc-100 transition hover:border-zinc-700 hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                >
                    {{ $eventType->event_name }}
                </button>
            @endforeach
        </div>

        <div
            id="event-form-panel"
            @class([
                'hidden' => ! old('event_type_id'),
            ])
            @if (old('event_type_id'))
                data-selected-type="{{ old('event_type_id') }}"
            @endif
        >
            <form method="POST" action="{{ route('events.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="event_type_id" id="event_type_id" value="{{ old('event_type_id') }}">

                @if ($errors->any())
                    <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900 p-5">
                    <div>
                        <label for="event_date" class="mb-2 block text-sm font-medium text-zinc-300">
                            Data <span class="font-normal text-zinc-500">(optional)</span>
                        </label>
                        <input
                            id="event_date"
                            name="event_date"
                            type="date"
                            value="{{ old('event_date') }}"
                            class="block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 [color-scheme:dark]"
                        >
                    </div>

                    <x-distance-input name="kilometers" field="kilometers" required />

                    <div>
                        <label for="observations" class="mb-2 block text-sm font-medium text-zinc-300">
                            Observatii <span class="font-normal text-zinc-500">(optional)</span>
                        </label>
                        <textarea
                            id="observations"
                            name="observations"
                            rows="3"
                            maxlength="255"
                            class="block w-full resize-none rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30"
                        >{{ old('observations') }}</textarea>
                    </div>
                </div>

                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3.5 text-base font-semibold text-zinc-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 active:bg-amber-600"
                >
                    <x-fa-icon name="floppy-disk" class="size-5" />
                    Salvează
                </button>
            </form>
        </div>
    </div>
@endsection
