<dialog
    id="reminder-delete-{{ $reminder->id }}"
    class="login-about-dialog w-[calc(100%-2rem)] max-w-md rounded-2xl border border-zinc-700 bg-zinc-900 p-0 text-zinc-100 shadow-xl"
    data-close-on-backdrop
>
    <div class="border-b border-zinc-800 px-5 py-4">
        <h2 class="text-lg font-semibold text-zinc-100">Șterge reminder</h2>
    </div>

    <div class="px-5 py-4">
        <p class="text-sm leading-relaxed text-zinc-300">
            Ești sigur că vrei să ștergi acest reminder?
        </p>

        <div class="mt-5 flex flex-col gap-2 sm:flex-row">
            <button
                type="button"
                data-dialog-close
                class="flex flex-1 items-center justify-center rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm font-semibold text-zinc-100 transition hover:border-zinc-600 hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
            >
                Anulează
            </button>

            <form method="POST" action="{{ route('reminders.destroy', $reminder) }}" class="flex-1">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-300 transition hover:border-red-500/50 hover:bg-red-500/20 focus:outline-none focus:ring-2 focus:ring-red-500/40"
                >
                    <x-fa-icon name="trash" class="size-4" />
                    Șterge
                </button>
            </form>
        </div>
    </div>
</dialog>
