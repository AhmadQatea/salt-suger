<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\StoreCheckoutRequest;
use App\Models\Order;
use App\Models\RestaurantSetting;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected CartService $cart,
        protected OrderService $orders,
        protected WhatsAppService $whatsapp,
    ) {}

    public function checkout(): View|RedirectResponse
    {
        $this->cart->revalidate(failOnUnavailable: false);

        if ($this->cart->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'سلتك فارغة.');
        }

        $settings = $this->settings();

        return view('checkout.index', [
            'settings' => $settings,
            'logoUrl' => $this->logoUrl($settings),
            'items' => collect($this->cart->all())->values(),
            'subtotal' => $this->cart->subtotal(),
            'currency' => $settings->currency ?: 'ل.س',
            'whatsappAvailable' => $this->whatsapp->isOrderingAvailable($settings),
        ]);
    }

    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        if ($this->cart->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'سلتك فارغة.');
        }

        // Block checkout when WhatsApp cannot be used — avoid orphaned orders.
        $this->whatsapp->assertOrderingAvailable();

        $order = $this->orders->createFromCart($request->orderPayload());

        $url = $this->whatsapp->orderUrl($order);

        if (is_string($url) && $url !== '') {
            $this->whatsapp->markRedirected($order);

            return redirect()->away($url);
        }

        // Order already committed — never roll it back. Show a safe fallback.
        Log::warning('Order created but WhatsApp URL could not be generated.', [
            'order_number' => $order->order_number,
        ]);

        $fallback = URL::temporarySignedRoute(
            'orders.success',
            now()->addDays(7),
            ['order' => $order->order_number],
        );

        return redirect()
            ->to($fallback)
            ->with('whatsapp_fallback', true);
    }

    public function success(string $order): View
    {
        $orderModel = Order::query()
            ->with('items')
            ->where('order_number', $order)
            ->firstOrFail();

        $settings = $this->settings();

        return view('orders.success', [
            'settings' => $settings,
            'logoUrl' => $this->logoUrl($settings),
            'order' => $orderModel,
            'currency' => $orderModel->currency ?: ($settings->currency ?: 'ل.س'),
            'whatsappFallback' => (bool) session('whatsapp_fallback'),
        ]);
    }

    protected function settings(): RestaurantSetting
    {
        return RestaurantSetting::cached();
    }

    protected function logoUrl(RestaurantSetting $settings): string
    {
        return $settings->logoUrl(asset('images/logo.png'));
    }
}
