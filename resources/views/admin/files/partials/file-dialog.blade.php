<dialog
    id="admin-file-{{ $file->id }}"
    class="login-about-dialog w-[calc(100%-2rem)] max-w-md rounded-2xl border border-zinc-700 bg-zinc-900 p-0 text-zinc-100 shadow-xl"
    data-close-on-backdrop
>
    <div class="flex items-start justify-between gap-3 border-b border-zinc-800 px-5 py-4">
        <h2 class="min-w-0 break-all text-lg font-semibold text-zinc-100">{{ $file->name }}</h2>
        <button
            type="button"
            data-dialog-close
            class="inline-flex shrink-0 items-center justify-center rounded-lg p-2 text-zinc-400 transition hover:bg-zinc-800 hover:text-zinc-100 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
            aria-label="Închide"
        >
            <x-fa-icon name="xmark" class="size-5" />
        </button>
    </div>

    <div class="max-h-[min(70dvh,36rem)] space-y-4 overflow-y-auto px-5 py-4">
        <p class="text-xs text-zinc-500">{{ $file->created_at->format('d.m.Y H:i') }}</p>

        <a
            href="{{ route('admin.files.download', $file) }}"
            class="flex w-full items-center justify-center gap-2 rounded-xl border border-zinc-700 bg-zinc-950 px-4 py-3 text-sm font-semibold text-zinc-100 transition hover:border-zinc-600 hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
        >
            <x-fa-icon name="download" class="size-4" />
            Descarcă
        </a>

        <form method="POST" action="{{ route('admin.files.update', $file) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label for="file_name_{{ $file->id }}" class="mb-2 block text-sm font-medium text-zinc-300">Nume</label>
                <input
                    id="file_name_{{ $file->id }}"
                    name="name"
                    type="text"
                    maxlength="255"
                    required
                    value="{{ (string) session('editing_file_id') === (string) $file->id ? old('name', $file->name) : $file->name }}"
                    class="{{ $inputClass }}"
                >
                @if (session('editing_file_id') == $file->id && $errors->has('name'))
                    <p class="mt-2 text-sm text-red-300">{{ $errors->first('name') }}</p>
                @endif
            </div>

            <div>
                <label for="file_extra_{{ $file->id }}" class="mb-2 block text-sm font-medium text-zinc-300">
                    Extra <span class="font-normal text-zinc-500">(opțional)</span>
                </label>
                <textarea
                    id="file_extra_{{ $file->id }}"
                    name="extra"
                    rows="4"
                    class="{{ $inputClass }} resize-y"
                >{{ (string) session('editing_file_id') === (string) $file->id ? old('extra', $file->extra) : $file->extra }}</textarea>
                @if (session('editing_file_id') == $file->id && $errors->has('extra'))
                    <p class="mt-2 text-sm text-red-300">{{ $errors->first('extra') }}</p>
                @endif
            </div>

            <button
                type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-4 py-3 text-sm font-semibold text-zinc-950 transition hover:bg-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-500/50 active:bg-amber-600"
            >
                <x-fa-icon name="floppy-disk" class="size-4" />
                Salvează
            </button>
        </form>

        <button
            type="button"
            data-admin-delete-toggle="admin-file-delete-{{ $file->id }}"
            class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-300 transition hover:border-red-500/50 hover:bg-red-500/20 focus:outline-none focus:ring-2 focus:ring-red-500/40"
        >
            <x-fa-icon name="trash" class="size-4" />
            Șterge
        </button>

        <form
            method="POST"
            action="{{ route('admin.files.destroy', $file) }}"
            id="admin-file-delete-{{ $file->id }}"
            class="hidden space-y-3 rounded-xl border border-red-500/20 bg-zinc-950 p-4"
            data-admin-delete-form
        >
            @csrf
            @method('DELETE')

            <p class="text-sm text-zinc-300">Introdu parola ca să confirmi ștergerea.</p>

            <label for="file_delete_password_{{ $file->id }}" class="sr-only">Parolă</label>
            <input
                id="file_delete_password_{{ $file->id }}"
                name="password"
                type="password"
                autocomplete="off"
                class="{{ $inputClass }}"
                required
            >

            <button
                type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-semibold text-red-300 transition hover:border-red-500/50 hover:bg-red-500/20 focus:outline-none focus:ring-2 focus:ring-red-500/40"
            >
                Confirmă ștergerea
            </button>
        </form>
    </div>
</dialog>
