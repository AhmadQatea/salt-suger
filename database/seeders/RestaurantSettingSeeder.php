<?php

namespace Database\Seeders;

use App\Models\RestaurantSetting;
use Illuminate\Database\Seeder;

class RestaurantSettingSeeder extends Seeder
{
    /**
     * Seed the singleton restaurant settings record.
     */
    public function run(): void
    {
        RestaurantSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'restaurant_name' => 'Salt&Suger',
                'logo' => null,
                'favicon' => null,
                'description' => 'Salt&Suger مطعم حلو ومالح ووجبات سريعة في إدلب يقدم البرجر والساندويشات والوجبات بنكهات خاصة.',
                'whatsapp_number' => null,
                'currency' => 'ل.س',
                'primary_color' => '#c8102e',
                'secondary_color' => '#111111',
                'accent_color' => '#f5c518',
                'whatsapp_enabled' => true,
                'whatsapp_message' => "مرحباً، أود تأكيد طلبي من Salt&Suger:\n{order_details}",
            ]
        );
    }
}
