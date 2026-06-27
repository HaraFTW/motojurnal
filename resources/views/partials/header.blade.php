<header class="sticky top-0 z-40 shrink-0 border-b border-zinc-800/80 bg-zinc-950/95 backdrop-blur">
    <div class="mx-auto flex w-full max-w-lg items-center justify-between gap-3 px-4 py-4 sm:px-6">
        @auth
            <div class="min-w-0">
                <a
                    href="{{ route('dashboard') }}"
                    class="block truncate font-mono text-lg font-semibold tracking-wider text-zinc-50 transition hover:text-amber-400"
                >
                    {{ auth()->user()->plate_number }}
                </a>
            </div>

            <div class="flex shrink-0 items-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 rounded-xl border border-zinc-700 px-3 py-2 text-sm font-medium text-zinc-300 transition hover:border-zinc-600 hover:bg-zinc-900 hover:text-zinc-100 active:bg-zinc-800"
                    >
                        <x-fa-icon name="right-from-bracket" class="size-4" />
                        <span>Ieșire</span>
                    </button>
                </form>
            </div>
        @endauth
    </div>
</header>
