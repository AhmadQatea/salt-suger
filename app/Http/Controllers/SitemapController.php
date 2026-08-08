<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Public XML sitemap of indexable restaurant pages.
     */
    public function __invoke(): Response
    {
        $urls = [
            [
                'loc' => route('home'),
                'changefreq' => 'daily',
                'priority' => '1.0',
            ],
            [
                'loc' => route('menu.index'),
                'changefreq' => 'daily',
                'priority' => '0.9',
            ],
        ];

        $categories = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['slug', 'updated_at']);

        foreach ($categories as $category) {
            $urls[] = [
                'loc' => route('menu.category', $category),
                'lastmod' => optional($category->updated_at)?->toAtomString(),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }

        $xml = view('seo.sitemap', ['urls' => $urls])->render();

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
