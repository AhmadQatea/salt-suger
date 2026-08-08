<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRestaurantSettingRequest;
use App\Models\RestaurantSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RestaurantSettingController extends Controller
{
    public function edit(): View
    {
        $settings = RestaurantSetting::query()->first() ?? RestaurantSetting::query()->create([
            'restaurant_name' => config('app.name', 'Salt&Suger'),
            'description' => 'وجبتك المفضلة... بطلب أسهل وأسرع',
            'currency' => 'ل.س',
            'primary_color' => '#ba0013',
            'secondary_color' => '#111111',
            'accent_color' => '#cca800',
            'whatsapp_enabled' => false,
            'whatsapp_number' => null,
        ]);

        return view('admin.settings.edit', [
            'settings' => $settings,
        ]);
    }

    public function update(UpdateRestaurantSettingRequest $request): RedirectResponse
    {
        $settings = RestaurantSetting::query()->first();

        if (! $settings) {
            $settings = new RestaurantSetting;
        }

        $settings->fill($request->settingsPayload());
        $settings->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'تم حفظ إعدادات المطعم بنجاح.');
    }
}
