<nav
    class="fixed inset-x-0 bottom-0 z-50 border-t border-zinc-800/80 bg-zinc-950/95 backdrop-blur"
    style="padding-bottom: env(safe-area-inset-bottom, 0px);"
    aria-label="Navigare principală"
>
    <div class="mx-auto grid w-full max-w-lg grid-cols-3">
        @foreach (config('navigation.items') as $item)
            @php
                $active = request()->routeIs($item['route']);
            @endphp
            <a
                href="{{ route($item['route']) }}"
                @class([
                    'flex flex-col items-center justify-center gap-1 px-2 py-3 text-center transition active:bg-zinc-900',
                    'text-amber-500' => $active,
                    'text-zinc-500 hover:text-zinc-300' => ! $active,
                ])
                @if ($active) aria-current="page" @endif
            >
                <x-fa-icon :name="$item['icon']" class="size-6" />
                <span class="text-[11px] font-medium leading-tight">{{ $item['short_label'] }}</span>
            </a>
        @endforeach
    </div>
</nav>
