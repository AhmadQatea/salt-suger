<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Seo extends Component
{
    /**
     * @param  list<array<string, mixed>>|array<string, mixed>  $jsonLd
     */
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $canonical = null,
        public ?string $image = null,
        public string $robots = 'index,follow',
        public string $type = 'website',
        public ?string $siteName = null,
        public ?string $locale = null,
        public array $jsonLd = [],
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.seo');
    }
}
