<?php

namespace App\Services;

use App\Models\Order;
use App\Models\RestaurantSetting;
use App\Support\PhoneNumber;
use App\Support\WhatsAppMessageBuilder;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WhatsAppService
{
    public function __construct(
        protected WhatsAppMessageBuilder $messages,
    ) {}

    /**
     * Ensure WhatsApp ordering is enabled and the restaurant number is valid.
     *
     * Prefer calling this before order creation so customers are not left
     * with an order they cannot send.
     *
     * @throws ValidationException
     */
    public function assertOrderingAvailable(?RestaurantSetting $settings = null): void
    {
        $settings ??= RestaurantSetting::cached();

        if (! $settings || ! $settings->whatsapp_enabled) {
            throw ValidationException::withMessages([
                'whatsapp' => 'الطلب عبر واتساب غير متاح حالياً.',
            ]);
        }

        if ($this->restaurantRecipient($settings) === null) {
            Log::warning('WhatsApp ordering is enabled but the restaurant WhatsApp number is missing or invalid.');

            throw ValidationException::withMessages([
                'whatsapp' => 'تعذر تجهيز الطلب حالياً، يرجى المحاولة لاحقاً.',
            ]);
        }
    }

    /**
     * Whether WhatsApp click-to-chat ordering can be offered.
     */
    public function isOrderingAvailable(?RestaurantSetting $settings = null): bool
    {
        $settings ??= RestaurantSetting::cached();

        return (bool) $settings
            && $settings->whatsapp_enabled
            && $this->restaurantRecipient($settings) !== null;
    }

    /**
     * Normalize the restaurant WhatsApp recipient from settings.
     */
    public function restaurantRecipient(?RestaurantSetting $settings = null): ?string
    {
        $settings ??= RestaurantSetting::cached();

        if (! $settings) {
            return null;
        }

        $normalized = PhoneNumber::normalize($settings->whatsapp_number);

        return PhoneNumber::isValid($normalized) ? $normalized : null;
    }

    /**
     * Build a WhatsApp click-to-chat URL for a persisted order.
     *
     * Returns null when WhatsApp is unavailable or the recipient is invalid.
     * Never accepts a customer-chosen recipient — always uses restaurant settings.
     */
    public function orderUrl(Order $order, ?RestaurantSetting $settings = null): ?string
    {
        $settings ??= RestaurantSetting::cached();

        if (! $settings || ! $settings->whatsapp_enabled) {
            return null;
        }

        $recipient = $this->restaurantRecipient($settings);

        if ($recipient === null) {
            return null;
        }

        $message = $this->messages->build($order, $settings);
        $digits = ltrim($recipient, '+');

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }

    /**
     * Record that the customer was redirected to WhatsApp click-to-chat.
     *
     * IMPORTANT: This does NOT mean the restaurant received the message.
     * Opening wa.me only starts WhatsApp with a pre-filled draft; the customer
     * still has to tap send. whatsapp_sent_at = redirect time, not delivery.
     */
    public function markRedirected(Order $order): void
    {
        $order->forceFill([
            'whatsapp_sent_at' => now(),
        ])->save();
    }
}
