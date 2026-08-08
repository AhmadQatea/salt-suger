<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\RestaurantSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class OrderService
{
    public function __construct(
        protected CartService $cart,
    ) {}

    /**
     * Create an order from the current session cart.
     *
     * Customer name/phone are intentionally not collected — WhatsApp itself
     * reveals the customer's identity when they send the message.
     *
     * @param  array{notes?: ?string}  $payload
     *
     * @throws ValidationException|Throwable
     */
    public function createFromCart(array $payload = []): Order
    {
        $validated = $this->cart->revalidate(failOnUnavailable: true);
        $settings = RestaurantSetting::cached();
        $currency = $settings->currency ?: 'ل.س';

        $notes = isset($payload['notes'])
            ? trim(strip_tags((string) $payload['notes']))
            : null;
        $notes = $notes === '' ? null : mb_substr($notes, 0, 1000);

        $subtotal = $validated['subtotal'];
        $total = $subtotal;

        try {
            $order = DB::transaction(function () use ($validated, $currency, $notes, $subtotal, $total) {
                $order = Order::query()->create([
                    'order_number' => $this->generateOrderNumber(),
                    'customer_name' => null,
                    'customer_phone' => null,
                    'notes' => $notes,
                    'subtotal' => $subtotal,
                    'total' => $total,
                    'currency' => $currency,
                    'status' => OrderStatus::Pending,
                ]);

                foreach ($validated['items'] as $item) {
                    OrderItem::query()->create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_name' => $item['name'],
                        'product_price' => $item['price'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['subtotal'],
                        'note' => $item['note'],
                    ]);
                }

                return $order->load('items');
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            report($exception);

            throw ValidationException::withMessages([
                'order' => 'تعذر إنشاء الطلب. يرجى المحاولة مرة أخرى.',
            ]);
        }

        $this->cart->clear();

        Cache::forget('admin.pending_orders_count');

        return $order;
    }

    protected function generateOrderNumber(): string
    {
        $prefix = 'ORD-'.now()->format('Ymd').'-';

        $latest = Order::query()
            ->where('order_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('order_number')
            ->value('order_number');

        $sequence = 1;

        if (is_string($latest) && preg_match('/-(\d+)$/', $latest, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }
}
