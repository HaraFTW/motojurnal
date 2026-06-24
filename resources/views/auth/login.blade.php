@extends('layouts.app')

@section('title', 'Autentificare — ' . config('app.name'))

@section('content')
    <div class="mx-auto flex w-full max-w-lg flex-1 flex-col justify-center">
        <div class="w-full space-y-6">
            <div class="space-y-2 text-center">
                <h1 class="text-2xl font-semibold tracking-tight text-zinc-50">
                    {{ config('app.name') }}
                </h1>
                <p class="text-sm text-zinc-400">
                </p>
            </div>

            <form method="POST" action="/login" class="space-y-4">
                @csrf

                @if ($errors->any())
                    <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                        {{ $errors->first() }}
                    </div>
                @endif

                <div>
                    <label for="plate_number" class="sr-only">Număr de înmatriculare</label>
                    <input
                        id="plate_number"
                        name="plate_number"
                        type="text"
                        inputmode="text"
                        autocomplete="off"
                        autocapitalize="characters"
                        maxlength="30"
                        placeholder="Numar de inmatriculare"
                        value="{{ old('plate_number') }}"
                        class="block w-full rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3.5 text-base text-zinc-100 placeholder:text-zinc-500 shadow-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30"
                        required
                    >
                </div>

                <label class="flex cursor-pointer items-center gap-3 text-sm text-zinc-400">
                    <input
                        type="checkbox"
                        name="remember"
                        value="1"
                        @checked(old('remember'))
                        class="size-4 rounded border-zinc-600 bg-zinc-900 text-amber-500 focus:ring-amber-500/30"
                    >
                    Ține-mă minte
                </label>

                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3.5 text-base font-semibold text-zinc-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 active:bg-amber-600"
                >
                    <x-fa-icon name="right-to-bracket" class="size-5" />
                    Intră
                </button>
            </form>
        </div>
    </div>
@endsection
