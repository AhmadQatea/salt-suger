<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\ImageService;
use App\Services\SlugGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected ImageService $images,
        protected SlugGenerator $slugs,
    ) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $categories = Category::query()
            ->withCount('products')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%');
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('admin.categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['slug'] = $this->slugs->unique($request->string('name')->toString(), 'categories');

        if ($request->hasFile('image')) {
            $data['image'] = $this->images->store($request->file('image'), 'categories');
        }

        Category::query()->create($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'تمت إضافة التصنيف بنجاح.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->safe()->except(['image', 'remove_image']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->images->replace(
                $request->file('image'),
                'categories',
                $category->image,
            );
        } elseif ($request->boolean('remove_image') && $category->image) {
            $this->images->delete($category->image);
            $data['image'] = null;
        }

        $category->update($data);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'تم تحديث التصنيف بنجاح.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'لا يمكن حذف هذا التصنيف لأنه يحتوي على أصناف مرتبطة به.');
        }

        $imagePath = $category->image;

        try {
            $category->delete();
        } catch (QueryException) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', 'لا يمكن حذف هذا التصنيف لأنه يحتوي على أصناف مرتبطة به.');
        }

        $this->images->delete($imagePath);

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'تم حذف التصنيف بنجاح.');
    }

    public function toggle(Category $category): RedirectResponse
    {
        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        $message = $category->is_active
            ? 'تم تفعيل التصنيف بنجاح.'
            : 'تم تعطيل التصنيف بنجاح.';

        return redirect()
            ->route('admin.categories.index')
            ->with('status', $message);
    }
}
