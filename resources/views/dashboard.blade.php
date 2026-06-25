@extends('layouts.app')

@section('title', 'Acasă — ' . config('app.name'))

@section('content')
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-50">
        </h1>

        <div class="space-y-3">
            @foreach (config('navigation.items') as $item)
                <x-nav-tile
                    :href="route($item['route'])"
                    :icon="$item['icon']"
                    :label="$item['label']"
                    :active="request()->routeIs($item['route'])"
                />
            @endforeach
        </div>

        <div class="rounded-2xl border border-zinc-800 bg-zinc-900 p-5">
            <p class="mb-3 text-sm font-medium text-zinc-300">Unitate distanță</p>
            <x-distance-unit-switch class="w-full" />
        </div>
    </div>
@endsection
