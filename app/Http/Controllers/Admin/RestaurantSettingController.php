<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateRestaurantSettingRequest;
use App\Models\RestaurantSetting;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use App\Support\PublicStorage;
use Illuminate\View\View;

class RestaurantSettingController extends Controller
{
    public function __construct(
        protected ImageService $images,
    ) {}

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
            'hero_image' => null,
        ]);

        return view('admin.settings.edit', [
            'settings' => $settings,
            'heroPreviewUrl' => $this->heroPreviewUrl($settings),
        ]);
    }

    public function update(UpdateRestaurantSettingRequest $request): RedirectResponse
    {
        $settings = RestaurantSetting::query()->first();

        if (! $settings) {
            $settings = new RestaurantSetting;
        }

        $settings->fill($request->settingsPayload());

        if ($request->boolean('remove_hero_image') && ! $request->hasFile('hero_image')) {
            $this->images->delete($settings->hero_image);
            $settings->hero_image = null;
        }

        if ($request->hasFile('hero_image')) {
            $settings->hero_image = $this->images->replace(
                $request->file('hero_image'),
                'restaurant/hero',
                $settings->hero_image,
            );
        }

        $settings->save();

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'تم حفظ إعدادات المطعم بنجاح.');
    }

    protected function heroPreviewUrl(RestaurantSetting $settings): ?string
    {
        return PublicStorage::url($settings->hero_image, $settings->updated_at?->timestamp);
    }
}
