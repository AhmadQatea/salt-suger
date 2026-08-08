<?php

namespace App\Support;

use App\Models\Order;
use App\Models\RestaurantSetting;

class WhatsAppMessageBuilder
{
    /**
     * Build a clean Arabic WhatsApp message from a persisted order snapshot.
     *
     * Customer name/phone are intentionally omitted — WhatsApp reveals the
     * sender identity when the customer presses Send.
     */
    public function build(Order $order, ?RestaurantSetting $settings = null): string
    {
        $order->loadMissing('items');

        $settings ??= RestaurantSetting::cached();
        $restaurantName = $settings->restaurant_name ?: config('app.name', 'Salt&Suger');
        $currency = $order->currency ?: 'ل.س';
        $statusLabel = $order->status?->label() ?: 'قيد التأكيد';

        $lines = [
            "🍔 *طلب جديد — {$restaurantName}*",
            '',
            '━━━━━━━━━━━━━━',
            '',
            "📋 *رقم الطلب:* #{$order->order_number}",
            "📌 *حالة الطلب:* {$statusLabel}",
            '',
            '━━━━━━━━━━━━━━',
            '',
            '🛒 *تفاصيل الطلب*',
            '',
        ];

        foreach ($order->items as $index => $item) {
            $number = $index + 1;
            $lines[] = "{$number}. *{$item->product_name}*";
            $lines[] = "الكمية: {$item->quantity}";
            $lines[] = 'السعر: '.Money::format($item->product_price, $currency);
            $lines[] = 'المجموع: '.Money::format($item->subtotal, $currency);

            if (filled($item->note)) {
                $lines[] = 'ملاحظة:';
                $lines[] = $item->note;
            }

            $lines[] = '';
        }

        $lines[] = '━━━━━━━━━━━━━━';
        $lines[] = '';

        if (filled($order->notes)) {
            $lines[] = '📝 *ملاحظات الطلب:*';
            $lines[] = $order->notes;
            $lines[] = '';
        }

        $lines[] = '💰 *الإجمالي:*';
        $lines[] = Money::format($order->total, $currency);
        $lines[] = '';
        $lines[] = '━━━━━━━━━━━━━━';
        $lines[] = '';
        $lines[] = "شكراً لاختياركم {$restaurantName} ❤️";

        return implode("\n", $lines);
    }
}
