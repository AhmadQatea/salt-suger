@extends('layouts.admin')

@section('title', 'التصنيفات — ' . config('app.name'))

@section('content')
    <div class="admin-card">
        <div class="page-toolbar">
            <div>
                <h1>التصنيفات</h1>
                <p class="subtitle">إدارة تصنيفات قائمة Salt&Suger</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="btn-primary">+ إضافة تصنيف</a>
        </div>

        <form method="GET" action="{{ route('admin.categories.index') }}" class="filters">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="بحث باسم التصنيف..."
            >
            <button type="submit" class="btn-secondary">بحث</button>
            @if ($search !== '')
                <a href="{{ route('admin.categories.index') }}" class="btn-ghost">مسح</a>
            @endif
        </form>

        @if ($categories->isEmpty())
            <div class="empty-state">لا توجد تصنيفات مطابقة.</div>
        @else
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>الصورة</th>
                            <th>الاسم</th>
                            <th>عدد الأصناف</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr class="hover:bg-surface-container-low/80">
                                <td>
                                    @if ($category->imageUrl())
                                        <img src="{{ $category->imageUrl() }}" alt="" class="thumb">
                                    @else
                                        <span class="thumb-placeholder">بدون</span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="text-on-surface">{{ $category->name }}</strong>
                                    <div class="text-xs text-on-surface-variant">{{ $category->slug }}</div>
                                </td>
                                <td>{{ $category->products_count }}</td>
                                <td>
                                    <span @class(['badge-status', 'is-on' => $category->is_active, 'is-off' => ! $category->is_active])>
                                        {{ $category->is_active ? 'مفعّل' : 'معطّل' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn-ghost btn-sm">تعديل</a>

                                        <form method="POST" action="{{ route('admin.categories.toggle', $category) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-secondary btn-sm">
                                                {{ $category->is_active ? 'تعطيل' : 'تفعيل' }}
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا التصنيف؟');">
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
                {{ $categories->links() }}
            </div>
        @endif
    </div>
@endsection
