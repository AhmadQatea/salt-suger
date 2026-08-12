<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Seed realistic menu products for each category.
     */
    public function run(): void
    {
        $catalog = [
            'burger' => [
                [
                    'name' => 'برغر كلاسيك',
                    'slug' => 'classic-burger',
                    'description' => 'برغر لحم، جبنة، خس، طماطم، وصوص خاص',
                    'price' => '25000.00',
                    'image' => 'products/classic-burger.webp',
                    'badge' => 'الأكثر طلباً',
                ],
                [
                    'name' => 'برغر دبل',
                    'slug' => 'double-burger',
                    'description' => 'قطعتان من اللحم مع جبنة شيدر وصلصة المنزل',
                    'price' => '35000.00',
                    'image' => 'products/double-burger.webp',
                    'badge' => null,
                ],
                [
                    'name' => 'برغر تشيكن',
                    'slug' => 'chicken-burger',
                    'description' => 'صدر دجاج مقرمش مع خس ومايونيز',
                    'price' => '22000.00',
                    'image' => 'products/chicken-burger.webp',
                    'badge' => 'جديد',
                ],
            ],
            'meals' => [
                [
                    'name' => 'وجبة برغر كلاسيك',
                    'slug' => 'classic-burger-meal',
                    'description' => 'برغر كلاسيك مع بطاطا ومشروب',
                    'price' => '40000.00',
                    'image' => 'products/classic-burger-meal.webp',
                    'badge' => 'عرض خاص',
                ],
                [
                    'name' => 'وجبة دجاج عائلي',
                    'slug' => 'family-chicken-meal',
                    'description' => 'قطع دجاج مقرمشة تكفي العائلة مع بطاطا',
                    'price' => '85000.00',
                    'image' => 'products/family-chicken-meal.webp',
                    'badge' => null,
                ],
            ],
            'sandwich' => [
                [
                    'name' => 'ساندويش شاورما دجاج',
                    'slug' => 'chicken-shawarma-sandwich',
                    'description' => 'شاورما دجاج مع ثوم ومخلل',
                    'price' => '18000.00',
                    'image' => 'products/chicken-shawarma-sandwich.webp',
                    'badge' => null,
                ],
                [
                    'name' => 'ساندويش فلافل',
                    'slug' => 'falafel-sandwich',
                    'description' => 'فلافل مقرمش مع طحينة وخضار',
                    'price' => '12000.00',
                    'image' => 'products/falafel-sandwich.webp',
                    'badge' => null,
                ],
            ],
            'pizza' => [
                [
                    'name' => 'بيتزا مارغريتا',
                    'slug' => 'margherita-pizza',
                    'description' => 'صلصة طماطم، موزاريلا، وريحان',
                    'price' => '30000.00',
                    'image' => 'products/margherita-pizza.webp',
                    'badge' => null,
                ],
                [
                    'name' => 'بيتزا خضار',
                    'slug' => 'veggie-pizza',
                    'description' => 'مزيج خضار طازجة مع جبنة',
                    'price' => '32000.00',
                    'image' => 'products/veggie-pizza.webp',
                    'badge' => 'جديد',
                ],
            ],
            'appetizers' => [
                [
                    'name' => 'حلقات بصل',
                    'slug' => 'onion-rings',
                    'description' => 'حلقات بصل مقرمشة مع صوص',
                    'price' => '10000.00',
                    'image' => 'products/onion-rings.webp',
                    'badge' => null,
                ],
                [
                    'name' => 'ناجتس دجاج',
                    'slug' => 'chicken-nuggets',
                    'description' => 'قطع دجاج مقرمشة (6 قطع)',
                    'price' => '15000.00',
                    'image' => 'products/chicken-nuggets.webp',
                    'badge' => null,
                ],
            ],
            'fries' => [
                [
                    'name' => 'بطاطا عادية',
                    'slug' => 'regular-fries',
                    'description' => 'بطاطا مقلية مقرمشة',
                    'price' => '8000.00',
                    'image' => 'products/regular-fries.webp',
                    'badge' => null,
                ],
                [
                    'name' => 'بطاطا بالجبنة',
                    'slug' => 'cheese-fries',
                    'description' => 'بطاطا مع صلصة جبنة غنية',
                    'price' => '12000.00',
                    'image' => 'products/cheese-fries.webp',
                    'badge' => 'الأكثر طلباً',
                ],
            ],
            'drinks' => [
                [
                    'name' => 'كولا',
                    'slug' => 'cola',
                    'description' => 'مشروب غازي بارد',
                    'price' => '5000.00',
                    'image' => 'products/cola.webp',
                    'badge' => null,
                ],
                [
                    'name' => 'عصير برتقال طازج',
                    'slug' => 'fresh-orange-juice',
                    'description' => 'عصير برتقال طبيعي',
                    'price' => '9000.00',
                    'image' => 'products/fresh-orange-juice.webp',
                    'badge' => null,
                ],
            ],
            'desserts' => [
                [
                    'name' => 'براوني شوكولا',
                    'slug' => 'chocolate-brownie',
                    'description' => 'براوني دافئ مع صوص شوكولا',
                    'price' => '14000.00',
                    'image' => 'products/chocolate-brownie.webp',
                    'badge' => null,
                ],
                [
                    'name' => 'آيس كريم فانيلا',
                    'slug' => 'vanilla-ice-cream',
                    'description' => 'كرة آيس كريم فانيلا',
                    'price' => '7000.00',
                    'image' => 'products/vanilla-ice-cream.webp',
                    'badge' => null,
                ],
            ],
        ];

        foreach ($catalog as $categorySlug => $products) {
            $category = Category::query()->where('slug', $categorySlug)->firstOrFail();

            foreach ($products as $product) {
                Product::updateOrCreate(
                    ['slug' => $product['slug']],
                    [
                        'category_id' => $category->id,
                        'name' => $product['name'],
                        'description' => $product['description'],
                        'price' => $product['price'],
                        'image' => $product['image'],
                        'badge' => $product['badge'],
                        'is_available' => true,
                    ]
                );
            }
        }
    }
}
