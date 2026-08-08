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
        public string $siteName = '',
        public string $locale = '',
        public array $jsonLd = [],
    ) {
        $this->siteName = $siteName !== '' ? $siteName : (string) config('app.name', 'Salt&Suger');
        $this->locale = $locale !== '' ? $locale : (string) config('seo.locale', 'ar_SY');
        $this->canonical = $canonical ?: url()->current();
        $this->image = $image ?: asset('images/logo.png');
        $this->title = $title ?: $this->siteName;
        $this->description = $description ?: (string) config('seo.default_description');
    }

    public function render(): View|Closure|string
    {
        return view('components.seo');
    }
}
