<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesFakeImages;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use CreatesFakeImages;
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->admin()->create();
    }

    public function test_guest_cannot_access_categories(): void
    {
        $this->get(route('admin.categories.index'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_categories(): void
    {
        $category = Category::factory()->create(['name' => 'برغر']);

        $this->actingAs($this->admin())
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('برغر')
            ->assertSee('0');
    }

    public function test_admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), [
                'name' => 'برغر',
                'description' => 'تصنيف البرغر',
                'is_active' => 1,
                'sort_order' => 1,
            ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('categories', [
            'name' => 'برغر',
            'slug' => 'burger',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function test_admin_can_update_category_without_changing_slug(): void
    {
        $category = Category::factory()->create([
            'name' => 'برغر',
            'slug' => 'burger',
        ]);

        $this->actingAs($this->admin())
            ->put(route('admin.categories.update', $category), [
                'name' => 'برغر مميز',
                'description' => 'وصف محدث',
                'is_active' => 1,
                'sort_order' => 5,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category->refresh();

        $this->assertSame('برغر مميز', $category->name);
        $this->assertSame('burger', $category->slug);
        $this->assertSame(5, $category->sort_order);
    }

    public function test_admin_can_deactivate_category(): void
    {
        $category = Category::factory()->create(['is_active' => true]);

        $this->actingAs($this->admin())
            ->patch(route('admin.categories.toggle', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertFalse($category->fresh()->is_active);
    }

    public function test_admin_cannot_delete_category_containing_products(): void
    {
        Storage::fake('public');

        $category = Category::factory()->create([
            'image' => 'categories/keep-me.jpg',
        ]);
        Storage::disk('public')->put('categories/keep-me.jpg', 'fake-image');

        Product::factory()->create(['category_id' => $category->id]);

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertTrue(Storage::disk('public')->exists('categories/keep-me.jpg'));
    }

    public function test_admin_can_delete_empty_category_and_its_image(): void
    {
        Storage::fake('public');

        $category = Category::factory()->create([
            'image' => 'categories/remove-me.jpg',
        ]);
        Storage::disk('public')->put('categories/remove-me.jpg', 'fake-image');

        $this->actingAs($this->admin())
            ->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
        $this->assertFalse(Storage::disk('public')->exists('categories/remove-me.jpg'));
    }

    public function test_category_search_works(): void
    {
        Category::factory()->create(['name' => 'برغر']);
        Category::factory()->create(['name' => 'بيتزا']);

        $this->actingAs($this->admin())
            ->get(route('admin.categories.index', ['search' => 'برغر']))
            ->assertOk()
            ->assertSee('برغر')
            ->assertDontSee('بيتزا');
    }

    public function test_category_product_count_is_queryable(): void
    {
        $category = Category::factory()->create();
        Product::factory()->count(3)->create(['category_id' => $category->id]);

        $counted = Category::query()->withCount('products')->find($category->id);

        $this->assertSame(3, $counted->products_count);

        $this->actingAs($this->admin())
            ->get(route('admin.categories.index'))
            ->assertOk()
            ->assertSee('3');
    }

    public function test_category_image_upload_works(): void
    {
        Storage::fake('public');

        $file = $this->fakeImage('burgers.png');

        $this->actingAs($this->admin())
            ->post(route('admin.categories.store'), [
                'name' => 'برغر',
                'is_active' => 1,
                'sort_order' => 0,
                'image' => $file,
            ])
            ->assertRedirect(route('admin.categories.index'));

        $category = Category::query()->where('name', 'برغر')->first();

        $this->assertNotNull($category?->image);
        $this->assertStringStartsWith('categories/', $category->image);
        $this->assertTrue(Storage::disk('public')->exists($category->image));
    }
}
