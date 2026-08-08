@extends('layouts.admin')

@section('title', 'إعدادات المطعم — '.config('app.name'))

@section('content')
    <div class="admin-card">
        <div class="page-toolbar">
            <div>
                <h1>إعدادات المطعم</h1>
                <p class="subtitle">إدارة بيانات المطعم، صورة الغلاف، وواتساب</p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('admin.settings.update') }}"
            class="admin-form"
            enctype="multipart/form-data"
            novalidate
        >
            @csrf
            @method('PUT')

            <section class="detail-block mb-5 max-w-xl">
                <h2 class="mb-4 text-base font-semibold text-on-surface">معلومات المطعم</h2>

                <div>
                    <label for="restaurant_name">اسم المطعم</label>
                    <input
                        id="restaurant_name"
                        type="text"
                        name="restaurant_name"
                        value="{{ old('restaurant_name', $settings->restaurant_name) }}"
                        required
                        maxlength="120"
                    >
                    @error('restaurant_name') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description">وصف المطعم</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="4"
                        maxlength="1000"
                    >{{ old('description', $settings->description) }}</textarea>
                    @error('description') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="currency">العملة</label>
                    <input
                        id="currency"
                        type="text"
                        name="currency"
                        value="{{ old('currency', $settings->currency) }}"
                        required
                        maxlength="20"
                    >
                    @error('currency') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </section>

            <section class="detail-block mb-5 max-w-xl">
                <h2 class="mb-4 text-base font-semibold text-on-surface">إعدادات المظهر</h2>

                <div>
                    <label for="hero_image">صورة الغلاف الرئيسية</label>
                    <p class="admin-sidebar-label mb-3">JPG / PNG / WEBP — الحد الأقصى 5 ميغابايت</p>

                    @if ($heroPreviewUrl)
                        <div class="mb-3 overflow-hidden rounded-lg border border-outline-variant">
                            <img
                                src="{{ $heroPreviewUrl }}"
                                alt="معاينة صورة الغلاف"
                                class="h-40 w-full object-cover"
                                id="hero-image-preview"
                            >
                        </div>
                        <label class="admin-checkbox mb-3" for="remove_hero_image">
                            <input
                                id="remove_hero_image"
                                type="checkbox"
                                name="remove_hero_image"
                                value="1"
                                @checked(old('remove_hero_image'))
                            >
                            <span>إزالة صورة الغلاف الحالية</span>
                        </label>
                    @else
                        <img
                            id="hero-image-preview"
                            src=""
                            alt=""
                            class="image-preview mb-3 h-40 w-full object-cover"
                        >
                    @endif

                    <input
                        id="hero_image"
                        type="file"
                        name="hero_image"
                        accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp"
                    >
                    @error('hero_image') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </section>

            <section class="detail-block mb-5 max-w-xl">
                <h2 class="mb-4 text-base font-semibold text-on-surface">WhatsApp</h2>

                <div>
                    <label for="whatsapp_number">رقم واتساب المطعم</label>
                    <input
                        id="whatsapp_number"
                        type="tel"
                        name="whatsapp_number"
                        value="{{ old('whatsapp_number', $settings->whatsapp_number) }}"
                        dir="ltr"
                        class="text-left"
                        placeholder="+9639XXXXXXXX أو 09XXXXXXXX"
                        maxlength="30"
                    >
                    <p class="admin-sidebar-label mt-1">سيُستخدم هذا الرقم كمستلم لطلبات العملاء عبر واتساب.</p>
                    @error('whatsapp_number') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="admin-checkbox" for="whatsapp_enabled">
                        <input
                            id="whatsapp_enabled"
                            type="checkbox"
                            name="whatsapp_enabled"
                            value="1"
                            @checked(old('whatsapp_enabled', $settings->whatsapp_enabled))
                        >
                        <span>تفعيل الطلب عبر واتساب</span>
                    </label>
                    @error('whatsapp_enabled') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </section>

            <button type="submit" class="btn-primary">حفظ الإعدادات</button>
        </form>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const input = document.getElementById('hero_image');
    const preview = document.getElementById('hero-image-preview');
    if (!input || !preview) return;

    input.addEventListener('change', () => {
        const file = input.files && input.files[0];
        if (!file) return;
        const url = URL.createObjectURL(file);
        preview.src = url;
        preview.classList.remove('image-preview');
        preview.classList.add('mb-3', 'h-40', 'w-full', 'object-cover', 'rounded-lg', 'border', 'border-outline-variant');
        preview.alt = 'معاينة صورة الغلاف';
    });
})();
</script>
@endpush
