@extends('layouts.admin')

@section('title', 'إضافة تصنيف — ' . config('app.name'))

@section('content')
    <div class="admin-card">
        <div class="page-toolbar">
            <div>
                <h1>إضافة تصنيف</h1>
                <p class="subtitle">إنشاء تصنيف جديد للقائمة</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="btn-ghost">رجوع</a>
        </div>

        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data" class="admin-form" novalidate>
            @csrf

            <div>
                <label for="name">اسم التصنيف</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required>
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="description">وصف التصنيف</label>
                <textarea id="description" name="description">{{ old('description') }}</textarea>
                @error('description') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="image">صورة التصنيف</label>
                <input id="image" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-preview-target="image-preview">
                <img id="image-preview" class="image-preview" alt="معاينة الصورة">
                @error('image') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="sort_order">ترتيب التصنيف</label>
                <input id="sort_order" type="number" name="sort_order" min="0" value="{{ old('sort_order', 0) }}">
                @error('sort_order') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <label class="admin-checkbox" for="is_active">
                <input id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                <span>حالة التصنيف (مفعّل)</span>
            </label>

            <button type="submit" class="btn-primary">حفظ التصنيف</button>
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
