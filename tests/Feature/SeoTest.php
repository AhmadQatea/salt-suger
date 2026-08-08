<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function seedRestaurant(): RestaurantSetting
    {
        return RestaurantSetting::factory()->create([
            'restaurant_name' => 'Salt&Suger',
            'description' => null,
            'currency' => 'ل.س',
        ]);
    }

    public function test_homepage_returns_ok_with_arabic_rtl_document(): void
    {
        $this->seedRestaurant();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('lang="ar"', false)
            ->assertSee('dir="rtl"', false);
    }

    public function test_homepage_has_expected_dynamic_title_and_description(): void
    {
        $this->seedRestaurant();

        $response = $this->get(route('home'))->assertOk();

        $response->assertSee('<title>مطعم Salt&amp;Suger | برجر ووجبات سريعة في إدلب، سوريا</title>', false);
        $response->assertSee(
            'Salt&amp;Suger مطعم وجبات سريعة في إدلب يقدم البرجر والساندويشات والوجبات بنكهات خاصة.',
            false
        );
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('href="'.route('home').'"', false);
    }

    public function test_homepage_canonical_is_absolute(): void
    {
        $this->seedRestaurant();

        $html = $this->get(route('home'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/rel="canonical" href="https?:\/\//',
            $html
        );
    }

    public function test_homepage_uses_restaurant_description_when_available(): void
    {
        RestaurantSetting::factory()->create([
            'restaurant_name' => 'Salt&Suger',
            'description' => 'وصف مخصص من إعدادات المطعم',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('وصف مخصص من إعدادات المطعم', false);
    }

    public function test_menu_has_different_title_and_description_from_homepage(): void
    {
        $this->seedRestaurant();

        $home = $this->get(route('home'))->assertOk()->getContent();
        $menu = $this->get(route('menu.index'))->assertOk()->getContent();

        $this->assertStringContainsString(
            '<title>مطعم Salt&amp;Suger | برجر ووجبات سريعة في إدلب، سوريا</title>',
            $home
        );
        $this->assertStringContainsString(
            '<title>منيو Salt&amp;Suger | برجر ووجبات سريعة في إدلب</title>',
            $menu
        );
        $this->assertStringContainsString('تصفح منيو Salt&amp;Suger في إدلب', $menu);
        $this->assertNotSame($home, $menu);
    }

    public function test_valid_category_generates_dynamic_seo_metadata(): void
    {
        $this->seedRestaurant();

        $burgers = Category::factory()->create([
            'name' => 'برغر',
            'slug' => 'burger',
            'description' => null,
            'is_active' => true,
        ]);

        Product::factory()->create([
            'category_id' => $burgers->id,
            'name' => 'برغر كلاسيك',
            'is_available' => true,
        ]);

        $response = $this->get(route('menu.category', $burgers))->assertOk();

        $response->assertSee('<title>برغر | منيو Salt&amp;Suger في إدلب</title>', false);
        $response->assertSee('اكتشف تشكيلة برغر من Salt&amp;Suger في إدلب', false);
        $response->assertSee('href="'.route('menu.category', $burgers).'"', false);
    }

    public function test_category_description_uses_category_record_when_present(): void
    {
        $this->seedRestaurant();

        $drinks = Category::factory()->create([
            'name' => 'مشروبات',
            'slug' => 'drinks',
            'description' => 'مشروبات باردة وساخنة من Salt&Suger.',
            'is_active' => true,
        ]);

        $this->get(route('menu.category', $drinks))
            ->assertOk()
            ->assertSee('<title>مشروبات | منيو Salt&amp;Suger في إدلب</title>', false)
            ->assertSee('مشروبات باردة وساخنة من Salt&amp;Suger.', false);
    }

    public function test_invalid_category_does_not_create_misleading_seo_metadata(): void
    {
        $this->seedRestaurant();

        $response = $this->get('/menu?category=does-not-exist')->assertOk();

        $response->assertSee('<title>منيو Salt&amp;Suger | برجر ووجبات سريعة في إدلب</title>', false);
        $response->assertSee('href="'.route('menu.index').'"', false);
        $response->assertDontSee('does-not-exist', false);
    }

    public function test_inactive_category_is_not_an_seo_landing_page(): void
    {
        $this->seedRestaurant();

        Category::factory()->create([
            'name' => 'مخفي',
            'slug' => 'hidden',
            'is_active' => false,
        ]);

        $this->get('/menu/hidden')->assertNotFound();

        $this->get('/menu?category=hidden')
            ->assertOk()
            ->assertSee('<title>منيو Salt&amp;Suger | برجر ووجبات سريعة في إدلب</title>', false)
            ->assertDontSee('<title>مخفي |', false);
    }

    public function test_unrelated_query_parameters_do_not_change_canonical(): void
    {
        $this->seedRestaurant();

        $category = Category::factory()->create([
            'name' => 'مقبلات',
            'slug' => 'appetizers',
            'is_active' => true,
        ]);

        $menuHtml = $this->get('/menu?utm_source=instagram&utm_campaign=spring')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('href="'.route('menu.index').'"', $menuHtml);
        $this->assertStringNotContainsString('utm_source', $menuHtml);

        $categoryHtml = $this->get('/menu/'.$category->slug.'?utm_source=instagram')
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('href="'.route('menu.category', $category).'"', $categoryHtml);
        $this->assertStringNotContainsString('utm_source=instagram', $categoryHtml);
    }

    public function test_category_query_redirects_to_clean_canonical_route(): void
    {
        $this->seedRestaurant();

        $category = Category::factory()->create([
            'slug' => 'burger',
            'name' => 'برغر',
            'is_active' => true,
        ]);

        $this->get('/menu?category=burger')
            ->assertRedirect(route('menu.category', $category));
    }

    public function test_admin_remains_noindex(): void
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
}
