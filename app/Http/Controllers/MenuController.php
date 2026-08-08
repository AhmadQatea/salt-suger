<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RestaurantSetting;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * Display the public digital menu.
     */
    public function index(Request $request, ?Category $category = null): View|RedirectResponse
    {
        $settings = $this->settings();
        $seo = Seo::fromSettings($settings);

        $querySlug = trim((string) $request->query('category', ''));

        if ($category === null && $querySlug !== '') {
            $matched = Category::query()->active()->where('slug', $querySlug)->first();

            if ($matched) {
                return redirect()->route('menu.category', $matched, 301);
            }
        }

        if ($category && ! $category->is_active) {
            abort(404);
        }

        $categories = Category::query()
            ->active()
            ->select(['id', 'name', 'slug', 'description', 'image', 'sort_order', 'is_active', 'updated_at'])
            ->with(['availableProducts' => function ($query) {
                $query->select([
                    'id',
                    'category_id',
                    'name',
                    'slug',
                    'description',
                    'price',
                    'image',
                    'badge',
                    'is_available',
                    'sort_order',
                ])->orderBy('sort_order')->orderBy('name');
            }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $selectedCategory = null;
        $selectedSlug = '';

        if ($category) {
            $selectedCategory = $categories->firstWhere('id', $category->id) ?: $category;
            $selectedSlug = $selectedCategory->slug;
        }

        if ($selectedCategory) {
            $products = $selectedCategory->relationLoaded('availableProducts')
                ? $selectedCategory->availableProducts
                : $selectedCategory->availableProducts()->orderBy('sort_order')->orderBy('name')->get();
        } else {
            $products = $categories
                ->flatMap(fn (Category $item) => $item->availableProducts)
                ->values();
        }

        $isHome = $request->routeIs('home');
        $logoUrl = $this->logoUrl($settings);
        $heroUrl = $settings->heroImageUrl($logoUrl);
        $canonical = $selectedCategory
            ? route('menu.category', $selectedCategory)
            : ($isHome ? route('home') : route('menu.index'));

        $title = $selectedCategory
            ? $seo->categoryTitle($selectedCategory)
            : ($isHome ? $seo->homeTitle() : $seo->menuTitle());

        $description = $selectedCategory
            ? $seo->categoryDescription($selectedCategory)
            : ($isHome ? $seo->homeDescription() : $seo->menuDescription());

        $jsonLd = [
            $seo->restaurantJsonLd($canonical),
            $seo->menuJsonLd($products, $selectedCategory, $canonical),
        ];

        if ($selectedCategory) {
            $jsonLd[] = $seo->breadcrumbJsonLd([
                ['name' => 'الرئيسية', 'url' => route('home')],
                ['name' => 'المنيو', 'url' => route('menu.index')],
                ['name' => $selectedCategory->name, 'url' => $canonical],
            ]);
        }

        return view('menu.index', [
            'settings' => $settings,
            'categories' => $categories,
            'products' => $products,
            'selectedSlug' => $selectedSlug,
            'selectedCategory' => $selectedCategory,
            'currency' => $settings->currency ?: 'ل.س',
            'logoUrl' => $logoUrl,
            'heroUrl' => $heroUrl,
            'seoTitle' => $title,
            'seoDescription' => $description,
            'seoCanonical' => $canonical,
            'seoImage' => $seo->absoluteUrl($heroUrl),
            'seoJsonLd' => $jsonLd,
            'seoRobots' => 'index,follow',
        ]);
    }

    protected function settings(): RestaurantSetting
    {
        return RestaurantSetting::cached();
    }

    protected function logoUrl(RestaurantSetting $settings): string
    {
        if ($settings->logo && Storage::disk('public')->exists($settings->logo)) {
            return asset('storage/'.$settings->logo);
        }

        return asset('images/logo.png');
    }
}
