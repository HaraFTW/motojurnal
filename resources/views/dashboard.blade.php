@extends('layouts.app')

@section('title', 'Acasă — ' . config('app.name'))

@section('content')
    <div class="space-y-4">
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-50">
            Acasă
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
    </div>
@endsection
