@props([
    'editDialogId',
    'deleteUrl',
])

<div class="flex shrink-0 items-center gap-1">
    <button
        type="button"
        data-open-dialog="{{ $editDialogId }}"
        class="inline-flex items-center justify-center rounded-lg p-2 text-zinc-400 transition hover:bg-zinc-800 hover:text-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
        aria-label="Editează"
    >
        <x-fa-icon name="pen" class="size-4" />
    </button>

    <form
        method="POST"
        action="{{ $deleteUrl }}"
        onsubmit="return confirm('Sigur vrei să ștergi această înregistrare?')"
    >
        @csrf
        @method('DELETE')
        <button
            type="submit"
            class="inline-flex items-center justify-center rounded-lg p-2 text-zinc-400 transition hover:bg-zinc-800 hover:text-red-400 focus:outline-none focus:ring-2 focus:ring-red-500/40"
            aria-label="Șterge"
        >
            <x-fa-icon name="trash" class="size-4" />
        </button>
    </form>
</div>
