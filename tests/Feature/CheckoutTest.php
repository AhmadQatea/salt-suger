<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $cart;

    protected RestaurantSetting $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cart = app(CartService::class);
        $this->settings = RestaurantSetting::factory()->create([
            'currency' => 'ل.س',
            'whatsapp_enabled' => true,
            'whatsapp_number' => '+963911111111',
        ]);
    }

    protected function availableProduct(array $overrides = []): Product
    {
        $category = Category::factory()->create([
            'is_active' => true,
            'slug' => 'cat-'.uniqid(),
        ]);

        return Product::factory()->create(array_merge([
            'category_id' => $category->id,
            'name' => 'برغر كلاسيك',
            'price' => '50000.00',
            'is_available' => true,
        ], $overrides));
    }

    public function test_empty_cart_redirects_away_from_checkout(): void
    {
        $this->get(route('checkout.index'))
            ->assertRedirect(route('cart.index'));
    }

    public function test_guest_can_access_checkout_with_cart(): void
    {
        $product = $this->availableProduct();
        $this->cart->add($product->id, 1);

        $this->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('إتمام الطلب', false)
            ->assertSee('ملخص الطلب', false)
            ->assertSee('ملاحظات إضافية على الطلب', false)
            ->assertSee('إتمام الطلب عبر واتساب', false)
            ->assertSee('سيتم فتح واتساب لإرسال طلبك مباشرة إلى المطعم.', false)
            ->assertDontSee('الاسم', false)
            ->assertDontSee('رقم الواتساب', false)
            ->assertDontSee('يرجى إدخال رقم واتساب صحيح.', false);
    }

    public function test_valid_checkout_creates_order_without_customer_contact(): void
    {
        $product = $this->availableProduct([
            'name' => 'برغر كلاسيك',
            'price' => '50000.00',
        ]);

        $this->cart->add($product->id, 2, 'بدون بصل');

        $response = $this->post(route('checkout.store'), [
            'notes' => 'يرجى التجهيز بسرعة',
            'price' => '1',
            'customer_name' => 'should-be-ignored',
            'customer_phone' => '0912345678',
        ]);

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $response->assertRedirect();

        $location = $response->headers->get('Location');
        $this->assertNotNull($location);
        $this->assertStringStartsWith('https://wa.me/963911111111?text=', $location);
        $this->assertNotNull($order->fresh()->whatsapp_sent_at);

        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertNull($order->customer_phone);
        $this->assertNull($order->customer_name);
        $this->assertSame('يرجى التجهيز بسرعة', $order->notes);
        $this->assertSame('ل.س', $order->currency);
        $this->assertSame('100000.00', $order->subtotal);
        $this->assertSame('100000.00', $order->total);
        $this->assertMatchesRegularExpression('/^ORD-\d{8}-\d{4}$/', $order->order_number);

        $item = OrderItem::query()->first();
        $this->assertSame($order->id, $item->order_id);
        $this->assertSame($product->id, $item->product_id);
        $this->assertSame('برغر كلاسيك', $item->product_name);
        $this->assertSame('50000.00', $item->product_price);
        $this->assertSame(2, $item->quantity);
        $this->assertSame('100000.00', $item->subtotal);
        $this->assertSame('بدون بصل', $item->note);

        $this->assertTrue($this->cart->isEmpty());
    }

    public function test_checkout_succeeds_with_only_csrf_and_optional_notes(): void
    {
        $product = $this->availableProduct();
        $this->cart->add($product->id, 1);

        $this->post(route('checkout.store'), [])
            ->assertRedirect();

        $order = Order::query()->first();
        $this->assertNotNull($order);
        $this->assertNull($order->customer_name);
        $this->assertNull($order->customer_phone);
        $this->assertTrue(blank($order->notes));
    }

    public function test_checkout_uses_latest_database_price_not_session_price(): void
    {
        $product = $this->availableProduct(['price' => '50000.00']);
        $this->cart->add($product->id, 1);

        $product->update(['price' => '70000.00']);

        $this->post(route('checkout.store'), [])->assertRedirect();

        $order = Order::query()->first();
        $item = OrderItem::query()->first();

        $this->assertSame('70000.00', $order->total);
        $this->assertSame('70000.00', $item->product_price);
    }

    public function test_historical_snapshot_is_preserved_after_product_changes(): void
    {
        $product = $this->availableProduct([
            'name' => 'برغر كلاسيك',
            'price' => '50000.00',
        ]);
        $this->cart->add($product->id, 1);

        $this->post(route('checkout.store'), [])->assertRedirect();

        $product->update([
            'name' => 'برغر معدل',
            'price' => '99999.00',
        ]);

        $item = OrderItem::query()->first();
        $this->assertSame('برغر كلاسيك', $item->product_name);
        $this->assertSame('50000.00', $item->product_price);
    }

    public function test_unavailable_product_before_checkout_is_rejected(): void
    {
        $product = $this->availableProduct();
        $this->cart->add($product->id, 1);
        $product->update(['is_available' => false]);

        $this->from(route('checkout.index'))
            ->post(route('checkout.store'), [])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
        $this->assertFalse($this->cart->isEmpty());
    }

    public function test_deleted_product_before_checkout_is_handled(): void
    {
        $product = $this->availableProduct();
        $this->cart->add($product->id, 1);
        $product->delete();

        $this->from(route('checkout.index'))
            ->post(route('checkout.store'), [])
            ->assertSessionHasErrors('cart');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_order_creation_rolls_back_on_item_failure(): void
    {
        $product = $this->availableProduct();
        $this->cart->add($product->id, 1);

        OrderItem::creating(function () {
            throw new \RuntimeException('forced failure');
        });

        $this->from(route('checkout.index'))
            ->post(route('checkout.store'), [
                'notes' => 'اختبار',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('order');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertFalse($this->cart->isEmpty());
        $this->assertNull(Order::query()->value('whatsapp_sent_at'));
    }

    public function test_success_page_requires_signed_url(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'ORD-20260807-0099',
            'customer_name' => null,
            'customer_phone' => null,
            'total' => '50000.00',
            'subtotal' => '50000.00',
            'currency' => 'ل.س',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_name' => 'برغر كلاسيك',
            'product_price' => '50000.00',
            'quantity' => 1,
            'subtotal' => '50000.00',
        ]);

        $this->get(route('orders.success', ['order' => $order->order_number]))
            ->assertForbidden();

        $signed = URL::temporarySignedRoute(
            'orders.success',
            now()->addHour(),
            ['order' => $order->order_number],
        );

        $this->get($signed)
            ->assertOk()
            ->assertSee('تم استلام طلبك بنجاح', false)
            ->assertSee($order->order_number, false)
            ->assertDontSee('رقم الواتساب', false);
    }

    public function test_customer_cannot_access_another_order_with_guessable_id(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'ORD-20260807-0100',
            'customer_phone' => null,
        ]);

        $this->get('/order/success/'.$order->id)->assertStatus(403);
        $this->get('/order/success/'.$order->order_number)->assertStatus(403);
    }
}
