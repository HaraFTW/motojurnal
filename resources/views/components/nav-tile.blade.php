@props([
    'href',
    'icon',
    'label',
    'active' => false,
])

<a
    href="{{ $href }}"
    {{ $attributes->class([
        'flex min-h-[5.5rem] items-center gap-5 rounded-2xl border px-5 py-5 transition active:scale-[0.98]',
        'border-amber-500/40 bg-amber-500/10 text-zinc-50' => $active,
        'border-zinc-800 bg-zinc-900 text-zinc-100 hover:border-zinc-700 hover:bg-zinc-800/80' => ! $active,
    ]) }}
    @if ($active) aria-current="page" @endif
>
    <span @class([
        'flex size-14 shrink-0 items-center justify-center rounded-xl',
        'bg-amber-500/20 text-amber-500' => $active,
        'bg-zinc-800 text-amber-500' => ! $active,
    ])>
        <x-fa-icon :name="$icon" class="size-7" />
    </span>
    <span class="text-lg font-semibold leading-snug">{{ $label }}</span>
</a>
