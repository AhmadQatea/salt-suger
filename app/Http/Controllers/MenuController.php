<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RestaurantSetting;
use App\Support\PublicStorage;
use App\Support\Seo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
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

        $categories = $this->cachedMenuCategories();

        $selectedCategory = null;
        $selectedSlug = '';

        if ($category) {
            $selectedCategory = $categories->firstWhere('id', $category->id) ?: $category;
            $selectedSlug = $selectedCategory->slug;
        }

        if ($selectedCategory) {
            $products = $selectedCategory->relationLoaded('availableProducts')
                ? $selectedCategory->availableProducts
                : $selectedCategory->availableProducts()->orderBy('name')->get();
        } else {
            $products = $categories
                ->flatMap(fn (Category $item) => $item->availableProducts)
                ->values();
        }

        $logoUrl = $this->logoUrl($settings);
        $isHome = $request->routeIs('home');

        if ($selectedCategory) {
            $seoTitle = $seo->categoryTitle($selectedCategory);
            $seoDescription = $seo->categoryDescription($selectedCategory);
            $seoCanonical = $seo->categoryCanonical($selectedCategory);
        } elseif ($isHome) {
            $seoTitle = $seo->homeTitle();
            $seoDescription = $seo->homeDescription();
            $seoCanonical = $seo->homeCanonical();
        } else {
            $seoTitle = $seo->menuTitle();
            $seoDescription = $seo->menuDescription();
            $seoCanonical = $seo->menuCanonical();
        }

        return view('menu.index', [
            'settings' => $settings,
            'categories' => $categories,
            'products' => $products,
            'selectedSlug' => $selectedSlug,
            'selectedCategory' => $selectedCategory,
            'currency' => $settings->currency ?: 'ل.س',
            'logoUrl' => $logoUrl,
            'heroUrl' => $settings->heroImageUrl($logoUrl),
            'seoTitle' => $seoTitle,
            'seoDescription' => $seoDescription,
            'seoCanonical' => $seoCanonical,
            'seoImage' => $seo->absoluteUrl($seo->imageUrl()),
            'seoRobots' => 'index,follow',
            'seoJsonLd' => [
                $seo->restaurantJsonLd($seoCanonical),
            ],
        ]);
    }

    protected function settings(): RestaurantSetting
    {
        return RestaurantSetting::cached();
    }

    /**
     * Active categories + available products for the public menu (cache invalidated on catalog changes).
     *
     * @return Collection<int, Category>
     */
    protected function cachedMenuCategories(): Collection
    {
        return Cache::remember(Category::MENU_CACHE_KEY, now()->addMinutes(30), function () {
            return Category::query()
                ->active()
                ->select(['id', 'name', 'slug', 'description', 'image', 'is_active', 'updated_at'])
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
                        'updated_at',
                    ])->orderBy('name');
                }])
                ->orderBy('name')
                ->get();
        });
    }

    protected function logoUrl(RestaurantSetting $settings): string
    {
        return $settings->logoUrl(asset('images/logo.png'));
    }
}
