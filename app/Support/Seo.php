<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Product;
use App\Models\RestaurantSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public function description(?string $override = null): string
    {
        $text = trim((string) ($override ?: $this->settings->description ?: config('seo.default_description')));

        return Str::limit(strip_tags($text), 160, '…');
    }

    public function logoUrl(): string
    {
        if ($this->settings->logo && Storage::disk('public')->exists($this->settings->logo)) {
            return asset('storage/'.$this->settings->logo);
        }

        return asset('images/logo.png');
    }

    public function shareImageUrl(): string
    {
        return $this->settings->heroImageUrl($this->logoUrl());
    }

    public function absoluteUrl(?string $url = null): string
    {
        $url ??= url()->current();

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return url($url);
    }

    public function homeTitle(): string
    {
        return 'مطعم '.$this->restaurantName().' | برجر ووجبات سريعة في إدلب، سوريا';
    }

    public function menuTitle(): string
    {
        return 'منيو '.$this->restaurantName().' | برجر ووجبات سريعة في إدلب';
    }

    public function categoryTitle(Category $category): string
    {
        return $category->name.' | منيو '.$this->restaurantName().' في إدلب';
    }

    public function homeDescription(): string
    {
        return $this->description();
    }

    public function menuDescription(): string
    {
        return $this->description(
            'تصفح منيو '.$this->restaurantName().' في إدلب: برجر، ساندويشات، وجبات، بطاطا ومشروبات بنكهات خاصة مع طلب سهل عبر واتساب.'
        );
    }

    public function categoryDescription(Category $category): string
    {
        $base = trim((string) $category->description);

        if ($base === '') {
            $base = 'اكتشف تشكيلة '.$category->name.' من '.$this->restaurantName().' في إدلب.';
        }

        return $this->description($base.' اطلب من منيو '.$this->restaurantName().' في إدلب بسهولة.');
    }

    public function currencyCode(): string
    {
        $currency = trim((string) ($this->settings->currency ?: 'ل.س'));

        return match (true) {
            in_array($currency, ['ل.س', 'ل.س.', 'SYP', 'syp'], true) => 'SYP',
            default => Str::upper(Str::limit(preg_replace('/\s+/', '', $currency) ?: 'SYP', 3, '')),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function restaurantJsonLd(?string $pageUrl = null): array
    {
        $name = $this->restaurantName();
        $url = $this->absoluteUrl($pageUrl ?? route('home'));
        $logo = $this->absoluteUrl($this->logoUrl());
        $image = $this->absoluteUrl($this->shareImageUrl());

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'FastFoodRestaurant',
            '@id' => $url.'#restaurant',
            'name' => $name,
            'image' => [$image],
            'logo' => $logo,
            'url' => $url,
            'description' => $this->description($this->settings->description ?: null),
            'servesCuisine' => config('seo.cuisine'),
            'address' => [
                '@type' => 'PostalAddress',
                'addressLocality' => config('seo.city_en'),
                'addressCountry' => config('seo.country_code'),
            ],
            'areaServed' => [
                '@type' => 'City',
                'name' => config('seo.city_en'),
            ],
        ];

        $phone = $this->publicTelephone();
        if ($phone) {
            $data['telephone'] = $phone;
        }

        return $data;
    }

    /**
     * @param  Collection<int, Product>|iterable<int, Product>  $products
     * @return array<string, mixed>
     */
    public function menuJsonLd(iterable $products, ?Category $category = null, ?string $pageUrl = null): array
    {
        $items = [];
        $position = 1;

        foreach ($products as $product) {
            if (! $product instanceof Product) {
                continue;
            }

            $items[] = [
                '@type' => 'MenuItem',
                'position' => $position++,
                'name' => $product->name,
                'description' => $this->productDescription($product),
                'image' => $this->absoluteUrl($product->imageUrl() ?: $this->logoUrl()),
                'offers' => [
                    '@type' => 'Offer',
                    'price' => number_format((float) $product->price, 2, '.', ''),
                    'priceCurrency' => $this->currencyCode(),
                    'availability' => 'https://schema.org/InStock',
                ],
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Menu',
            'name' => $category
                ? 'منيو '.$category->name.' — '.$this->restaurantName()
                : 'منيو '.$this->restaurantName(),
            'url' => $this->absoluteUrl($pageUrl ?? route('menu.index')),
            'hasMenuSection' => [
                '@type' => 'MenuSection',
                'name' => $category?->name ?: 'الأصناف المتاحة',
                'hasMenuItem' => $items,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function breadcrumbJsonLd(array $crumbs): array
    {
        $items = [];

        foreach (array_values($crumbs) as $index => $crumb) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $crumb['name'],
                'item' => $this->absoluteUrl($crumb['url']),
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    public function productDescription(Product $product): string
    {
        $description = trim((string) $product->description);

        if ($description !== '') {
            return Str::limit($description, 200, '…');
        }

        return $product->name.' من '.$this->restaurantName().' في إدلب.';
    }

    public function publicTelephone(): ?string
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
}
