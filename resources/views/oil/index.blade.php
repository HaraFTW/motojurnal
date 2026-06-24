@extends('layouts.app')

@section('title', 'Ulei — ' . config('app.name'))

@push('scripts')
    @vite(['resources/js/ulei.js'])
@endpush

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-50">Schimb/Completare ulei</h1>

            @if ($entries->isNotEmpty())
                <button
                    type="button"
                    id="oil-history-open"
                    class="inline-flex shrink-0 items-center justify-center rounded-xl border border-zinc-700 bg-zinc-900 p-2.5 text-amber-400 transition hover:border-amber-500/50 hover:bg-zinc-800 hover:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                    aria-label="Afișează istoric"
                >
                    <x-fa-icon name="clock-rotate-left" class="size-5" />
                </button>
            @endif
        </div>

        @if ($entries->isNotEmpty())
            <dialog
                id="oil-history-dialog"
                class="fuel-chart-dialog w-full max-w-none rounded-none border-x-0 border-zinc-800 bg-zinc-900 p-0 text-zinc-100 shadow-xl open:flex open:max-h-dvh open:flex-col"
            >
                <div class="flex items-center justify-between gap-3 border-b border-zinc-800 px-5 py-4">
                    <h2 class="text-lg font-semibold text-zinc-100">Istoric</h2>
                    <button
                        type="button"
                        data-oil-history-close
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
                                    {{ $entry->created_at->format('d.m.Y H:i') }}
                                </p>
                                <x-history-actions
                                    :edit-dialog-id="'oil-edit-'.$entry->id"
                                    :delete-url="route('oil.destroy', $entry)"
                                />
                            </div>

                            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                @if ($entry->total_kilometers !== null)
                                    <div>
                                        <dt class="text-zinc-500">Total km</dt>
                                        <dd class="font-medium text-zinc-100">{{ number_format($entry->total_kilometers, 1, '.', '') }}</dd>
                                    </div>
                                @endif
                                <div>
                                    <dt class="text-zinc-500">Filtru</dt>
                                    <dd class="font-medium text-zinc-100">{{ $entry->oil_filter ? 'Da' : 'Nu' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-zinc-500">Garnituri</dt>
                                    <dd class="font-medium text-zinc-100">{{ $entry->gasket ? 'Da' : 'Nu' }}</dd>
                                </div>
                                @if ($entry->oil_amount !== null)
                                    <div>
                                        <dt class="text-zinc-500">Cantitate</dt>
                                        <dd class="font-medium text-zinc-100">{{ number_format($entry->oil_amount, 1, '.', '') }} L</dd>
                                    </div>
                                @endif
                                @if ($entry->oil_brand)
                                    <div>
                                        <dt class="text-zinc-500">Brand</dt>
                                        <dd class="font-medium text-zinc-100">{{ $entry->oil_brand }}</dd>
                                    </div>
                                @endif
                                @if ($entry->tipUlei)
                                    <div>
                                        <dt class="text-zinc-500">Tip ulei</dt>
                                        <dd class="font-medium text-zinc-100">{{ $entry->tipUlei->oil_type }}</dd>
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
                @include('oil.partials.edit-dialog', ['entry' => $entry, 'oilTypes' => $oilTypes])
            @endforeach
        @endif

        @if (session('editing_oil_id'))
            <div id="oil-editing-entry" data-history-dialog="oil-history-dialog" data-edit-dialog="oil-edit-{{ session('editing_oil_id') }}" hidden></div>
        @endif

        @if (session('success'))
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('oil.store') }}" class="space-y-4">
            @csrf

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
                    <label for="total_kilometers" class="mb-2 block text-sm font-medium text-zinc-300">Total kilometri</label>
                    <input
                        id="total_kilometers"
                        name="total_kilometers"
                        type="number"
                        inputmode="decimal"
                        step="0.1"
                        min="0"
                        value="{{ old('total_kilometers') }}"
                        class="block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30"
                        required
                    >
                </div>

                <x-toggle-switch name="oil_filter" label="Filtru schimbat" />
                <x-toggle-switch name="gasket" label="Garnituri schimbate" />

                <div>
                    <label for="oil_amount" class="mb-2 block text-sm font-medium text-zinc-300">
                        Cantitate ulei <span class="font-normal text-zinc-500">(optional)</span>
                    </label>
                    <input
                        id="oil_amount"
                        name="oil_amount"
                        type="number"
                        inputmode="decimal"
                        step="0.1"
                        min="0"
                        value="{{ old('oil_amount') }}"
                        class="block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30"
                    >
                </div>

                <div>
                    <label for="oil_brand" class="mb-2 block text-sm font-medium text-zinc-300">
                        Brand ulei <span class="font-normal text-zinc-500">(optional)</span>
                    </label>
                    <input
                        id="oil_brand"
                        name="oil_brand"
                        type="text"
                        value="{{ old('oil_brand') }}"
                        maxlength="255"
                        class="block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30"
                    >
                </div>

                <div>
                    <label for="oil_type_id" class="mb-2 block text-sm font-medium text-zinc-300">
                        Tip ulei <span class="font-normal text-zinc-500">(optional)</span>
                    </label>
                    <select
                        id="oil_type_id"
                        name="oil_type_id"
                        class="block w-full"
                    >
                        <option value=""></option>
                        @foreach ($oilTypes as $oilType)
                            <option value="{{ $oilType->id }}" @selected((string) old('oil_type_id') === (string) $oilType->id)>
                                {{ $oilType->oil_type }}
                            </option>
                        @endforeach
                    </select>
                </div>

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
@endsection
