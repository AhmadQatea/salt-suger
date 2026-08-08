<?php

namespace App\Providers;

use App\Models\RestaurantSetting;
use App\Models\User;
use App\Services\CartService;
use App\Services\OrderManagementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CartService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer([
            'layouts.public',
            'components.menu.header',
            'components.menu.bottom-nav',
            'components.menu.sticky-cart',
            'menu.*',
            'cart.*',
            'checkout.*',
            'orders.*',
        ], function ($view): void {
            $cart = app(CartService::class);
            $data = $view->getData();
            $settings = ($data['settings'] ?? null) instanceof RestaurantSetting
                ? $data['settings']
                : RestaurantSetting::cached();

            if (! array_key_exists('settings', $data)) {
                $view->with('settings', $settings);
            }

            $view->with('cartCount', $cart->totalQuantity());
            $view->with('cartSubtotal', $cart->subtotal());
            $view->with('cartCurrency', $settings->currency ?: 'ل.س');
        });

        View::composer('layouts.admin', function ($view): void {
            $user = Auth::user();
            $pending = 0;

            if ($user instanceof User && $user->isAdmin()) {
                $pending = Cache::remember('admin.pending_orders_count', 15, function (): int {
                    return app(OrderManagementService::class)->pendingCount();
                });
            }

            $view->with('pendingOrdersCount', $pending);
        });
    }
}
