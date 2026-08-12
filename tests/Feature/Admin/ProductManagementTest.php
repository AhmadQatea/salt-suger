<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesFakeImages;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use CreatesFakeImages;
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_cannot_access_products(): void
    {
        $this->get(route('admin.products.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_products(): void
    {
        $product = Product::factory()->create(['name' => 'برغر كلاسيك']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index'))
            ->assertOk()
            ->assertSee('برغر كلاسيك');
    }

    public function test_admin_can_create_product(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [
                'name' => 'برغر كلاسيك',
                'category_id' => $category->id,
                'description' => 'برغر لحم وجبنة',
                'price' => '25000.00',
                'badge' => 'جديد',
                'is_available' => 1,
            ])
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('products', [
            'name' => 'برغر كلاسيك',
            'slug' => 'burger-classic',
            'category_id' => $category->id,
            'price' => '25000.00',
            'badge' => 'جديد',
            'is_available' => true,
        ]);
    }

    public function test_admin_can_update_product_without_changing_slug(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'برغر كلاسيك',
            'slug' => 'classic-burger',
            'price' => '25000.00',
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product), [
                'name' => 'برغر كلاسيك محدث',
                'category_id' => $category->id,
                'description' => 'وصف جديد',
                'price' => '27000.50',
                'badge' => 'الأكثر طلباً',
                'is_available' => 1,
            ])
            ->assertRedirect(route('admin.products.index'));

        $product->refresh();

        $this->assertSame('برغر كلاسيك محدث', $product->name);
        $this->assertSame('classic-burger', $product->slug);
        $this->assertSame('27000.50', $product->price);
    }

    public function test_admin_can_change_product_availability(): void
    {
        $product = Product::factory()->create(['is_available' => true]);

        $this->actingAs($this->admin())
            ->patch(route('admin.products.toggle', $product))
            ->assertRedirect(route('admin.products.index'));

        $this->assertFalse($product->fresh()->is_available);
    }

    public function test_admin_can_delete_product(): void
    {
        Storage::fake('public');

        $product = Product::factory()->create([
            'image' => 'products/old.jpg',
        ]);
        Storage::disk('public')->put('products/old.jpg', 'fake');

        $this->actingAs($this->admin())
            ->delete(route('admin.products.destroy', $product))
            ->assertRedirect(route('admin.products.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertFalse(Storage::disk('public')->exists('products/old.jpg'));
    }

    public function test_product_search_works(): void
    {
        Product::factory()->create(['name' => 'برغر كلاسيك']);
        Product::factory()->create(['name' => 'بيتزا مارغريتا']);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['search' => 'برغر']))
            ->assertOk()
            ->assertSee('برغر كلاسيك')
            ->assertDontSee('بيتزا مارغريتا');
    }

    public function test_product_category_filter_works(): void
    {
        $burgers = Category::factory()->create(['name' => 'برغر']);
        $pizza = Category::factory()->create(['name' => 'بيتزا']);

        Product::factory()->create(['name' => 'برغر كلاسيك', 'category_id' => $burgers->id]);
        Product::factory()->create(['name' => 'بيتزا مارغريتا', 'category_id' => $pizza->id]);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['category' => $burgers->id]))
            ->assertOk()
            ->assertSee('برغر كلاسيك')
            ->assertDontSee('بيتزا مارغريتا');
    }

    public function test_product_availability_filter_works(): void
    {
        Product::factory()->create(['name' => 'برغر متاح', 'is_available' => true]);
        Product::factory()->create(['name' => 'بيتزا موقوفة', 'is_available' => false]);

        $this->actingAs($this->admin())
            ->get(route('admin.products.index', ['availability' => 'unavailable']))
            ->assertOk()
            ->assertSee('بيتزا موقوفة')
            ->assertDontSee('برغر متاح');
    }

    public function test_product_pagination_preserves_query_string(): void
    {
        $category = Category::factory()->create();

        Product::factory()->count(13)->create([
            'category_id' => $category->id,
            'name' => 'صنف اختبار',
            'is_available' => true,
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.products.index', [
                'search' => 'صنف',
                'category' => $category->id,
                'availability' => 'available',
            ]));

        $response->assertOk();
        $response->assertSee('page=2');
        $response->assertSee('search=');
    }

    public function test_valid_product_image_can_be_uploaded(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();
        $file = $this->fakeImage('classic-burger.webp');

        $this->actingAs($this->admin())
            ->post(route('admin.products.store'), [
                'name' => 'برغر كلاسيك',
                'category_id' => $category->id,
                'price' => '25000.00',
                'is_available' => 1,
                'image' => $file,
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = Product::query()->where('name', 'برغر كلاسيك')->first();

        $this->assertNotNull($product?->image);
        $this->assertStringStartsWith('products/', $product->image);
        $this->assertTrue(Storage::disk('public')->exists($product->image));
    }

    public function test_invalid_product_image_is_rejected(): void
    {
        Storage::fake('public');
        $category = Category::factory()->create();
        $file = UploadedFile::fake()->create('notes.pdf', 100, 'application/pdf');

        $this->actingAs($this->admin())
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), [
                'name' => 'برغر كلاسيك',
                'category_id' => $category->id,
                'price' => '25000.00',
                'is_available' => 1,
                'image' => $file,
            ])
            ->assertRedirect(route('admin.products.create'))
            ->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('products', ['name' => 'برغر كلاسيك']);
    }

    public function test_replacing_product_image_removes_old_image(): void
    {
        Storage::fake('public');

        $oldPath = 'products/old-burger.jpg';
        Storage::disk('public')->put($oldPath, 'old-contents');

        $product = Product::factory()->create([
            'image' => $oldPath,
            'price' => '20000.00',
        ]);

        $newFile = $this->fakeImage('new-burger.png');

        $this->actingAs($this->admin())
            ->put(route('admin.products.update', $product), [
                'name' => $product->name,
                'category_id' => $product->category_id,
                'price' => '20000.00',
                'is_available' => 1,
                'image' => $newFile,
            ])
            ->assertRedirect(route('admin.products.index'));

        $product->refresh();

        $this->assertFalse(Storage::disk('public')->exists($oldPath));
        $this->assertTrue(Storage::disk('public')->exists($product->image));
        $this->assertNotSame($oldPath, $product->image);
    }
}
