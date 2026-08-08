<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Services\CartService;
use App\Services\WhatsAppService;
use App\Support\PhoneNumber;
use App\Support\WhatsAppMessageBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class WhatsAppOrderTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $cart;

    protected WhatsAppService $whatsapp;

    protected WhatsAppMessageBuilder $messages;

    protected RestaurantSetting $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cart = app(CartService::class);
        $this->whatsapp = app(WhatsAppService::class);
        $this->messages = app(WhatsAppMessageBuilder::class);
        $this->settings = RestaurantSetting::factory()->create([
            'restaurant_name' => 'Salt&Suger',
            'currency' => 'ل.س',
            'whatsapp_enabled' => true,
            'whatsapp_number' => '0911111111',
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
            'price' => '85000.00',
            'is_available' => true,
        ], $overrides));
    }

    protected function makeOrder(array $orderOverrides = [], array $itemOverrides = []): Order
    {
        $order = Order::factory()->create(array_merge([
            'order_number' => 'ORD-20260807-0001',
            'customer_name' => 'أحمد محمد',
            'customer_phone' => '+963912345678',
            'notes' => 'يرجى تجهيز الطلب بسرعة',
            'subtotal' => '195000.00',
            'total' => '195000.00',
            'currency' => 'ل.س',
        ], $orderOverrides));

        OrderItem::factory()->create(array_merge([
            'order_id' => $order->id,
            'product_name' => 'برغر كلاسيك',
            'product_price' => '85000.00',
            'quantity' => 2,
            'subtotal' => '170000.00',
            'note' => 'بدون بصل',
        ], $itemOverrides));

        return $order->fresh('items');
    }

    public function test_valid_restaurant_number_generates_whatsapp_url(): void
    {
        $order = $this->makeOrder();
        $url = $this->whatsapp->orderUrl($order, $this->settings->fresh());

        $this->assertNotNull($url);
        $this->assertStringStartsWith('https://wa.me/963911111111?text=', $url);
        $this->assertStringContainsString(rawurlencode('طلب جديد'), $url);
    }

    public function test_message_contains_arabic_content_and_order_data(): void
    {
        $order = $this->makeOrder();
        $message = $this->messages->build($order, $this->settings);

        $this->assertStringContainsString('طلب جديد — Salt&Suger', $message);
        $this->assertStringContainsString('#ORD-20260807-0001', $message);
        $this->assertStringContainsString('حالة الطلب:', $message);
        $this->assertStringContainsString('قيد التأكيد', $message);
        $this->assertStringContainsString('برغر كلاسيك', $message);
        $this->assertStringContainsString('بدون بصل', $message);
        $this->assertStringContainsString('يرجى تجهيز الطلب بسرعة', $message);
        $this->assertStringContainsString('195,000 ل.س', $message);
        $this->assertStringNotContainsString('أحمد محمد', $message);
        $this->assertStringNotContainsString('+963912345678', $message);
        $this->assertStringNotContainsString('بيانات العميل', $message);
        $this->assertStringNotContainsString('الاسم:', $message);
    }

    public function test_url_encoding_preserves_arabic_and_special_characters(): void
    {
        $order = $this->makeOrder([
            'notes' => "ملاحظة + خاصة\nسطر ثاني ❤️",
        ]);

        $url = $this->whatsapp->orderUrl($order);
        $this->assertNotNull($url);

        $query = parse_url($url, PHP_URL_QUERY);
        $this->assertIsString($query);
        parse_str($query, $params);

        $this->assertArrayHasKey('text', $params);
        $this->assertStringContainsString('ملاحظة + خاصة', $params['text']);
        $this->assertStringContainsString('سطر ثاني', $params['text']);
        $this->assertStringContainsString('❤️', $params['text']);
    }

    public function test_message_uses_order_item_snapshot_not_current_product(): void
    {
        $product = $this->availableProduct([
            'name' => 'برغر كلاسيك',
            'price' => '85000.00',
        ]);

        $order = $this->makeOrder([], [
            'product_id' => $product->id,
            'product_name' => 'برغر كلاسيك',
            'product_price' => '85000.00',
            'quantity' => 1,
            'subtotal' => '85000.00',
            'note' => null,
        ]);

        $product->update([
            'name' => 'برغر معدل',
            'price' => '1.00',
        ]);

        $message = $this->messages->build($order->fresh('items'));

        $this->assertStringContainsString('برغر كلاسيك', $message);
        $this->assertStringContainsString('85,000 ل.س', $message);
        $this->assertStringNotContainsString('برغر معدل', $message);
    }

    public function test_conditional_fields_are_omitted_when_empty(): void
    {
        $order = $this->makeOrder([
            'customer_name' => null,
            'customer_phone' => null,
            'notes' => null,
        ], [
            'note' => null,
        ]);

        $message = $this->messages->build($order);

        $this->assertStringNotContainsString('الاسم:', $message);
        $this->assertStringNotContainsString('ملاحظات الطلب', $message);
        $this->assertStringNotContainsString('ملاحظة:', $message);
        $this->assertStringNotContainsString('بيانات العميل', $message);
        $this->assertStringNotContainsString('واتساب:', $message);
        $this->assertStringContainsString('تفاصيل الطلب', $message);
    }

    public function test_message_uses_order_currency_and_total(): void
    {
        $order = $this->makeOrder([
            'currency' => 'ل.س',
            'total' => '195000.00',
        ]);

        $this->settings->update(['currency' => 'USD']);

        $message = $this->messages->build($order->fresh('items'));

        $this->assertStringContainsString('ل.س', $message);
        $this->assertStringContainsString('195,000 ل.س', $message);
        $this->assertStringNotContainsString('USD', $message);
    }

    public function test_whatsapp_disabled_blocks_checkout_without_creating_order(): void
    {
        $this->settings->update(['whatsapp_enabled' => false]);

        $product = $this->availableProduct();
        $this->cart->add($product->id, 1);

        $this->from(route('checkout.index'))
            ->post(route('checkout.store'), [])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('whatsapp');

        $this->assertDatabaseCount('orders', 0);
        $this->assertFalse($this->cart->isEmpty());
    }

    public function test_missing_restaurant_number_blocks_checkout(): void
    {
        $this->settings->update([
            'whatsapp_enabled' => true,
            'whatsapp_number' => null,
        ]);

        $product = $this->availableProduct();
        $this->cart->add($product->id, 1);

        $this->from(route('checkout.index'))
            ->post(route('checkout.store'), [])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('whatsapp');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_invalid_restaurant_number_blocks_checkout(): void
    {
        $this->settings->update([
            'whatsapp_enabled' => true,
            'whatsapp_number' => 'not-a-phone',
        ]);

        $product = $this->availableProduct();
        $this->cart->add($product->id, 1);

        $this->from(route('checkout.index'))
            ->post(route('checkout.store'), [])
            ->assertSessionHasErrors('whatsapp');

        $this->assertDatabaseCount('orders', 0);
        $this->assertNull($this->whatsapp->orderUrl($this->makeOrder()));
    }

    public function test_checkout_redirects_to_whatsapp_and_sets_timestamp(): void
    {
        $product = $this->availableProduct(['price' => '25000.00', 'name' => 'بطاطا مقلية']);
        $this->cart->add($product->id, 1, 'صوص إضافي');

        $response = $this->post(route('checkout.store'), [
            'notes' => 'سريع من فضلك',
        ]);

        $order = Order::query()->first();
        $this->assertNotNull($order);

        // whatsapp_sent_at = redirect preparation time only — NOT message delivery.
        $this->assertNotNull($order->whatsapp_sent_at);
        $this->assertNull($order->customer_phone);
        $this->assertNull($order->customer_name);
        $this->assertTrue($this->cart->isEmpty());

        $location = $response->headers->get('Location');
        $this->assertNotNull($location);
        $this->assertStringStartsWith('https://wa.me/963911111111?text=', $location);

        $query = parse_url($location, PHP_URL_QUERY);
        parse_str((string) $query, $params);
        $this->assertStringContainsString('بطاطا مقلية', $params['text']);
        $this->assertStringContainsString('صوص إضافي', $params['text']);
        $this->assertStringContainsString('سريع من فضلك', $params['text']);
        $this->assertStringContainsString($order->order_number, $params['text']);
        $this->assertStringContainsString('قيد التأكيد', $params['text']);
        $this->assertStringNotContainsString('بيانات العميل', $params['text']);
    }

    public function test_db_failure_does_not_redirect_to_whatsapp(): void
    {
        $product = $this->availableProduct();
        $this->cart->add($product->id, 1);

        OrderItem::creating(function () {
            throw new \RuntimeException('forced failure');
        });

        $this->from(route('checkout.index'))
            ->post(route('checkout.store'), [])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('order');

        $this->assertDatabaseCount('orders', 0);
        $this->assertFalse($this->cart->isEmpty());
    }

    public function test_restaurant_whatsapp_number_is_normalized(): void
    {
        $this->settings->update(['whatsapp_number' => '0998765432']);

        $order = $this->makeOrder();
        $url = $this->whatsapp->orderUrl($order, $this->settings->fresh());

        $this->assertSame('+963998765432', PhoneNumber::normalize('0998765432'));
        $this->assertStringStartsWith('https://wa.me/963998765432?text=', (string) $url);
    }

    public function test_whatsapp_fallback_page_keeps_order_when_url_unavailable_after_create(): void
    {
        // Simulate post-create URL failure by disabling WhatsApp after order would be created
        // via a partial mock of orderUrl path: create order manually then hit success fallback.
        $order = $this->makeOrder([
            'whatsapp_sent_at' => null,
        ]);

        $signed = URL::temporarySignedRoute(
            'orders.success',
            now()->addHour(),
            ['order' => $order->order_number],
        );

        $this->withSession(['whatsapp_fallback' => true])
            ->get($signed)
            ->assertOk()
            ->assertSee('تم حفظ طلبك بنجاح', false)
            ->assertSee('تعذر فتح واتساب حالياً', false)
            ->assertSee($order->order_number, false);

        $this->assertNull($order->fresh()->whatsapp_sent_at);
    }

    public function test_repeated_checkout_with_empty_cart_does_not_create_second_order(): void
    {
        $product = $this->availableProduct();
        $this->cart->add($product->id, 1);

        $this->post(route('checkout.store'), [])->assertRedirect();

        $this->assertDatabaseCount('orders', 1);

        $this->post(route('checkout.store'), [])->assertRedirect(route('cart.index'));

        $this->assertDatabaseCount('orders', 1);
    }
}
