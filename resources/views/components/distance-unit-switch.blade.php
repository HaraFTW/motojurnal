@php
    $unit = auth()->user()->distance_unit ?? \App\Enums\DistanceUnit::Km;
    $buttonClass = 'flex-1 rounded-lg px-2.5 py-2.5 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-amber-500/40';
    $activeClass = 'bg-amber-500 text-zinc-950';
    $inactiveClass = 'text-zinc-400 hover:text-zinc-200';
@endphp

<form method="POST" action="{{ route('distance-unit.update') }}" {{ $attributes->class(['flex w-full rounded-xl border border-zinc-700 bg-zinc-950 p-0.5']) }}>
    @csrf
    @method('PATCH')

    <button
        type="submit"
        name="distance_unit"
        value="km"
        @class([
            $buttonClass,
            $activeClass => $unit === \App\Enums\DistanceUnit::Km,
            $inactiveClass => $unit !== \App\Enums\DistanceUnit::Km,
        ])
        aria-pressed="{{ $unit === \App\Enums\DistanceUnit::Km ? 'true' : 'false' }}"
    >
        Kilometri
    </button>

    <button
        type="submit"
        name="distance_unit"
        value="mi"
        @class([
            $buttonClass,
            $activeClass => $unit === \App\Enums\DistanceUnit::Miles,
            $inactiveClass => $unit !== \App\Enums\DistanceUnit::Miles,
        ])
        aria-pressed="{{ $unit === \App\Enums\DistanceUnit::Miles ? 'true' : 'false' }}"
    >
        Mile
    </button>
</form>
