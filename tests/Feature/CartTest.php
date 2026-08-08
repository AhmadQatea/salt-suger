<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $cart;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cart = app(CartService::class);
        RestaurantSetting::factory()->create(['currency' => 'ل.س']);
    }

    protected function availableProduct(array $overrides = []): Product
    {
        $category = Category::factory()->create([
            'is_active' => true,
            'slug' => 'burger-'.uniqid(),
        ]);

        return Product::factory()->create(array_merge([
            'category_id' => $category->id,
            'name' => 'برغر كلاسيك',
            'price' => '50000.00',
            'is_available' => true,
        ], $overrides));
    }

    public function test_empty_cart_page_shows_empty_state(): void
    {
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('سلتك فارغة', false)
            ->assertSee('تصفح المنيو', false);
    }

    public function test_guest_can_add_product_to_cart(): void
    {
        $product = $this->availableProduct();

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
            'note' => 'بدون بصل',
        ])->assertRedirect();

        $this->assertSame(2, $this->cart->totalQuantity());
        $this->assertSame('100000.00', $this->cart->subtotal());
    }

    public function test_adding_same_product_with_same_note_merges_quantities(): void
    {
        $product = $this->availableProduct();

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'note' => 'بدون بصل',
        ]);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 2,
            'note' => 'بدون بصل',
        ]);

        $this->assertCount(1, $this->cart->all());
        $this->assertSame(3, $this->cart->totalQuantity());
    }

    public function test_same_product_with_different_note_creates_separate_line(): void
    {
        $product = $this->availableProduct();

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'note' => 'بدون بصل',
        ]);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'note' => 'صوص إضافي',
        ]);

        $this->assertCount(2, $this->cart->all());
        $this->assertSame(2, $this->cart->totalQuantity());
    }

    public function test_can_increase_and_decrease_quantity(): void
    {
        $product = $this->availableProduct();
        $this->cart->add($product->id, 2, null);
        $key = array_key_first($this->cart->all());

        $this->patch(route('cart.items.update', ['key' => $key]), [
            'quantity' => 5,
        ])->assertRedirect();

        $this->assertSame(5, $this->cart->all()[$key]['quantity']);

        $this->patch(route('cart.items.update', ['key' => $key]), [
            'quantity' => 1,
        ])->assertRedirect();

        $this->assertSame(1, $this->cart->all()[$key]['quantity']);
    }

    public function test_quantity_cannot_exceed_maximum(): void
    {
        $product = $this->availableProduct();

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 100,
        ])->assertSessionHasErrors('quantity');

        $this->assertTrue($this->cart->isEmpty());
    }

    public function test_can_remove_item_and_clear_cart(): void
    {
        $product = $this->availableProduct();
        $this->cart->add($product->id, 1, null);
        $key = array_key_first($this->cart->all());

        $this->delete(route('cart.items.destroy', ['key' => $key]))
            ->assertRedirect()
            ->assertSessionHas('status', 'تم حذف الصنف من الطلب.');

        $this->assertTrue($this->cart->isEmpty());

        $this->cart->add($product->id, 2, null);
        $this->delete(route('cart.clear'))->assertRedirect(route('cart.index'));
        $this->assertTrue($this->cart->isEmpty());
    }

    public function test_cart_total_and_badge_count_are_correct(): void
    {
        $burger = $this->availableProduct(['name' => 'برغر', 'price' => '50000.00']);
        $fries = $this->availableProduct(['name' => 'بطاطا', 'price' => '10000.00']);

        $this->cart->add($burger->id, 2, null);
        $this->cart->add($fries->id, 1, null);

        $this->assertSame(3, $this->cart->totalQuantity());
        $this->assertSame('110000.00', $this->cart->subtotal());

        $this->get(route('menu.index'))
            ->assertOk()
            ->assertSee('data-cart-count="3"', false);
    }

    public function test_unavailable_product_cannot_be_added(): void
    {
        $product = $this->availableProduct(['is_available' => false]);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertSessionHasErrors('product_id');

        $this->assertTrue($this->cart->isEmpty());
    }

    public function test_inactive_category_product_cannot_be_added(): void
    {
        $category = Category::factory()->create(['is_active' => false]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_available' => true,
            'price' => '20000.00',
        ]);

        $this->postJson(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('product_id');
    }

    public function test_client_provided_price_is_ignored(): void
    {
        $product = $this->availableProduct(['price' => '50000.00']);

        $this->post(route('cart.items.store'), [
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => '1',
        ])->assertRedirect();

        $item = collect($this->cart->all())->first();

        $this->assertSame('50000.00', $item['price']);
        $this->assertSame('50000.00', $item['subtotal']);
    }

    public function test_can_update_product_note(): void
    {
        $product = $this->availableProduct();
        $this->cart->add($product->id, 1, 'بدون بصل');
        $key = array_key_first($this->cart->all());

        $this->patch(route('cart.items.update', ['key' => $key]), [
            'note' => 'صوص إضافي',
        ])->assertRedirect();

        $items = $this->cart->all();
        $this->assertCount(1, $items);
        $this->assertSame('صوص إضافي', collect($items)->first()['note']);
    }
}
