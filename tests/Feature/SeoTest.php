<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRestaurant(): RestaurantSetting
    {
        return RestaurantSetting::factory()->create([
            'restaurant_name' => 'Salt&Suger',
            'description' => 'Salt&Suger مطعم وجبات سريعة في إدلب يقدم برجر لذيذ بنكهات خاصة.',
            'currency' => 'ل.س',
            'whatsapp_number' => '963944000000',
            'whatsapp_enabled' => true,
        ]);
    }

    public function test_homepage_contains_core_seo_tags_and_restaurant_json_ld(): void
    {
        $this->seedRestaurant();

        $response = $this->get(route('home'))->assertOk();
        $html = $response->getContent();

        $response->assertSee('<title>مطعم Salt&amp;Suger | برجر ووجبات سريعة في إدلب، سوريا</title>', false);
        $response->assertSee('name="description"', false);
        $response->assertSee('rel="canonical"', false);
        $response->assertSee(route('home'), false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:image"', false);
        $response->assertSee('og:locale" content="ar_SY"', false);
        $response->assertSee('twitter:card" content="summary_large_image"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('FastFoodRestaurant', false);
        $response->assertSee('Idlib', false);
    }

    public function test_menu_page_contains_seo_metadata(): void
    {
        $this->seedRestaurant();

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('<title>منيو Salt&amp;Suger | برجر ووجبات سريعة في إدلب</title>', false)
            ->assertSee('rel="canonical"', false)
            ->assertSee(route('menu.index'), false)
            ->assertSee('"@type":"Menu"', false);
    }

    public function test_category_pages_have_appropriate_metadata(): void
    {
        $this->seedRestaurant();

        $category = Category::factory()->create([
            'name' => 'برغر',
            'slug' => 'burger',
            'description' => 'تشكيلة برجر Salt&Suger في إدلب.',
            'is_active' => true,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'برغر كلاسيك',
            'is_available' => true,
        ]);

        $this->get(route('menu.category', $category))
            ->assertOk()
            ->assertSee('<title>برغر | منيو Salt&amp;Suger في إدلب</title>', false)
            ->assertSee(route('menu.category', $category), false)
            ->assertSee('BreadcrumbList', false)
            ->assertSee('برغر كلاسيك', false);
    }

    public function test_sitemap_returns_public_urls_only(): void
    {
        $this->seedRestaurant();

        $active = Category::factory()->create([
            'name' => 'برغر',
            'slug' => 'burger',
            'is_active' => true,
        ]);

        Category::factory()->create([
            'name' => 'مخفي',
            'slug' => 'hidden',
            'is_active' => false,
        ]);

        $response = $this->get(route('sitemap'))->assertOk();
        $xml = $response->getContent();

        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $this->assertStringContainsString(route('home'), $xml);
        $this->assertStringContainsString(route('menu.index'), $xml);
        $this->assertStringContainsString(route('menu.category', $active), $xml);
        $this->assertStringNotContainsString('/admin', $xml);
        $this->assertStringNotContainsString('/cart', $xml);
        $this->assertStringNotContainsString('/checkout', $xml);
        $this->assertStringNotContainsString('hidden', $xml);
    }

    public function test_robots_txt_protects_private_areas(): void
    {
        $response = $this->get(route('robots'))->assertOk();
        $body = $response->getContent();

        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $this->assertStringContainsString('Disallow: /admin', $body);
        $this->assertStringContainsString('Disallow: /cart', $body);
        $this->assertStringContainsString('Disallow: /checkout', $body);
        $this->assertStringContainsString('Allow: /menu', $body);
        $this->assertStringContainsString('Sitemap: '.route('sitemap'), $body);
    }

    public function test_admin_pages_are_not_indexable(): void
    {
        $admin = User::factory()->admin()->create();

        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('noindex,nofollow', false);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('noindex,nofollow', false);
    }

    public function test_cart_and_checkout_are_noindex(): void
    {
        $this->seedRestaurant();

        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('noindex,nofollow', false);

        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_available' => true,
            'price' => '1000.00',
        ]);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('noindex,nofollow', false);
    }

    public function test_hero_image_is_used_in_metadata_when_configured(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('restaurant/hero/cover.png', 'fake-image');

        RestaurantSetting::factory()->create([
            'restaurant_name' => 'Salt&Suger',
            'hero_image' => 'restaurant/hero/cover.png',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('og:image" content="'.asset('storage/restaurant/hero/cover.png').'"', false)
            ->assertSee('twitter:image" content="'.asset('storage/restaurant/hero/cover.png').'"', false);
    }

    public function test_json_ld_does_not_expose_private_order_data(): void
    {
        $this->seedRestaurant();

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertStringNotContainsString('customer_phone', $html);
        $this->assertStringNotContainsString('order_number', $html);
        $this->assertStringNotContainsString('admin@gmail.com', $html);
    }

    public function test_category_query_parameter_redirects_to_clean_url(): void
    {
        $this->seedRestaurant();

        $category = Category::factory()->create([
            'slug' => 'burger',
            'is_active' => true,
        ]);

        $this->get('/menu?category=burger')
            ->assertRedirect(route('menu.category', $category));
    }
}
