@extends('layouts.admin')

@section('title', 'تعديل تصنيف — ' . config('app.name'))

@section('content')
    <div class="admin-card">
        <div class="page-toolbar">
            <div>
                <h1>تعديل التصنيف</h1>
                <p class="subtitle">{{ $category->name }}</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="btn-ghost">رجوع</a>
        </div>

        <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="admin-form" novalidate>
            @csrf
            @method('PUT')

            <div>
                <label for="name">اسم التصنيف</label>
                <input id="name" type="text" name="name" value="{{ old('name', $category->name) }}" required>
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description">وصف التصنيف</label>
                <textarea id="description" name="description">{{ old('description', $category->description) }}</textarea>
                @error('description') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="image">صورة التصنيف</label>
                @if ($category->image)
                    <div class="mb-3">
                        <img src="{{ asset('storage/'.$category->image) }}" alt="" class="thumb h-30 w-30">
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

            <label class="admin-checkbox" for="is_active">
                <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))>
                <span>حالة التصنيف (مفعّل)</span>
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
