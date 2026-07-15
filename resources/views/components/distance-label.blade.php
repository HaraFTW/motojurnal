@props([
    'field',
    'optional' => false,
    'suffix' => null,
])

<span {{ $attributes }}>
    {{ auth()->user()->distanceFieldLabel($field) }}@if ($suffix) {{ $suffix }}@endif
    @if ($optional)<span class="font-normal text-zinc-500"> (opțional)</span>@endif
</span>
