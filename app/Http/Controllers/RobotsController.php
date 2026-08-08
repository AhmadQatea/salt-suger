<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Dynamic robots.txt for public indexing rules.
     */
    public function __invoke(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Allow: /menu',
            'Allow: /build/',
            'Allow: /images/',
            'Allow: /storage/',
            'Disallow: /admin',
            'Disallow: /admin/',
            'Disallow: /cart',
            'Disallow: /cart/',
            'Disallow: /checkout',
            'Disallow: /checkout/',
            'Disallow: /order/',
            '',
            'Sitemap: '.route('sitemap'),
            '',
        ];

        return response(implode("\n", $lines), 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
