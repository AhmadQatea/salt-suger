<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckoutRequest extends FormRequest
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
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'notes.max' => 'ملاحظات الطلب طويلة جداً.',
        ];
    }

    /**
     * Payload for order creation — no customer contact data collected.
     *
     * @return array{notes: ?string}
     */
    public function orderPayload(): array
    {
        $notes = $this->input('notes');
        $notes = is_string($notes) ? trim(strip_tags($notes)) : null;

        return [
            'notes' => ($notes === null || $notes === '') ? null : mb_substr($notes, 0, 1000),
        ];
    }
}
