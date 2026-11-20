<?php

/*
 * Titles, descriptions and indexing for each page of comordo.com, the same shape as
 * config/seo.php (which serves imagesafe.co.uk). Seo::config() picks the file by site.
 */

return [
    'site_name' => 'Comordo',
    'title' => 'Comordo — Digital assurance for schools and trusts',
    'description' => 'Comordo builds digital assurance tools for UK schools and multi-academy trusts. Our first product, ImageSafe, helps schools find and review the images they publish.',
    'image' => '/images/og-comordo.png',
    'image_alt' => 'Comordo: digital assurance for schools and trusts',
    'theme_color' => '#1B2536',
    'twitter' => '@comordoapp',

    // The company behind the site: one Organization in schema.org for both sites.
    'organisation_url' => 'https://comordo.com',

    // Google Tag Manager container. Consent Mode keeps analytics off until the visitor accepts.
    'gtm_id' => env('GTM_ID', 'GTM-NMW5V3FZ'),

    'pages' => [
        'home' => [
            'title' => 'Comordo — Digital assurance for schools and trusts',
            'sitemap' => ['priority' => '1.0', 'changefreq' => 'weekly'],
        ],
        'imagesafe' => [
            'title' => 'ImageSafe: image safeguarding for schools · Comordo',
            'description' => 'ImageSafe by Comordo finds every image your school publishes, checks it against photo permissions and safeguarding guidance, and gives your safeguarding lead a clear review workflow.',
            'sitemap' => ['priority' => '0.9', 'changefreq' => 'monthly'],
        ],
        'contact' => [
            'title' => 'Contact Comordo',
            'description' => 'Talk to Comordo about digital assurance for your school or multi-academy trust, or about ImageSafe, our image safeguarding product.',
            'sitemap' => ['priority' => '0.6', 'changefreq' => 'yearly'],
        ],
        'privacy' => [
            'title' => 'Privacy policy · Comordo',
            'description' => 'How Comordo Ltd collects and uses personal data on comordo.com, including contact and newsletter details and cookies.',
            'sitemap' => ['priority' => '0.3', 'changefreq' => 'yearly'],
        ],
        'terms' => [
            'title' => 'Terms of use · Comordo',
            'description' => 'The terms for using the comordo.com website, run by Comordo Ltd.',
            'sitemap' => ['priority' => '0.3', 'changefreq' => 'yearly'],
        ],
    ],
];
