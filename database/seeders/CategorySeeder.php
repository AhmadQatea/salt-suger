<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Seed restaurant menu categories.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'برغر',
                'slug' => 'burger',
                'description' => 'تشكيلة برجر Salt&Suger بنكهات خاصة لعشاق البرجر.',
                'image' => 'categories/burger.webp',
            ],
            [
                'name' => 'وجبات',
                'slug' => 'meals',
                'description' => 'وجبات سريعة متنوعة من Salt&Suger بنكهات مميزة.',
                'image' => 'categories/meals.webp',
            ],
            [
                'name' => 'ساندويش',
                'slug' => 'sandwich',
                'description' => 'ساندويشات شهية وسريعة من Salt&Suger.',
                'image' => 'categories/sandwich.webp',
            ],
            [
                'name' => 'بيتزا',
                'slug' => 'pizza',
                'description' => 'بيتزا بعجينة طازجة ونكهات متنوعة.',
                'image' => 'categories/pizza.webp',
            ],
            [
                'name' => 'مقبلات',
                'slug' => 'appetizers',
                'description' => 'مجموعة من المقبلات والجانبيات لتكمل وجبتك.',
                'image' => 'categories/appetizers.webp',
            ],
            [
                'name' => 'بطاطا',
                'slug' => 'fries',
                'description' => 'بطاطا مقرمشة بأحجام مختلفة.',
                'image' => 'categories/fries.webp',
            ],
            [
                'name' => 'مشروبات',
                'slug' => 'drinks',
                'description' => 'مشروبات متنوعة تناسب وجبتك.',
                'image' => 'categories/drinks.webp',
            ],
            [
                'name' => 'حلويات',
                'slug' => 'desserts',
                'description' => 'حلويات لذيذة لتكمل تجربة حلو ومالح.',
                'image' => 'categories/desserts.webp',
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'slug' => $category['slug'] ?: Str::slug($category['name']),
                    'description' => $category['description'],
                    'image' => $category['image'],
                    'is_active' => true,
                ]
            );
        }
    }
}
