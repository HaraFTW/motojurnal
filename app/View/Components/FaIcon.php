<?php

namespace App\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class FaIcon extends Component
{
    /** @var list<string> */
    private const STYLES = ['solid', 'regular', 'brands'];

    public function __construct(
        public string $name,
        public string $style = 'solid',
    ) {
        if (! in_array($style, self::STYLES, true)) {
            $this->style = 'solid';
        }
    }

    public function svgMarkup(): ?string
    {
        foreach ($this->resolvePaths() as $path) {
            if (is_file($path)) {
                return file_get_contents($path);
            }
        }

        return null;
    }

    /** @return list<string> */
    private function resolvePaths(): array
    {
        $file = $this->name.'.svg';

        return [
            public_path("build/fontawesome/svgs/{$this->style}/{$file}"),
            base_path("node_modules/@fortawesome/fontawesome-free/svgs/{$this->style}/{$file}"),
        ];
    }

    public function render(): View
    {
        return view('components.fa-icon');
    }
}
