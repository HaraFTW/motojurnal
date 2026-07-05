@extends('layouts.app')

@section('title', 'Autentificare — ' . config('app.name'))

@push('scripts')
    @vite(['resources/js/login-odometer.js'])
@endpush

@section('content')
    <dialog
        id="login-about-dialog"
        class="login-about-dialog w-[calc(100%-2rem)] max-w-md rounded-2xl border border-zinc-700 bg-zinc-900 p-0 text-zinc-100 shadow-xl"
    >
        <div class="flex items-start justify-between gap-3 border-b border-zinc-800 px-5 py-4">
            <h2 class="text-lg font-semibold text-zinc-50">De la dezvoltator</h2>
            <button
                type="button"
                class="inline-flex shrink-0 items-center justify-center rounded-lg border border-zinc-700 p-2 text-zinc-400 transition hover:border-zinc-600 hover:bg-zinc-800 hover:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                data-login-about-close
                aria-label="Închide"
            >
                <x-fa-icon name="xmark" class="size-4" />
            </button>
        </div>

        <div class="max-h-[min(70dvh,32rem)] space-y-4 overflow-y-auto px-5 py-4 text-sm leading-relaxed text-zinc-300">
            <div class="space-y-2">
                <h3 class="font-semibold text-zinc-100">Ce este MotoJurnal?</h3>
                <p>
                    Este un proiect făcut din pasiune și timp liber, ca să-mi pot salva un istoric al consumului,
                    schimburilor de ulei și a altor modificări pe care le fac la motocicleta mea. Pentru că niciodată
                    nu am știut la câți kilometri am făcut ultimul schimb de ulei sau schimb de plăcuțe etc., și când
                    mi-am notat asta undeva, am uitat unde mi-am notat.
                </p>
            </div>

            <p>
                Aplicația folosește un singur cookie, „Ține-mă minte”.
                Nu îți vinde reclame, nu face nimic cu datele tale, nu îți cere e-mail sau telefon.
                E total gratis și oricine e binevenit să o folosească.
                Așa că simte-te liber să o folosești după cum ai nevoie.
            </p>

            <p>
                Şi nu în ultimul rând,
            </p>

            <h3 class="font-semibold text-zinc-100">Asfalt uscat!</h3>
            
            <br><br>
            <!-- <p>
                În viitor voi mai face modificări și voi mai adăuga și alte funcționalități, dar momentan sunt
                mulțumit de ea așa.
            </p> -->

            <div class="space-y-3">
                <h3 class="font-semibold text-zinc-100">Cum funcționează?</h3>

                <p>
                    În pagina de combustibil
                    <span class="login-about-inline-icon" aria-hidden="true">
                        <x-fa-icon name="gas-pump" class="size-4" />
                    </span>
                    adaug numărul de kilometri (sau mile) de la ultima călătorie și câți litri de benzină am pus.
                    Restul de date sunt opționale. Eu îmi notez și prețul uneori, ca să am o idee de cât am cheltuit.
                    Apoi pe butonul
                    <span class="login-about-inline-icon" aria-hidden="true">
                        <x-fa-icon name="chart-line" class="size-4" />
                    </span>
                    văd un grafic cu ce consum am avut de-a lungul timpului.
                </p>

                <p>
                    În pagina de ulei
                    <span class="login-about-inline-icon" aria-hidden="true">
                        <x-fa-icon name="oil-can" class="size-4" />
                    </span>
                    adaug la câți kilometri (sau mile) fac schimbul de ulei, sau când completez nivelul de ulei.
                    Restul de date sunt opționale. Eu îmi notez și tipul de ulei ca să știu dacă mai folosesc sau
                    nu același ulei.
                </p>

                <p>
                    În pagina Altele
                    <span class="login-about-inline-icon" aria-hidden="true">
                        <x-fa-icon name="clipboard-list" class="size-4" />
                    </span>
                    adaug alte evenimente: plăcuțe frână, cauciucuri, filtru aer sau alte piese pe care le mai schimb
                    sau alte reglaje pe care le fac.
                </p>

                <p>
                    Pe toate cele trei pagini am pus și un buton de istoric
                    <span class="login-about-inline-icon" aria-hidden="true">
                        <x-fa-icon name="clock-rotate-left" class="size-4" />
                    </span>
                    unde pot să văd toate intrările și le pot modifica sau șterge, depinde de nevoie.
                </p>
            </div>
        </div>
    </dialog>

    <div class="mx-auto flex w-full max-w-xl flex-1 flex-col justify-center">
        <div class="w-full space-y-6">
            <div class="space-y-3 text-center">
                <img
                    src="{{ asset('app-icons/icon-source.png') }}"
                    alt="App logo"
                    class="mx-auto rounded-2xl shadow-lg shadow-black/30"
                >
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
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
                        placeholder="Număr de înmatriculare"
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
            <div class="fuel-pump-panel">
                <div class="fuel-pump-panel__housing">
                    <div class="fuel-pump-panel__gauges">
                        <div class="fuel-pump-panel__gauge">
                            <span class="fuel-pump-panel__label">Utilizatori</span>
                            <div
                                class="fuel-odometer"
                                data-count="{{ $userCount }}"
                                aria-label="Utilizatori: {{ $userCount }}"
                            ></div>
                        </div>
                        <div class="fuel-pump-panel__gauge">
                            <span class="fuel-pump-panel__label">Plinuri</span>
                            <div
                                class="fuel-odometer"
                                data-count="{{ $fuelFillCount }}"
                                aria-label="Plinuri: {{ $fuelFillCount }}"
                            ></div>
                        </div>
                        <div class="fuel-pump-panel__gauge">
                            <span class="fuel-pump-panel__label">Schimburi ulei</span>
                            <div
                                class="fuel-odometer"
                                data-count="{{ $oilChangeCount }}"
                                aria-label="Schimburi ulei: {{ $oilChangeCount }}"
                            ></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
