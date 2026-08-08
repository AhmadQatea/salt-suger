<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function seedMenuBasics(): RestaurantSetting
    {
        return RestaurantSetting::factory()->create([
            'restaurant_name' => 'Salt&Suger',
            'description' => 'وجبتك المفضلة... بطلب أسهل وأسرع',
            'currency' => 'ل.س',
            'primary_color' => '#ba0013',
            'secondary_color' => '#111111',
            'accent_color' => '#cca800',
        ]);
    }

    public function test_guest_can_access_home_menu_without_authentication(): void
    {
        $this->seedMenuBasics();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Salt&Suger')
            ->assertSee('أهلاً بك في', false)
            ->assertSee('product-modal', false)
            ->assertSee('modal-add-to-order', false);
    }

    public function test_guest_can_access_menu_route(): void
    {
        $this->seedMenuBasics();

        $this->get(route('menu.index'))
            ->assertOk();
    }

    public function test_only_active_categories_appear(): void
    {
        $this->seedMenuBasics();

        Category::factory()->create([
            'name' => 'برغر نشط',
            'slug' => 'active-burgers',
            'is_active' => true,
        ]);

        Category::factory()->create([
            'name' => 'تصنيف مخفي',
            'slug' => 'hidden-category',
            'is_active' => false,
        ]);

        $response = $this->get(route('menu.index'));

        $response->assertOk();
        $response->assertSee('برغر نشط', false);
        $response->assertDontSee('تصنيف مخفي', false);
    }

    public function test_only_available_products_appear(): void
    {
        $this->seedMenuBasics();

        $category = Category::factory()->create([
            'name' => 'برغر',
            'slug' => 'burger',
            'is_active' => true,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'برغر كلاسيك',
            'slug' => 'classic-burger',
            'description' => 'برغر لحم، جبنة، خس، طماطم، وصوص خاص',
            'price' => '25000.00',
            'badge' => 'الأكثر طلباً',
            'is_available' => true,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'برغر غير متوفر',
            'slug' => 'unavailable-burger',
            'is_available' => false,
        ]);

        $response = $this->get(route('menu.index'));

        $response->assertOk();
        $response->assertSee('برغر كلاسيك', false);
        $response->assertSee('برغر لحم، جبنة، خس، طماطم، وصوص خاص', false);
        $response->assertSee('25,000 ل.س', false);
        $response->assertSee('الأكثر طلباً', false);
        $response->assertDontSee('برغر غير متوفر', false);
    }

    public function test_product_badge_is_hidden_when_null(): void
    {
        $this->seedMenuBasics();

        $category = Category::factory()->create(['is_active' => true, 'slug' => 'meals']);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'وجبة عادية',
            'slug' => 'plain-meal',
            'badge' => null,
            'is_available' => true,
        ]);

        $html = $this->get(route('menu.index'))->assertOk()->getContent();

        $this->assertStringContainsString('وجبة عادية', $html);
        $this->assertStringNotContainsString('data-product-badge="جديد"', $html);
    }

    public function test_filtering_by_valid_category_slug_returns_that_category_products(): void
    {
        $this->seedMenuBasics();

        $burgers = Category::factory()->create([
            'name' => 'برغر',
            'slug' => 'burger',
            'is_active' => true,
        ]);

        $pizza = Category::factory()->create([
            'name' => 'بيتزا',
            'slug' => 'pizza',
            'is_active' => true,
        ]);

        Product::factory()->create([
            'category_id' => $burgers->id,
            'name' => 'برغر كلاسيك',
            'slug' => 'classic-burger',
            'is_available' => true,
        ]);

        Product::factory()->create([
            'category_id' => $pizza->id,
            'name' => 'بيتزا مارغريتا',
            'slug' => 'margherita-pizza',
            'is_available' => true,
        ]);

        $this->get(route('menu.index', ['category' => 'burger']))
            ->assertRedirect(route('menu.category', $burgers));

        $this->get(route('menu.category', $burgers))
            ->assertOk()
            ->assertSee('برغر كلاسيك', false)
            ->assertDontSee('بيتزا مارغريتا', false);
    }

    public function test_filtering_by_inactive_category_does_not_expose_its_products(): void
    {
        $this->seedMenuBasics();

        $inactive = Category::factory()->create([
            'name' => 'مخفي',
            'slug' => 'hidden',
            'is_active' => false,
        ]);

        Product::factory()->create([
            'category_id' => $inactive->id,
            'name' => 'صنف مخفي',
            'slug' => 'hidden-product',
            'is_available' => true,
        ]);

        $this->get(route('menu.index', ['category' => 'hidden']))
            ->assertOk()
            ->assertDontSee('صنف مخفي', false)
            ->assertDontSee('مخفي', false);

        $this->get('/menu/hidden')->assertNotFound();
    }

    public function test_invalid_category_slug_fails_gracefully(): void
    {
        $this->seedMenuBasics();

        $category = Category::factory()->create([
            'name' => 'برغر',
            'slug' => 'burger',
            'is_active' => true,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'برغر كلاسيك',
            'slug' => 'classic-burger',
            'is_available' => true,
        ]);

        $this->get(route('menu.index', ['category' => 'does-not-exist']))
            ->assertOk()
            ->assertSee('برغر كلاسيك', false);
    }

    public function test_restaurant_settings_and_currency_are_loaded_from_database(): void
    {
        RestaurantSetting::factory()->create([
            'restaurant_name' => 'مطعم الاختبار',
            'currency' => 'ل.س',
            'description' => 'وصف من قاعدة البيانات',
        ]);

        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'منتج تجريبي',
            'price' => '1000.00',
            'is_available' => true,
        ]);

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('مطعم الاختبار', false)
            ->assertSee('وصف من قاعدة البيانات', false)
            ->assertSee('1,000 ل.س', false);
    }

    public function test_empty_states_are_shown_when_needed(): void
    {
        $this->seedMenuBasics();

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('لا توجد تصنيفات متاحة حالياً.', false);

        $category = Category::factory()->create([
            'name' => 'مشروبات',
            'slug' => 'drinks',
            'is_active' => true,
        ]);

        $this->get(route('menu.category', $category))
            ->assertOk()
            ->assertSee('لا توجد أصناف متاحة ضمن هذا التصنيف.', false);

        $this->assertSame(0, $category->availableProducts()->count());
    }

    public function test_guest_still_cannot_access_admin_management(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.products.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_authenticated_admin_can_still_access_admin_area(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_missing_product_image_uses_fallback_and_does_not_break(): void
    {
        Storage::fake('public');
        $this->seedMenuBasics();

        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'منتج بلا صورة',
            'image' => 'products/missing.webp',
            'is_available' => true,
        ]);

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('منتج بلا صورة', false)
            ->assertSee(asset('images/logo.png'), false);
    }

    public function test_public_menu_uses_custom_hero_image_when_present(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('restaurant/hero/cover.png', 'fake');

        RestaurantSetting::factory()->create([
            'restaurant_name' => 'Salt&Suger',
            'hero_image' => 'restaurant/hero/cover.png',
        ]);

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('restaurant/hero/cover.png', false)
            ->assertSee('menu-hero', false);
    }
}
