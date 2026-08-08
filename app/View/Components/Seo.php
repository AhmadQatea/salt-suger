<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Reusable SEO head tags (Phase 1 foundation).
 *
 * Props for Open Graph / Twitter are accepted for later phases but are not
 * rendered yet.
 */
class Seo extends Component
{
    public function __construct(
        public ?string $title = null,
        public ?string $description = null,
        public ?string $canonical = null,
        public ?string $image = null,
        public string $robots = 'index,follow',
        public string $type = 'website',
        public ?string $siteName = null,
        public ?string $locale = null,
    ) {}

    public function render(): View|Closure|string
    {
        return view('components.seo');
    }
}
