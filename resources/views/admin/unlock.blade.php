@extends('layouts.admin')

@section('title', 'Admin — ' . config('app.name'))

@section('content')
    <div class="mx-auto flex w-full max-w-sm flex-1 flex-col justify-center">
        <form method="POST" action="{{ route('admin.unlock.store') }}" class="space-y-4" autocomplete="off">
            @csrf

            <div>
                <label for="password" class="sr-only">Parolă</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="off"
                    placeholder="Parolă"
                    class="block w-full rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3.5 text-base text-zinc-100 placeholder:text-zinc-500 shadow-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30"
                    required
                >
            </div>

            <button
                type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3.5 text-base font-semibold text-zinc-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 active:bg-amber-600"
            >
                Intră
            </button>
        </form>
    </div>
@endsection
