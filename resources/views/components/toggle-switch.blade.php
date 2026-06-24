@props([
    'name',
    'label',
    'checked' => false,
])

@php
    $id = $name.'_'.uniqid();
@endphp

<div {{ $attributes->class(['group flex items-center justify-between gap-4']) }}>
    <span class="text-sm font-medium text-zinc-300">{{ $label }}</span>

    <div class="flex items-center gap-3">
        <input
            type="checkbox"
            id="{{ $id }}"
            name="{{ $name }}"
            value="1"
            class="peer sr-only"
            @checked((bool) old($name, $checked))
        >
        <span class="min-w-6 text-right text-sm font-semibold text-zinc-400 group-has-[:checked]:hidden">Nu</span>
        <label
            for="{{ $id }}"
            class="relative inline-flex h-8 w-14 shrink-0 cursor-pointer rounded-full bg-zinc-700 transition-colors group-has-[:checked]:bg-amber-500 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-500/40"
        >
            <span class="absolute left-1 top-1 h-6 w-6 rounded-full bg-zinc-200 shadow-sm transition-transform group-has-[:checked]:translate-x-6"></span>
        </label>
        <span class="min-w-6 hidden text-sm font-semibold text-amber-500 group-has-[:checked]:inline">Da</span>
    </div>
</div>
