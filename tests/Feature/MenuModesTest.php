<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\RestaurantSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuModesTest extends TestCase
{
    use RefreshDatabase;

    protected function seedCatalog(): Category
    {
        RestaurantSetting::factory()->create([
            'restaurant_name' => 'Salt&Suger',
            'currency' => 'ل.س',
        ]);

        $category = Category::factory()->create([
            'name' => 'برغر',
            'slug' => 'burger',
            'is_active' => true,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'برغر كلاسيك',
            'description' => 'برغر لحم مع جبنة',
            'price' => '25000.00',
            'is_available' => true,
        ]);

        return $category;
    }

    public function test_display_menu_is_read_only(): void
    {
        $this->seedCatalog();

        $response = $this->get(route('menu.display'))->assertOk();

        $response
            ->assertSee('برغر كلاسيك', false)
            ->assertSee('برغر لحم مع جبنة', false)
            ->assertSee('25,000 ل.س', false)
            ->assertDontSee('product-modal', false)
            ->assertDontSee('modal-add-to-order', false)
            ->assertDontSee('data-open-product', false)
            ->assertDontSee('cart-count-badge', false)
            ->assertDontSee('data-floating-cart-wrap', false)
            ->assertDontSee('resources/js/public.js', false);
    }

    public function test_display_category_filter_stays_in_display_mode(): void
    {
        $category = $this->seedCatalog();

        $this->get(route('menu.display'))
            ->assertOk()
            ->assertSee(route('menu.display.category', $category, absolute: false), false);

        $this->get(route('menu.display', ['category' => $category->slug]))
            ->assertRedirect(route('menu.display.category', $category));
    }

    public function test_explicit_and_legacy_ordering_menus_keep_ordering_controls(): void
    {
        $this->seedCatalog();

        foreach ([route('order.index'), route('menu.index'), route('home')] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('product-modal', false)
                ->assertSee('modal-add-to-order', false)
                ->assertSee('cart-count-badge', false)
                ->assertSee(route('cart.items.store'), false);
        }
    }
}
