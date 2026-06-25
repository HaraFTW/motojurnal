@props([
    'name',
    'field',
    'id' => null,
    'kmValue' => null,
    'required' => false,
    'optional' => false,
    'inputClass' => 'block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30',
])

@php
    $inputId = $id ?? $name;
    $displayValue = old($name);

    if ($displayValue === null && $kmValue !== null) {
        $displayValue = auth()->user()->formatDistance($kmValue);
    }
@endphp

<div {{ $attributes }}>
    <label for="{{ $inputId }}" class="mb-2 block text-sm font-medium text-zinc-300">
        <x-distance-label :field="$field" :optional="$optional" />
    </label>
    <input
        id="{{ $inputId }}"
        name="{{ $name }}"
        type="number"
        inputmode="decimal"
        step="0.1"
        min="0"
        value="{{ $displayValue }}"
        class="{{ $inputClass }}"
        @required($required)
    >
</div>
