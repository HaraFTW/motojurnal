@extends('layouts.app')

@section('title', 'Remindere — ' . config('app.name'))

@push('scripts')
    @vite(['resources/js/reminders.js'])
@endpush

@section('content')
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-50">Remindere</h1>
            <p class="mt-2 text-sm leading-relaxed text-zinc-400">
                Când se apropie momentul expirării unui document de aici (20 de zile înainte), vei vedea o notificare când deschizi aplicația.
            </p>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($reminders->isNotEmpty())
            <div class="space-y-3">
                @foreach ($reminders as $reminder)
                    <article @class([
                        'rounded-2xl border bg-zinc-900 p-4',
                        'border-zinc-800' => ! $reminder->solved,
                        'border-emerald-500/30 opacity-80' => $reminder->solved,
                    ])>
                        <div class="mb-3">
                            <span class="inline-flex rounded-lg bg-amber-500/10 px-2.5 py-1 text-xs font-semibold text-amber-400">
                                {{ $reminder->displayType() }}
                            </span>
                        </div>

                        <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                            @if ($reminder->starting_date)
                                <div>
                                    <dt class="text-zinc-500">Data întocmirii</dt>
                                    <dd class="font-medium text-zinc-100">{{ $reminder->starting_date->format('d.m.Y') }}</dd>
                                </div>
                            @endif
                            @if ($reminder->ending_date)
                                <div>
                                    <dt class="text-zinc-500">Data expirării</dt>
                                    <dd class="font-medium text-zinc-100">{{ $reminder->ending_date->format('d.m.Y') }}</dd>
                                </div>
                            @endif
                        </dl>

                        @if ($reminder->observations)
                            <p class="mt-3 text-sm text-zinc-400">{{ $reminder->observations }}</p>
                        @endif

                        <div class="mt-4 flex flex-col gap-2 sm:flex-row">
                            <form method="POST" action="{{ route('reminders.toggle-solved', $reminder) }}" class="flex-1">
                                @csrf
                                @method('PATCH')
                                <button
                                    type="submit"
                                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm font-semibold text-amber-400 transition hover:border-amber-500 hover:bg-amber-500/20 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                                >
                                    {{ $reminder->solved ? 'Marchează ca nerezolvat' : 'Marchează ca rezolvat' }}
                                </button>
                            </form>

                            <button
                                type="button"
                                data-open-dialog="reminder-delete-{{ $reminder->id }}"
                                class="flex w-full flex-1 items-center justify-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-300 transition hover:border-red-500/50 hover:bg-red-500/20 focus:outline-none focus:ring-2 focus:ring-red-500/40"
                            >
                                <x-fa-icon name="trash" class="size-4" />
                                Șterge
                            </button>
                        </div>
                    </article>

                    @include('reminders.partials.delete-dialog', ['reminder' => $reminder])
                @endforeach
            </div>
        @else
            <p class="text-sm text-zinc-500">Nu ai remindere salvate.</p>
        @endif

        <button
            type="button"
            id="reminder-form-toggle"
            class="flex w-full items-center justify-center gap-2 rounded-xl border border-amber-500/40 bg-amber-500/10 px-4 py-3.5 text-base font-semibold text-amber-400 transition hover:border-amber-500 hover:bg-amber-500/20 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
        >
            <x-fa-icon name="plus" class="size-5" />
            Adaugă reminder nou
        </button>

        <div
            id="reminder-form-panel"
            @class([
                'hidden' => ! ($errors->any() || old('_reminder_form')),
            ])
            @if ($errors->any() || old('_reminder_form'))
                data-show-form="true"
            @endif
        >
            <form method="POST" action="{{ route('reminders.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_reminder_form" value="1">

                @if ($errors->any())
                    <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                        <ul class="space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-5 rounded-2xl border border-zinc-800 bg-zinc-900 p-5">
                    <div>
                        <p class="mb-3 text-sm font-medium text-zinc-300">Tip reminder</p>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach ($types as $type)
                                <button
                                    type="button"
                                    data-reminder-type-button
                                    data-value="{{ $type }}"
                                    class="reminder-option-button rounded-xl border border-zinc-800 bg-zinc-900 px-2 py-4 text-center text-sm font-semibold text-zinc-100 transition hover:border-zinc-700 hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                                >
                                    {{ $type }}
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="type" id="type" value="{{ old('type', 'RCA') }}">
                    </div>

                    <div
                        id="custom-type-field"
                        @class([
                            'hidden' => old('type', 'RCA') !== 'Altele',
                        ])
                    >
                        <label for="custom_type" class="mb-2 block text-sm font-medium text-zinc-300">
                            Tip document
                        </label>
                        <input
                            id="custom_type"
                            name="custom_type"
                            type="text"
                            value="{{ old('custom_type') }}"
                            maxlength="100"
                            class="block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30"
                        >
                    </div>

                    <div>
                        <label for="starting_date" class="mb-2 block text-sm font-medium text-zinc-300">
                            Data întocmirii documentului
                        </label>
                        <input
                            id="starting_date"
                            name="starting_date"
                            type="date"
                            value="{{ old('starting_date') }}"
                            class="block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 [color-scheme:dark]"
                        >
                    </div>

                    <div>
                        <p class="mb-3 text-sm font-medium text-zinc-300">Expiră după</p>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach (['30_zile' => '30 zile', '1_an' => '1 an', '2_ani' => '2 ani'] as $value => $label)
                                <button
                                    type="button"
                                    data-reminder-duration-button
                                    data-value="{{ $value }}"
                                    class="reminder-option-button rounded-xl border border-zinc-800 bg-zinc-900 px-2 py-4 text-center text-sm font-semibold text-zinc-100 transition hover:border-zinc-700 hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                                >
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        <input type="hidden" name="duration" id="duration" value="{{ old('duration', '1_an') }}">
                    </div>

                    <div>
                        <label for="ending_date" class="mb-2 block text-sm font-medium text-zinc-300">
                            Data expirării documentului <span class="font-normal text-zinc-500">(opțional)</span>
                        </label>
                        <input
                            id="ending_date"
                            name="ending_date"
                            type="date"
                            value="{{ old('ending_date') }}"
                            class="block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 [color-scheme:dark]"
                        >
                    </div>

                    <div>
                        <label for="observations" class="mb-2 block text-sm font-medium text-zinc-300">
                            Observații <span class="font-normal text-zinc-500">(opțional)</span>
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
