<?php

namespace App\Support;

use App\Models\Category;
use App\Models\RestaurantSetting;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * SEO helpers for public pages and local business metadata.
 */
class Seo
{
    public function __construct(
        protected RestaurantSetting $settings,
    ) {}

    public static function fromSettings(?RestaurantSetting $settings = null): self
    {
        return new self($settings ?? RestaurantSetting::cached());
    }

    public function restaurantName(): string
    {
        return $this->settings->restaurant_name ?: (string) config('app.name', 'Salt&Suger');
    }

    public function title(?string $override = null): string
    {
        $title = trim((string) ($override ?: $this->homeTitle()));

        return $title !== '' ? $title : $this->restaurantName();
    }

    public function description(?string $override = null): string
    {
        $text = trim((string) (
            $override
            ?: $this->settings->description
            ?: config('seo.default_description')
        ));

        return $this->limitDescription($text);
    }

    public function homeTitle(): string
    {
        return $this->fill(config('seo.home_title'), [
            'name' => $this->restaurantName(),
        ]) ?: (string) config('seo.default_title');
    }

    public function homeDescription(): string
    {
        $fromSettings = trim((string) $this->settings->description);

        if ($fromSettings !== '') {
            return $this->limitDescription($fromSettings);
        }

        return $this->limitDescription($this->fill(config('seo.home_description'), [
            'name' => $this->restaurantName(),
        ]) ?: (string) config('seo.default_description'));
    }

    public function menuTitle(): string
    {
        return $this->fill(config('seo.menu_title'), [
            'name' => $this->restaurantName(),
        ]);
    }

    public function menuDescription(): string
    {
        return $this->limitDescription($this->fill(config('seo.menu_description'), [
            'name' => $this->restaurantName(),
        ]));
    }

    public function categoryTitle(Category $category): string
    {
        return $this->fill(config('seo.category_title'), [
            'name' => $this->restaurantName(),
            'category' => $category->name,
        ]);
    }

    public function categoryDescription(Category $category): string
    {
        $custom = trim((string) $category->description);

        if ($custom !== '') {
            return $this->limitDescription($custom);
        }

        return $this->limitDescription($this->fill(config('seo.category_description'), [
            'name' => $this->restaurantName(),
            'category' => $category->name,
        ]));
    }

    public function homeCanonical(): string
    {
        return $this->absoluteUrl(route('home'));
    }

    public function menuCanonical(): string
    {
        return $this->absoluteUrl(route('menu.index'));
    }

    public function categoryCanonical(Category $category): string
    {
        return $this->absoluteUrl(route('menu.category', $category));
    }

    public function logoUrl(): string
    {
        if ($this->settings->logo && Storage::disk('public')->exists($this->settings->logo)) {
            return asset('storage/'.$this->settings->logo);
        }

        return asset('images/logo.png');
    }

    public function imageUrl(): string
    {
        return $this->settings->heroImageUrl($this->logoUrl());
    }

    public function telephone(): ?string
    {
        $number = preg_replace('/\D+/', '', (string) $this->settings->whatsapp_number);

        if (! $number) {
            return null;
        }

        if (Str::startsWith($number, '00')) {
            $number = substr($number, 2);
        }

        return '+'.$number;
    }

    public function locality(): string
    {
        return (string) config('seo.city_en', 'Idlib');
    }

    public function countryCode(): string
    {
        return (string) config('seo.country_code', 'SY');
    }

    /**
     * Public FastFoodRestaurant JSON-LD for local SEO (Idlib, Syria only).
     *
     * @return array<string, mixed>
     */
    public function restaurantJsonLd(?string $pageUrl = null): array
    {
        $name = $this->restaurantName();
        $url = $this->absoluteUrl($pageUrl ?? route('home'));
        $logo = $this->absoluteUrl($this->logoUrl());
        $image = $this->absoluteUrl($this->imageUrl());
        $description = $this->homeDescription();

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'FastFoodRestaurant',
            '@id' => $url.'#restaurant',
            'name' => $name,
            'alternateName' => [
                'Salt&Suger',
                'مطعم حلو ومالح',
            ],
            'description' => $description,
            'url' => $url,
            'image' => [$image],
            'logo' => $logo,
            'servesCuisine' => config('seo.cuisine'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => $this->locality(),
                'addressCountry' => $this->countryCode(),
            ],
            'areaServed' => [
                '@type' => 'City',
                'name' => $this->locality(),
            ],
        ];

        $phone = $this->telephone();
        if ($phone) {
            $data['telephone'] = $phone;
        }

        return $data;
    }

    public function absoluteUrl(?string $url = null): string
    {
        $url ??= url()->current();

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return url($url);
    }

    public function settings(): RestaurantSetting
    {
        return $this->settings;
    }

    /**
     * @param  array<string, string>  $replacements
     */
    protected function fill(?string $template, array $replacements): string
    {
        $template = (string) $template;

        foreach ($replacements as $key => $value) {
            $template = str_replace(':'.$key, $value, $template);
        }

        return trim($template);
    }

    protected function limitDescription(string $text): string
    {
        return Str::limit(strip_tags(trim($text)), 160, '…');
    }
}
