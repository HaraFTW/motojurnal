@extends('layouts.admin')

@section('title', 'Fișiere — Admin — ' . config('app.name'))

@php
    $inputClass = 'block w-full rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3.5 text-base text-zinc-100 outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30';
    $reopenDialog = session('editing_file_id') ? 'admin-file-'.session('editing_file_id') : '';
@endphp

@push('scripts')
    @vite(['resources/js/admin-files.js'])
@endpush

@section('content')
    <div
        id="admin-files-page"
        class="space-y-6"
        @if ($reopenDialog !== '')
            data-reopen-dialog="{{ $reopenDialog }}"
        @endif
    >
        <h1 class="text-2xl font-semibold tracking-tight text-zinc-50">Fișiere</h1>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->has('file'))
            <div class="rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
                {{ $errors->first('file') }}
            </div>
        @endif

        <form
            method="POST"
            action="{{ route('admin.files.store') }}"
            enctype="multipart/form-data"
            class="space-y-4 rounded-2xl border border-zinc-800 bg-zinc-900 p-5"
        >
            @csrf

            <div>
                <label for="file" class="mb-2 block text-sm font-medium text-zinc-300">Fișier</label>
                <input
                    id="file"
                    name="file"
                    type="file"
                    required
                    class="block w-full text-sm text-zinc-300 file:mr-3 file:rounded-lg file:border-0 file:bg-amber-500 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-zinc-950 hover:file:bg-amber-400"
                >
                <p class="mt-2 text-xs text-zinc-500">Limita serverului: {{ $uploadLimit }}</p>
            </div>

            <div>
                <label for="extra" class="mb-2 block text-sm font-medium text-zinc-300">
                    Extra <span class="font-normal text-zinc-500">(opțional)</span>
                </label>
                <textarea
                    id="extra"
                    name="extra"
                    rows="3"
                    class="{{ $inputClass }} resize-y"
                >{{ old('extra') }}</textarea>
            </div>

            <button
                type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3.5 text-base font-semibold text-zinc-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 active:bg-amber-600"
            >
                <x-fa-icon name="upload" class="size-5" />
                Încarcă
            </button>
        </form>

        <form method="GET" action="{{ route('admin.files.index') }}" class="space-y-3">
            <input type="hidden" name="sort" value="{{ $sort }}">

            <label for="q" class="sr-only">Caută</label>
            <div class="flex gap-2">
                <input
                    id="q"
                    name="q"
                    type="search"
                    value="{{ $search }}"
                    placeholder="Caută după nume, dată sau extra"
                    class="{{ $inputClass }}"
                >
                <button
                    type="submit"
                    class="shrink-0 rounded-xl border border-zinc-700 bg-zinc-900 px-4 py-3.5 text-sm font-semibold text-zinc-100 transition hover:border-zinc-600 hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                >
                    Caută
                </button>
            </div>

            <div class="flex gap-2">
                <a
                    href="{{ route('admin.files.index', array_filter(['q' => $search !== '' ? $search : null, 'sort' => 'date'])) }}"
                    @class([
                        'flex flex-1 items-center justify-center rounded-xl border px-3 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-amber-500/40',
                        'border-amber-500 bg-zinc-800 ring-2 ring-amber-500/30 text-zinc-100' => $sort === 'date',
                        'border-zinc-800 bg-zinc-900 text-zinc-300 hover:border-zinc-700' => $sort !== 'date',
                    ])
                >
                    Cronologic
                </a>
                <a
                    href="{{ route('admin.files.index', array_filter(['q' => $search !== '' ? $search : null, 'sort' => 'name'])) }}"
                    @class([
                        'flex flex-1 items-center justify-center rounded-xl border px-3 py-2.5 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-amber-500/40',
                        'border-amber-500 bg-zinc-800 ring-2 ring-amber-500/30 text-zinc-100' => $sort === 'name',
                        'border-zinc-800 bg-zinc-900 text-zinc-300 hover:border-zinc-700' => $sort !== 'name',
                    ])
                >
                    Alfabetic
                </a>
            </div>
        </form>

        @if ($files->isEmpty())
            <p class="text-sm text-zinc-500">
                {{ $search !== '' ? 'Niciun rezultat.' : 'Nu există fișiere.' }}
            </p>
        @else
            <div class="space-y-3">
                @foreach ($files as $file)
                    <button
                        type="button"
                        data-open-dialog="admin-file-{{ $file->id }}"
                        class="w-full rounded-2xl border border-zinc-800 bg-zinc-950 p-4 text-left transition hover:border-zinc-700 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <p class="min-w-0 break-all font-medium text-zinc-100">{{ $file->name }}</p>
                            <p class="shrink-0 text-xs text-zinc-500">
                                {{ $file->created_at->format('d.m.Y H:i') }}
                            </p>
                        </div>

                        @if (filled($file->extra))
                            <p class="mt-2 truncate text-sm text-zinc-400">{{ $file->extra }}</p>
                        @endif
                    </button>

                    @include('admin.files.partials.file-dialog', ['file' => $file, 'inputClass' => $inputClass])
                @endforeach
            </div>

            @if ($files->hasPages())
                <nav class="flex items-center justify-between gap-3 pt-2 text-sm">
                    @if ($files->onFirstPage())
                        <span class="rounded-xl border border-zinc-800 px-3 py-2 text-zinc-600">Înapoi</span>
                    @else
                        <a
                            href="{{ $files->previousPageUrl() }}"
                            class="rounded-xl border border-zinc-700 px-3 py-2 text-zinc-200 transition hover:border-zinc-600 hover:bg-zinc-900"
                        >
                            Înapoi
                        </a>
                    @endif

                    <span class="text-zinc-400">
                        Pagina {{ $files->currentPage() }} din {{ $files->lastPage() }}
                    </span>

                    @if ($files->hasMorePages())
                        <a
                            href="{{ $files->nextPageUrl() }}"
                            class="rounded-xl border border-zinc-700 px-3 py-2 text-zinc-200 transition hover:border-zinc-600 hover:bg-zinc-900"
                        >
                            Înainte
                        </a>
                    @else
                        <span class="rounded-xl border border-zinc-800 px-3 py-2 text-zinc-600">Înainte</span>
                    @endif
                </nav>
            @endif
        @endif
    </div>
@endsection
