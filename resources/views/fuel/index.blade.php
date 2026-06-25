@extends('layouts.app')

@section('title', 'Combustibil — ' . config('app.name'))

@if ($entries->isNotEmpty() || count($consumptionChartData) > 0)
    @push('scripts')
        @if (count($consumptionChartData) > 0)
            <script type="application/json" id="fuel-chart-data">@json($consumptionChartData)</script>
        @endif
        @vite(['resources/js/combustibil.js'])
    @endpush
@endif

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold tracking-tight text-zinc-50">Combustibil</h1>

            @if ($entries->isNotEmpty() || count($consumptionChartData) > 0)
                <div class="flex items-center gap-2">
                    @if ($entries->isNotEmpty())
                        <button
                            type="button"
                            id="fuel-history-open"
                            class="inline-flex shrink-0 items-center justify-center rounded-xl border border-zinc-700 bg-zinc-900 p-2.5 text-amber-400 transition hover:border-amber-500/50 hover:bg-zinc-800 hover:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                            aria-label="Afișează istoric"
                        >
                            <x-fa-icon name="clock-rotate-left" class="size-5" />
                        </button>
                    @endif

                    @if (count($consumptionChartData) > 0)
                        <button
                            type="button"
                            id="fuel-chart-open"
                            class="inline-flex shrink-0 items-center justify-center rounded-xl border border-zinc-700 bg-zinc-900 p-2.5 text-amber-400 transition hover:border-amber-500/50 hover:bg-zinc-800 hover:text-amber-300 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                            aria-label="Afișează grafic consum"
                        >
                            <x-fa-icon name="chart-line" class="size-5" />
                        </button>
                    @endif
                </div>
            @endif
        </div>

        @if ($entries->isNotEmpty())
            <dialog
                id="fuel-history-dialog"
                class="fuel-chart-dialog w-full max-w-none rounded-none border-x-0 border-zinc-800 bg-zinc-900 p-0 text-zinc-100 shadow-xl open:flex open:max-h-dvh open:flex-col"
            >
                <div class="flex items-center justify-between gap-3 border-b border-zinc-800 px-5 py-4">
                    <h2 class="text-lg font-semibold text-zinc-100">Istoric</h2>
                    <button
                        type="button"
                        data-fuel-history-close
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
                                    :edit-dialog-id="'fuel-edit-'.$entry->id"
                                    :delete-url="route('fuel.destroy', $entry)"
                                />
                            </div>

                            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                                @if ($entry->kilometers !== null)
                                    <div>
                                        <dt class="text-zinc-500">Kilometri</dt>
                                        <dd class="font-medium text-zinc-100">{{ number_format($entry->kilometers, 1, '.', '') }}</dd>
                                    </div>
                                @endif
                                @if ($entry->liters !== null)
                                    <div>
                                        <dt class="text-zinc-500">Litri</dt>
                                        <dd class="font-medium text-zinc-100">{{ number_format($entry->liters, 1, '.', '') }}</dd>
                                    </div>
                                @endif
                                @if ($entry->total_price !== null)
                                    <div>
                                        <dt class="text-zinc-500">Pret total</dt>
                                        <dd class="font-medium text-zinc-100">{{ number_format($entry->total_price, 1, '.', '') }}</dd>
                                    </div>
                                @endif
                                @if ($entry->price_per_liter !== null)
                                    <div>
                                        <dt class="text-zinc-500">Pret / litru</dt>
                                        <dd class="font-medium text-zinc-100">{{ number_format($entry->price_per_liter, 1, '.', '') }}</dd>
                                    </div>
                                @endif
                                @if ($entry->total_kilometers !== null)
                                    <div>
                                        <dt class="text-zinc-500">Total km</dt>
                                        <dd class="font-medium text-zinc-100">{{ number_format($entry->total_kilometers, 1, '.', '') }}</dd>
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
                @include('fuel.partials.edit-dialog', ['entry' => $entry])
            @endforeach
        @endif

        @if (session('editing_fuel_id'))
            <div id="fuel-editing-entry" data-history-dialog="fuel-history-dialog" data-edit-dialog="fuel-edit-{{ session('editing_fuel_id') }}" hidden></div>
        @endif

        @if (count($consumptionChartData) > 0)
            <dialog
                id="fuel-chart-dialog"
                class="fuel-chart-dialog w-full max-w-none rounded-none border-x-0 border-zinc-800 bg-zinc-900 p-0 text-zinc-100 shadow-xl open:flex open:max-h-dvh open:flex-col"
            >
                <div class="flex items-center justify-between gap-3 border-b border-zinc-800 px-5 py-4">
                    <h2 class="text-lg font-semibold text-zinc-100">Consum combustibil</h2>
                    <button
                        type="button"
                        data-fuel-chart-close
                        class="inline-flex items-center justify-center rounded-lg p-2 text-zinc-400 transition hover:bg-zinc-800 hover:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                        aria-label="Închide"
                    >
                        <x-fa-icon name="xmark" class="size-5" />
                    </button>
                </div>

                <div id="fuel-chart-scroll" class="overflow-x-auto overscroll-x-contain px-4 py-4">
                    <div id="fuel-chart-wrapper" class="h-[280px] min-w-full">
                        <canvas id="fuel-chart-canvas"></canvas>
                    </div>
                </div>
            </dialog>
        @endif

        @if (session('success'))
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('fuel.store') }}" class="space-y-4">
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

            <div class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900 p-5">
                <x-distance-input name="kilometers" field="kilometers" required />

                <div>
                    <label for="liters" class="mb-2 block text-sm font-medium text-zinc-300">Litri combustibil</label>
                    <input
                        id="liters"
                        name="liters"
                        type="number"
                        inputmode="decimal"
                        step="0.1"
                        min="0"
                        value="{{ old('liters') }}"
                        class="block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30"
                        required
                    >
                </div>

                <div>
                    <label for="total_price" class="mb-2 block text-sm font-medium text-zinc-300">
                        Pret total <span class="font-normal text-zinc-500">(optional)</span>
                    </label>
                    <input
                        id="total_price"
                        name="total_price"
                        type="number"
                        inputmode="decimal"
                        step="0.1"
                        min="0"
                        value="{{ old('total_price') }}"
                        class="block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30"
                    >
                </div>

                <div>
                    <label for="price_per_liter" class="mb-2 block text-sm font-medium text-zinc-300">
                        Pret per litru <span class="font-normal text-zinc-500">(optional)</span>
                    </label>
                    <input
                        id="price_per_liter"
                        name="price_per_liter"
                        type="number"
                        inputmode="decimal"
                        step="0.1"
                        min="0"
                        value="{{ old('price_per_liter') }}"
                        class="block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30"
                    >
                </div>

                <x-distance-input name="total_kilometers" field="total_kilometers" optional />

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
