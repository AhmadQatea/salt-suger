<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\RestaurantSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MenuController extends Controller
{
    /**
     * Display the public digital menu.
     */
    public function index(Request $request): View
    {
        $settings = $this->settings();

        $categories = Category::query()
            ->active()
            ->select(['id', 'name', 'slug', 'image', 'sort_order', 'is_active'])
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

        $selectedSlug = trim((string) $request->query('category', ''));
        $selectedCategory = null;

        if ($selectedSlug !== '') {
            $selectedCategory = $categories->firstWhere('slug', $selectedSlug);
        }

        if ($selectedCategory) {
            $products = $selectedCategory->availableProducts;
        } else {
            $selectedSlug = '';
            $products = $categories
                ->flatMap(fn (Category $category) => $category->availableProducts)
                ->values();
        }

        return view('menu.index', [
            'settings' => $settings,
            'categories' => $categories,
            'products' => $products,
            'selectedSlug' => $selectedSlug,
            'selectedCategory' => $selectedCategory,
            'currency' => $settings->currency ?: 'ل.س',
            'logoUrl' => $this->logoUrl($settings),
            'heroUrl' => $settings->heroImageUrl($this->logoUrl($settings)),
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
