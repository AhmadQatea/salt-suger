<?php

namespace Database\Factories;

use App\Models\RestaurantSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RestaurantSetting>
 */
class RestaurantSettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'restaurant_name' => 'Salt&Suger',
            'logo' => null,
            'favicon' => null,
            'description' => 'مطعم Salt&Suger — نكهات لا تُنسى.',
            // Test placeholder — production value comes from restaurant_settings.
            'whatsapp_number' => '+963911111111',
            'currency' => 'ل.س',
            'primary_color' => '#c8102e',
            'secondary_color' => '#111111',
            'accent_color' => '#f5c518',
            'whatsapp_enabled' => true,
            'whatsapp_message' => null,
        ];
    }
}
