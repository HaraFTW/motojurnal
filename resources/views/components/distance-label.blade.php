@props([
    'field',
    'optional' => false,
])

<span {{ $attributes }}>
    {{ auth()->user()->distanceFieldLabel($field) }}@if ($optional)<span class="font-normal text-zinc-500"> (optional)</span>@endif
</span>
