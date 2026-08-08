@extends('layouts.admin')

@section('title', 'إعدادات المطعم — '.config('app.name'))

@section('content')
    <div class="admin-card">
        <div class="page-toolbar">
            <div>
                <h1>إعدادات المطعم</h1>
                <p class="subtitle">إدارة بيانات المطعم وإعدادات الطلب عبر واتساب</p>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.settings.update') }}" class="admin-form" novalidate>
            @csrf
            @method('PUT')

            <section class="detail-block" style="margin-bottom:1.25rem;max-width:640px;">
                <h2 style="margin:0 0 1rem;font-size:1.05rem;">معلومات المطعم</h2>

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

            <section class="detail-block" style="margin-bottom:1.25rem;max-width:640px;">
                <h2 style="margin:0 0 1rem;font-size:1.05rem;">WhatsApp</h2>

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
                    <p class="admin-sidebar-label" style="margin-top:.35rem;">سيُستخدم هذا الرقم كمستلم لطلبات العملاء عبر واتساب.</p>
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
