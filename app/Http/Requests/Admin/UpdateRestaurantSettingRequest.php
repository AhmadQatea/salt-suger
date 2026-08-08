<?php

namespace App\Http\Requests\Admin;

use App\Support\PhoneNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateRestaurantSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'whatsapp_enabled' => $this->boolean('whatsapp_enabled'),
            'whatsapp_number' => is_string($this->whatsapp_number)
                ? trim($this->whatsapp_number)
                : $this->whatsapp_number,
            'restaurant_name' => is_string($this->restaurant_name)
                ? trim($this->restaurant_name)
                : $this->restaurant_name,
            'description' => is_string($this->description)
                ? trim($this->description)
                : $this->description,
            'currency' => is_string($this->currency)
                ? trim($this->currency)
                : $this->currency,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'restaurant_name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'currency' => ['required', 'string', 'max:20'],
            'whatsapp_enabled' => ['required', 'boolean'],
            'whatsapp_number' => ['nullable', 'string', 'max:30'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $enabled = $this->boolean('whatsapp_enabled');
            $raw = $this->input('whatsapp_number');

            if (! filled($raw)) {
                if ($enabled) {
                    $validator->errors()->add(
                        'whatsapp_number',
                        'رقم واتساب المطعم مطلوب عند تفعيل الطلب عبر واتساب.'
                    );
                }

                return;
            }

            if (! PhoneNumber::isValid((string) $raw)) {
                $validator->errors()->add(
                    'whatsapp_number',
                    'يرجى إدخال رقم واتساب صحيح للمطعم.'
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'restaurant_name.required' => 'اسم المطعم مطلوب.',
            'currency.required' => 'العملة مطلوبة.',
        ];
    }

    /**
     * Normalized payload ready for persistence.
     *
     * @return array{
     *     restaurant_name: string,
     *     description: ?string,
     *     currency: string,
     *     whatsapp_enabled: bool,
     *     whatsapp_number: ?string
     * }
     */
    public function settingsPayload(): array
    {
        $rawPhone = $this->validated('whatsapp_number');
        $normalized = filled($rawPhone) ? PhoneNumber::normalize((string) $rawPhone) : null;

        return [
            'restaurant_name' => $this->validated('restaurant_name'),
            'description' => $this->validated('description'),
            'currency' => $this->validated('currency'),
            'whatsapp_enabled' => (bool) $this->validated('whatsapp_enabled'),
            'whatsapp_number' => $normalized,
        ];
    }
}
