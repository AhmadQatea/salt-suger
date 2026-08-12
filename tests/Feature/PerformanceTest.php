<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\RestaurantSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function seedBasics(): void
    {
        RestaurantSetting::factory()->create([
            'restaurant_name' => 'Salt&Suger',
            'description' => 'Salt&Suger مطعم حلو ومالح في إدلب.',
        ]);
    }

    public function test_homepage_and_menu_return_ok(): void
    {
        $this->seedBasics();

        $this->get(route('home'))->assertOk();
        $this->get(route('menu.index'))->assertOk();
    }

    public function test_hero_image_is_eager_and_prioritized(): void
    {
        $this->seedBasics();

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringContainsString('menu-hero', $html);
        $this->assertStringContainsString('fetchpriority="high"', $html);
        $this->assertStringContainsString('loading="eager"', $html);
        $this->assertStringContainsString('width="1280"', $html);
        $this->assertStringContainsString('height="420"', $html);
        $this->assertStringNotContainsString('menu-hero__media" loading="lazy"', $html);
    }

    public function test_product_images_are_lazy_loaded_with_dimensions(): void
    {
        $this->seedBasics();

        $category = Category::factory()->create(['is_active' => true, 'slug' => 'burger']);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'برغر كلاسيك',
            'is_available' => true,
        ]);

        $html = $this->get(route('menu.index'))->assertOk()->getContent();

        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringContainsString('width="400"', $html);
        $this->assertStringContainsString('height="400"', $html);
        $this->assertStringContainsString('menu-product-card__media', $html);
    }

    public function test_public_assets_are_split_from_admin_bundle(): void
    {
        $this->seedBasics();

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertTrue(
            str_contains($html, 'resources/js/public.js')
            || (bool) preg_match('/build\/assets\/public-[^"]+\.js/', $html),
            'Public JS entry should be referenced.'
        );
        $this->assertFalse(
            str_contains($html, 'resources/js/admin.js')
            || (bool) preg_match('/build\/assets\/admin-[^"]+\.js/', $html),
            'Admin JS entry should not load on the public menu.'
        );
    }

    public function test_seo_metadata_still_present_after_performance_changes(): void
    {
        $this->seedBasics();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('rel="canonical"', false)
            ->assertSee('name="description"', false)
            ->assertSee('FastFoodRestaurant', false);
    }

    public function test_menu_cache_invalidates_when_product_changes(): void
    {
        $this->seedBasics();

        $category = Category::factory()->create(['is_active' => true, 'slug' => 'burger']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'برغر قديم',
            'is_available' => true,
        ]);

        $this->get(route('menu.index'))->assertOk()->assertSee('برغر قديم', false);
        $this->assertTrue(Cache::has(Category::MENU_CACHE_KEY));

        $product->update(['name' => 'برغر جديد']);

        $this->assertFalse(Cache::has(Category::MENU_CACHE_KEY));

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('برغر جديد', false)
            ->assertDontSee('برغر قديم', false);
    }
}
