@extends('layouts.admin')

@section('title', 'تعديل صنف — ' . config('app.name'))

@section('content')
    <div class="admin-card">
        <div class="page-toolbar">
            <div>
                <h1>تعديل الصنف</h1>
                <p class="subtitle">{{ $product->name }}</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="btn-ghost">رجوع</a>
        </div>

        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="admin-form" novalidate>
            @csrf
            @method('PUT')

            <div>
                <label for="name">اسم الصنف</label>
                <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}" required>
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="category_id">التصنيف</label>
                <select id="category_id" name="category_id" required>
                    <option value="">اختر التصنيف</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id) === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description">الوصف</label>
                <textarea id="description" name="description">{{ old('description', $product->description) }}</textarea>
                @error('description') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="price">السعر</label>
                <input id="price" type="number" name="price" min="0" step="0.01" value="{{ old('price', $product->price) }}" required>
                @error('price') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="badge">الشارة</label>
                <select id="badge" name="badge">
                    <option value="">بدون شارة</option>
                    @foreach (['جديد', 'الأكثر طلباً', 'عرض خاص'] as $badge)
                        <option value="{{ $badge }}" @selected(old('badge', $product->badge) === $badge)>{{ $badge }}</option>
                    @endforeach
                </select>
                @error('badge') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="image">صورة الصنف</label>
                @if ($product->image)
                    <div class="mb-3">
                        <img src="{{ asset('storage/'.$product->image) }}" alt="" class="thumb h-30 w-30">
                    </div>
                    <label class="admin-checkbox" for="remove_image">
                        <input id="remove_image" type="checkbox" name="remove_image" value="1">
                        <span>حذف الصورة الحالية</span>
                    </label>
                @endif
                <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-preview-target="image-preview" class="mt-3">
                <img id="image-preview" class="image-preview" alt="معاينة الصورة">
                @error('image') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="sort_order">الترتيب</label>
                <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', $product->sort_order) }}">
                @error('sort_order') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <label class="admin-checkbox" for="is_available">
                <input id="is_available" type="checkbox" name="is_available" value="1" @checked(old('is_available', $product->is_available))>
                <span>متوفر للطلب</span>
            </label>

            <button type="submit" class="btn-primary">حفظ التغييرات</button>
        </form>
    </div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('[data-preview-target]').forEach((input) => {
    input.addEventListener('change', () => {
        const preview = document.getElementById(input.dataset.previewTarget);
        const file = input.files?.[0];
        if (!preview) return;
        if (!file) {
            preview.classList.remove('is-visible');
            preview.removeAttribute('src');
            return;
        }
        preview.src = URL.createObjectURL(file);
        preview.classList.add('is-visible');
    });
});
</script>
@endpush
