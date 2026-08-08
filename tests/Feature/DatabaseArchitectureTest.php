<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\RestaurantSetting;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\RestaurantSettingSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseArchitectureTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_many_products_and_product_belongs_to_category(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->assertTrue($category->products->contains($product));
        $this->assertTrue($product->category->is($category));
    }

    public function test_product_cannot_reference_a_nonexistent_category(): void
    {
        $this->expectException(QueryException::class);

        Product::factory()->create([
            'category_id' => 999999,
        ]);
    }

    public function test_order_can_contain_multiple_order_items(): void
    {
        $order = Order::factory()->create();
        $productA = Product::factory()->create(['price' => '10000.00']);
        $productB = Product::factory()->create(['price' => '15000.50']);

        $itemA = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $productA->id,
            'product_name' => $productA->name,
            'product_price' => $productA->price,
            'quantity' => 2,
            'subtotal' => '20000.00',
        ]);

        $itemB = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $productB->id,
            'product_name' => $productB->name,
            'product_price' => $productB->price,
            'quantity' => 1,
            'subtotal' => '15000.50',
        ]);

        $order->refresh();

        $this->assertCount(2, $order->items);
        $this->assertTrue($itemA->order->is($order));
        $this->assertTrue($itemB->product->is($productB));
    }

    public function test_order_status_is_cast_to_order_status_enum(): void
    {
        $order = Order::factory()->create([
            'status' => OrderStatus::Pending,
        ]);

        $this->assertInstanceOf(OrderStatus::class, $order->status);
        $this->assertSame(OrderStatus::Pending, $order->status);

        $order->update(['status' => OrderStatus::Preparing]);
        $order->refresh();

        $this->assertSame(OrderStatus::Preparing, $order->status);
    }

    public function test_order_item_keeps_historical_product_name_and_price(): void
    {
        $product = Product::factory()->create([
            'name' => 'برغر كلاسيك',
            'price' => '25000.00',
        ]);

        $order = Order::factory()->create();

        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->price,
            'quantity' => 1,
            'subtotal' => '25000.00',
        ]);

        $product->update([
            'name' => 'برغر معدل',
            'price' => '30000.00',
        ]);

        $item->refresh();
        $product->refresh();

        $this->assertSame('برغر كلاسيك', $item->product_name);
        $this->assertSame('25000.00', $item->product_price);
        $this->assertSame('برغر معدل', $product->name);
        $this->assertSame('30000.00', $product->price);
    }

    public function test_deleting_product_nulls_order_item_product_id_but_keeps_history(): void
    {
        $product = Product::factory()->create([
            'name' => 'بيتزا مارغريتا',
            'price' => '30000.00',
        ]);

        $item = OrderItem::factory()->create([
            'product_id' => $product->id,
            'product_name' => 'بيتزا مارغريتا',
            'product_price' => '30000.00',
            'quantity' => 1,
            'subtotal' => '30000.00',
        ]);

        $product->delete();
        $item->refresh();

        $this->assertNull($item->product_id);
        $this->assertSame('بيتزا مارغريتا', $item->product_name);
        $this->assertSame('30000.00', $item->product_price);
    }

    public function test_restaurant_setting_seeder_creates_exactly_one_record(): void
    {
        $this->seed(RestaurantSettingSeeder::class);
        $this->seed(RestaurantSettingSeeder::class);

        $this->assertSame(1, RestaurantSetting::query()->count());
        $this->assertSame('Salt&Suger', RestaurantSetting::current()->restaurant_name);
        $this->assertSame('ل.س', RestaurantSetting::current()->currency);
    }

    public function test_monetary_columns_use_decimal_types_not_float(): void
    {
        foreach (['products.price', 'orders.subtotal', 'orders.total', 'order_items.product_price', 'order_items.subtotal'] as $column) {
            [$table, $name] = explode('.', $column);
            $type = Schema::getColumnType($table, $name);

            $this->assertContains(
                $type,
                ['decimal', 'numeric'],
                "Expected {$column} to be decimal/numeric, got {$type}."
            );
            $this->assertNotSame('float', $type);
            $this->assertNotSame('double', $type);
        }

        $product = Product::factory()->create(['price' => '19999.99']);
        $order = Order::factory()->create([
            'subtotal' => '19999.99',
            'total' => '19999.99',
        ]);

        $this->assertIsString($product->price);
        $this->assertIsString($order->total);
        $this->assertSame('19999.99', $product->price);
        $this->assertSame('19999.99', $order->total);
    }

    public function test_category_and_product_seeders_create_realistic_arabic_menu_data(): void
    {
        $this->seed([
            CategorySeeder::class,
            ProductSeeder::class,
        ]);

        $this->assertSame(8, Category::query()->count());
        $this->assertTrue(Category::query()->where('slug', 'burger')->exists());
        $this->assertTrue(Product::query()->where('slug', 'classic-burger')->exists());
        $this->assertGreaterThan(0, Product::query()->where('category_id', Category::query()->where('slug', 'burger')->value('id'))->count());
    }

    public function test_admin_authentication_still_works_after_schema_changes(): void
    {
        $this->seed(AdminSeeder::class);

        $admin = User::query()->where('email', 'admin@gmail.com')->first();

        $this->assertNotNull($admin);
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue(Hash::check('admin123', $admin->password));

        $this->post(route('admin.login.store'), [
            'email' => 'admin@gmail.com',
            'password' => 'admin123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }
}
