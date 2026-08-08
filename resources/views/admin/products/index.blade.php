@extends('layouts.admin')

@section('title', 'الأصناف — ' . config('app.name'))

@section('content')
    <div class="admin-card">
        <div class="page-toolbar">
            <div>
                <h1>الأصناف</h1>
                <p class="subtitle">إدارة أصناف قائمة Salt&Suger</p>
            </div>
            <a href="{{ route('admin.products.create') }}" class="btn-primary">+ إضافة صنف</a>
        </div>

        <form method="GET" action="{{ route('admin.products.index') }}" class="filters">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="بحث باسم الصنف..."
            >

            <select name="category">
                <option value="">كل التصنيفات</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <select name="availability">
                <option value="">كل الحالات</option>
                <option value="available" @selected($availability === 'available')>متوفر</option>
                <option value="unavailable" @selected($availability === 'unavailable')>غير متوفر</option>
            </select>

            <button type="submit" class="btn-secondary">تصفية</button>
            @if ($search !== '' || filled($categoryId) || filled($availability))
                <a href="{{ route('admin.products.index') }}" class="btn-ghost">مسح</a>
            @endif
        </form>

        @if ($products->isEmpty())
            <div class="empty-state">لا توجد أصناف مطابقة.</div>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>الصورة</th>
                            <th>الاسم</th>
                            <th>التصنيف</th>
                            <th>السعر</th>
                            <th>الشارة</th>
                            <th>التوفر</th>
                            <th>الترتيب</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr class="hover:bg-surface-container-low/80">
                                <td>
                                    @if ($product->image)
                                        <img src="{{ asset('storage/'.$product->image) }}" alt="" class="thumb">
                                    @else
                                        <span class="thumb-placeholder">بدون</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-on-surface">{{ $product->name }}</strong>
                                    <div class="text-xs text-on-surface-variant">{{ $product->slug }}</div>
                                </td>
                                <td>{{ $product->category?->name ?? '—' }}</td>
                                <td>{{ $product->price }}</td>
                                <td>
                                    @if ($product->badge)
                                        <span class="badge-label">{{ $product->badge }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    <span @class(['badge-status', 'is-on' => $product->is_available, 'is-off' => ! $product->is_available])>
                                        {{ $product->is_available ? 'متوفر' : 'غير متوفر' }}
                                    </span>
                                </td>
                                <td>{{ $product->sort_order }}</td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="btn-ghost btn-sm">تعديل</a>

                                        <form method="POST" action="{{ route('admin.products.toggle', $product) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-secondary btn-sm">
                                                {{ $product->is_available ? 'تعطيل' : 'تفعيل' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا الصنف؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger btn-sm">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrap">
                {{ $products->links() }}
            </div>
        @endif
    </div>
@endsection
