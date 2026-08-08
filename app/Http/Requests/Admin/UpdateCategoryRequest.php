<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remove_image' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم التصنيف مطلوب.',
            'name.max' => 'اسم التصنيف طويل جداً.',
            'image.image' => 'يجب أن يكون الملف صورة.',
            'image.mimes' => 'صيغة الصورة يجب أن تكون jpeg أو jpg أو png أو webp.',
            'image.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميغابايت.',
            'sort_order.integer' => 'ترتيب التصنيف يجب أن يكون رقماً صحيحاً.',
            'sort_order.min' => 'ترتيب التصنيف لا يمكن أن يكون سالباً.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'اسم التصنيف',
            'description' => 'وصف التصنيف',
            'image' => 'صورة التصنيف',
            'is_active' => 'حالة التصنيف',
            'sort_order' => 'ترتيب التصنيف',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'remove_image' => $this->boolean('remove_image'),
            'sort_order' => $this->input('sort_order', 0),
        ]);
    }
}
