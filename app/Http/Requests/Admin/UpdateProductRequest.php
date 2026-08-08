<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'badge' => ['nullable', 'string', Rule::in(['جديد', 'الأكثر طلباً', 'عرض خاص'])],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
            'is_available' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم الصنف مطلوب.',
            'category_id.required' => 'التصنيف مطلوب.',
            'category_id.exists' => 'التصنيف المحدد غير موجود.',
            'price.required' => 'السعر مطلوب.',
            'price.numeric' => 'السعر يجب أن يكون رقماً.',
            'price.min' => 'السعر لا يمكن أن يكون سالباً.',
            'price.decimal' => 'السعر يجب أن يحتوي على منزلتين عشريتين كحد أقصى.',
            'badge.in' => 'الشارة المحددة غير صالحة.',
            'image.image' => 'يجب أن يكون الملف صورة.',
            'image.mimes' => 'صيغة الصورة يجب أن تكون jpeg أو jpg أو png أو webp.',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميغابايت.',
            'sort_order.min' => 'ترتيب الصنف لا يمكن أن يكون سالباً.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم الصنف',
            'category_id' => 'التصنيف',
            'description' => 'الوصف',
            'price' => 'السعر',
            'badge' => 'الشارة',
            'image' => 'صورة الصنف',
            'is_available' => 'التوفر',
            'sort_order' => 'الترتيب',
        ];
    }

    protected function prepareForValidation(): void
    {
        $badge = $this->input('badge');

        $this->merge([
            'is_available' => $this->boolean('is_available'),
            'remove_image' => $this->boolean('remove_image'),
            'sort_order' => $this->input('sort_order', 0),
            'badge' => $badge === '' ? null : $badge,
        ]);
    }
}
