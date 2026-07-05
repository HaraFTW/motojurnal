@php
    $inputClass = $inputClass ?? 'block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30';
    $idPrefix = isset($entry) ? 'fuel_edit_'.$entry->id.'_' : '';

    $value = fn (string $field) => old($field, $entry?->$field);

    $hasOptionalData = filled($value('total_price'))
        || filled($value('price_per_liter'))
        || filled($value('total_kilometers'))
        || filled($value('observations'));

    $hasOptionalErrors = $errors->hasAny(['total_price', 'price_per_liter', 'total_kilometers', 'observations']);
@endphp

<details class="group rounded-xl border border-zinc-800 bg-zinc-950/60" @if ($hasOptionalData || $hasOptionalErrors) open @endif>
    <summary class="flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3.5 text-sm font-medium text-zinc-300 transition hover:text-zinc-100 [&::-webkit-details-marker]:hidden">
        <span>Detalii opționale</span>
        <x-fa-icon name="chevron-down" class="size-4 shrink-0 text-zinc-500 transition group-open:rotate-180" />
    </summary>

    <div class="space-y-4 border-t border-zinc-800 px-4 pb-4 pt-4">
        <div style="margin-block-start: calc(calc(var(--spacing) * 4) * calc(1 - var(--tw-space-y-reverse)));">
            <label for="{{ $idPrefix }}total_price" class="mb-2 block text-sm font-medium text-zinc-300">
                Pret total <span class="font-normal text-zinc-500">(optional)</span>
            </label>
            <input
                id="{{ $idPrefix }}total_price"
                name="total_price"
                type="number"
                inputmode="decimal"
                step="0.01"
                min="0"
                value="{{ $value('total_price') }}"
                class="{{ $inputClass }}"
                data-fuel-price-field="total"
            >
        </div>

        <div>
            <label for="{{ $idPrefix }}price_per_liter" class="mb-2 block text-sm font-medium text-zinc-300">
                Pret per litru <span class="font-normal text-zinc-500">(optional)</span>
            </label>
            <input
                id="{{ $idPrefix }}price_per_liter"
                name="price_per_liter"
                type="number"
                inputmode="decimal"
                step="0.01"
                min="0"
                value="{{ $value('price_per_liter') }}"
                class="{{ $inputClass }}"
                data-fuel-price-field="per-liter"
            >
        </div>

        <x-distance-input
            name="total_kilometers"
            field="total_kilometers"
            :id="$idPrefix.'total_kilometers'"
            :km-value="$entry?->total_kilometers"
            :input-class="$inputClass"
            optional
        />

        <div>
            <label for="{{ $idPrefix }}observations" class="mb-2 block text-sm font-medium text-zinc-300">
                Observatii <span class="font-normal text-zinc-500">(optional)</span>
            </label>
            <textarea
                id="{{ $idPrefix }}observations"
                name="observations"
                rows="3"
                maxlength="255"
                class="{{ $inputClass }} resize-none"
            >{{ $value('observations') }}</textarea>
        </div>
    </div>
</details>
