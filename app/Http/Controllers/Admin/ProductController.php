<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageService;
use App\Services\SlugGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        protected ImageService $images,
        protected SlugGenerator $slugs,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));
        $categoryId = $request->query('category');
        $availability = $request->query('availability');

        $products = Product::query()
            ->with('category')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->when(filled($categoryId), function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($availability === 'available', function ($query) {
                $query->where('is_available', true);
            })
            ->when($availability === 'unavailable', function ($query) {
                $query->where('is_available', false);
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.products.index', compact(
            'products',
            'categories',
            'search',
            'categoryId',
            'availability',
        ));
    }

    public function create(): View
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['slug'] = $this->slugs->unique($request->string('name')->toString(), 'products');
        $data['price'] = bcadd((string) $request->input('price'), '0', 2);

        if ($request->hasFile('image')) {
            $data['image'] = $this->images->store($request->file('image'), 'products');
        }

        Product::query()->create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'تمت إضافة الصنف بنجاح.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);
        $data['price'] = bcadd((string) $request->input('price'), '0', 2);

        DB::transaction(function () use ($request, $product, &$data) {
            if ($request->hasFile('image')) {
                $data['image'] = $this->images->replace(
                    $request->file('image'),
                    'products',
                    $product->image,
                );
            } elseif ($request->boolean('remove_image') && $product->image) {
                $this->images->delete($product->image);
                $data['image'] = null;
            }

            $product->update($data);
        });

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'تم تحديث الصنف بنجاح.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $imagePath = $product->image;

        $product->delete();
        $this->images->delete($imagePath);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'تم حذف الصنف بنجاح.');
    }

    public function toggle(Product $product): RedirectResponse
    {
        $product->update([
            'is_available' => ! $product->is_available,
        ]);

        $message = $product->is_available
            ? 'تم تفعيل الصنف بنجاح.'
            : 'تم تعطيل الصنف بنجاح.';

        return redirect()
            ->route('admin.products.index')
            ->with('status', $message);
    }
}
