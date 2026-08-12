<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\RestaurantSetting;
use App\Services\CartService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        protected CartService $cart,
    ) {}

    public function index(): View
    {
        $this->cart->revalidate(failOnUnavailable: false);

        $settings = $this->settings();

        return view('cart.index', [
            'settings' => $settings,
            'logoUrl' => $this->logoUrl($settings),
            'items' => collect($this->cart->all())->values(),
            'subtotal' => $this->cart->subtotal(),
            'currency' => $settings->currency ?: 'ل.س',
            'isEmpty' => $this->cart->isEmpty(),
        ]);
    }

    public function store(StoreCartItemRequest $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $this->cart->add(
            (int) $request->integer('product_id'),
            (int) $request->integer('quantity', 1),
            $request->input('note'),
        );

        if ($request->wantsJson()) {
            $settings = $this->settings();
            $currency = $settings->currency ?: 'ل.س';
            $subtotal = $this->cart->subtotal();

            return response()->json([
                'message' => 'تمت إضافة المنتج إلى السلة',
                'cart_count' => $this->cart->totalQuantity(),
                'cart_subtotal' => $subtotal,
                'cart_subtotal_formatted' => Money::format($subtotal, $currency),
            ]);
        }

        return back()->with('status', 'تمت إضافة الصنف إلى الطلب.');
    }

    public function update(UpdateCartItemRequest $request, string $key): RedirectResponse
    {
        $this->cart->update(
            $key,
            $request->filled('quantity') ? (int) $request->integer('quantity') : null,
            $request->exists('note') ? $request->input('note') : null,
            $request->exists('note'),
        );

        return back()->with('status', 'تم تحديث الطلب.');
    }

    public function destroy(string $key): RedirectResponse
    {
        $this->cart->remove($key);

        return back()->with('status', 'تم حذف الصنف من الطلب.');
    }

    public function clear(): RedirectResponse
    {
        $this->cart->clear();

        return redirect()
            ->route('cart.index')
            ->with('status', 'تم تفريغ السلة.');
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
