<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Local SEO defaults (Idlib, Syria)
    |--------------------------------------------------------------------------
    |
    | Known public location only. Do not invent street addresses or coordinates.
    |
    */

    'city' => 'إدلب',
    'city_en' => 'Idlib',
    'country' => 'سوريا',
    'country_en' => 'Syria',
    'country_code' => 'SY',
    'locale' => 'ar_SY',
    'cuisine' => [
        'برجر',
        'وجبات سريعة',
        'ساندويشات',
    ],

    'default_description' => 'Salt&Suger مطعم وجبات سريعة في إدلب يقدم برجر لذيذ بنكهات خاصة، مع ساندويشات ووجبات متنوعة. اكتشف المنيو واطلب بسهولة عبر واتساب.',

    /*
    |--------------------------------------------------------------------------
    | Optional search-engine verification tokens
    |--------------------------------------------------------------------------
    */

    'google_site_verification' => env('SEO_GOOGLE_SITE_VERIFICATION'),
    'bing_site_verification' => env('SEO_BING_SITE_VERIFICATION'),

];
