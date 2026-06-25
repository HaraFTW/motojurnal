@php
    $inputClass = 'block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30';
@endphp

<dialog
    id="oil-edit-{{ $entry->id }}"
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

    <form method="POST" action="{{ route('oil.update', $entry) }}" class="space-y-4 overflow-y-auto px-4 py-4">
        @csrf
        @method('PUT')

        <div class="space-y-5 rounded-2xl border border-zinc-800 bg-zinc-950 p-5">
            <x-distance-input
                name="total_kilometers"
                field="total_kilometers"
                :id="'oil_edit_total_kilometers_'.$entry->id"
                :km-value="$entry->total_kilometers"
                :input-class="$inputClass"
                required
            />

            <x-toggle-switch name="oil_filter" label="Filtru schimbat" :checked="$entry->oil_filter" />
            <x-toggle-switch name="gasket" label="Garnituri schimbate" :checked="$entry->gasket" />

            <div>
                <label for="oil_edit_amount_{{ $entry->id }}" class="mb-2 block text-sm font-medium text-zinc-300">
                    Cantitate ulei <span class="font-normal text-zinc-500">(optional)</span>
                </label>
                <input
                    id="oil_edit_amount_{{ $entry->id }}"
                    name="oil_amount"
                    type="number"
                    inputmode="decimal"
                    step="0.1"
                    min="0"
                    value="{{ old('oil_amount', $entry->oil_amount) }}"
                    class="{{ $inputClass }}"
                >
            </div>

            <div>
                <label for="oil_edit_brand_{{ $entry->id }}" class="mb-2 block text-sm font-medium text-zinc-300">
                    Brand ulei <span class="font-normal text-zinc-500">(optional)</span>
                </label>
                <input
                    id="oil_edit_brand_{{ $entry->id }}"
                    name="oil_brand"
                    type="text"
                    value="{{ old('oil_brand', $entry->oil_brand) }}"
                    maxlength="255"
                    class="{{ $inputClass }}"
                >
            </div>

            <div>
                <label for="oil_edit_type_{{ $entry->id }}" class="mb-2 block text-sm font-medium text-zinc-300">
                    Tip ulei <span class="font-normal text-zinc-500">(optional)</span>
                </label>
                <select
                    id="oil_edit_type_{{ $entry->id }}"
                    name="oil_type_id"
                    class="oil-type-select-edit block w-full"
                    data-entry-id="{{ $entry->id }}"
                >
                    <option value=""></option>
                    @foreach ($oilTypes as $oilType)
                        <option
                            value="{{ $oilType->id }}"
                            @selected((string) old('oil_type_id', $entry->oil_type_id) === (string) $oilType->id)
                        >
                            {{ $oilType->oil_type }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="oil_edit_observations_{{ $entry->id }}" class="mb-2 block text-sm font-medium text-zinc-300">
                    Observatii <span class="font-normal text-zinc-500">(optional)</span>
                </label>
                <textarea
                    id="oil_edit_observations_{{ $entry->id }}"
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
