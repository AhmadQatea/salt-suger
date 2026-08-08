<?php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
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
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:99'],
            'note' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'quantity.integer' => 'الكمية غير صالحة.',
            'quantity.min' => 'الكمية يجب أن تكون 1 على الأقل.',
            'quantity.max' => 'الكمية يجب ألا تتجاوز 99.',
            'note.max' => 'الملاحظة طويلة جداً.',
        ];
    }
}
