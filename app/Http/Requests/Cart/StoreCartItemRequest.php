<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class StoreCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'الصنف مطلوب.',
            'product_id.exists' => 'هذا الصنف غير متاح حالياً.',
            'quantity.integer' => 'الكمية غير صالحة.',
            'quantity.min' => 'الكمية يجب أن تكون 1 على الأقل.',
            'quantity.max' => 'الكمية يجب ألا تتجاوز 99.',
            'note.max' => 'الملاحظة طويلة جداً.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'quantity' => $this->input('quantity', 1),
            'note' => $this->filled('note') ? $this->input('note') : null,
        ]);
    }
}
