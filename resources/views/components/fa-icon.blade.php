@if ($svg = $svgMarkup())
    <span {{ $attributes->class(['inline-flex shrink-0']) }}>
        {!! preg_replace(
            '/<svg\b/',
            '<svg class="size-full" aria-hidden="true" focusable="false"',
            $svg,
            1,
        ) !!}
    </span>
@endif
