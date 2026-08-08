<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SlugGenerator
{
    /**
     * Common Arabic restaurant terms mapped to Latin URL-friendly tokens.
     *
     * @var array<string, string>
     */
    protected array $dictionary = [
        'برغر' => 'burger',
        'برجر' => 'burger',
        'وجبات' => 'meals',
        'وجبة' => 'meal',
        'ساندويش' => 'sandwich',
        'ساندويتش' => 'sandwich',
        'بيتزا' => 'pizza',
        'مقبلات' => 'appetizers',
        'بطاطا' => 'fries',
        'بطاطس' => 'fries',
        'مشروبات' => 'drinks',
        'مشروب' => 'drink',
        'حلويات' => 'desserts',
        'حلوى' => 'dessert',
        'كلاسيك' => 'classic',
        'دبل' => 'double',
        'تشيكن' => 'chicken',
        'دجاج' => 'chicken',
        'لحم' => 'beef',
        'عائلي' => 'family',
        'شاورما' => 'shawarma',
        'فلافل' => 'falafel',
        'مارغريتا' => 'margherita',
        'مارجريتا' => 'margherita',
        'خضار' => 'veggie',
        'حلقات' => 'rings',
        'بصل' => 'onion',
        'ناجتس' => 'nuggets',
        'عادية' => 'regular',
        'الجبنة' => 'cheese',
        'جبنة' => 'cheese',
        'كولا' => 'cola',
        'عصير' => 'juice',
        'برتقال' => 'orange',
        'طازج' => 'fresh',
        'براوني' => 'brownie',
        'شوكولا' => 'chocolate',
        'آيس' => 'ice',
        'كريم' => 'cream',
        'فانيلا' => 'vanilla',
        'جديد' => 'new',
        'خاص' => 'special',
    ];

    /**
     * Generate a unique Latin slug from an Arabic (or mixed) name.
     */
    public function unique(string $name, string $table, string $column = 'slug', ?int $ignoreId = null): string
    {
        $base = $this->fromName($name);
        $slug = $base;
        $suffix = 2;

        while ($this->exists($table, $column, $slug, $ignoreId)) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * Convert a name into a Latin URL-friendly slug base.
     */
    public function fromName(string $name): string
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $name) ?? $name);
        $parts = preg_split('/\s+/u', $normalized) ?: [];
        $tokens = [];

        foreach ($parts as $part) {
            $clean = trim($part, " \t\n\r\0\x0B-_/\\|");

            if ($clean === '') {
                continue;
            }

            if (isset($this->dictionary[$clean])) {
                $tokens[] = $this->dictionary[$clean];

                continue;
            }

            $latin = Str::slug($clean);

            if ($latin !== '') {
                $tokens[] = $latin;
            }
        }

        $slug = implode('-', array_filter($tokens));

        if ($slug === '') {
            $slug = 'item-'.Str::lower(Str::random(8));
        }

        return $slug;
    }

    protected function exists(string $table, string $column, string $slug, ?int $ignoreId = null): bool
    {
        $query = DB::table($table)->where($column, $slug);

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}
