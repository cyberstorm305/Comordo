<?php

/*
 * Titles, descriptions and indexing for each page. app.blade.php writes these into the
 * server-rendered HTML, because link previews (LinkedIn, Facebook, Slack, WhatsApp,
 * Teams) don't run JavaScript. The page title in the browser comes from the same place
 * (props.seo, via titleFor() in resources/js/pages.js).
 *
 * The site is ImageSafe's: previews and titles say ImageSafe, not Comordo.
 */

return [
    'site_name' => 'ImageSafe',
    'title' => 'ImageSafe — Image safeguarding for schools and trusts',
    'description' => 'ImageSafe finds photos of pupils across your school website and documents, checks them against permission records and safeguarding guidance, and shows your safeguarding lead what needs attention.',
    'image' => '/images/og-image.png',
    'image_alt' => 'ImageSafe: Every photo your school publishes is a safeguarding decision.',
    'theme_color' => '#1B2536',
    'twitter' => '@comordoapp',

    // The company behind the site: one Organization in schema.org for both sites.
    'organisation_url' => 'https://comordo.com',

    // Google Tag Manager container. Consent Mode keeps analytics off until the visitor accepts.
    'gtm_id' => env('GTM_ID', 'GTM-NMW5V3FZ'),

    'pages' => [
        'home' => [
            'title' => 'ImageSafe — Image safeguarding for schools and trusts',
            'sitemap' => ['priority' => '1.0', 'changefreq' => 'weekly'],
        ],
        'pricing' => [
            'title' => 'Pricing for schools and trusts · ImageSafe',
            'description' => 'ImageSafe pricing for schools and trusts: per school, monthly or yearly, with volume discounts from 10 schools. Start with a 7-day free trial.',
            'sitemap' => ['priority' => '0.9', 'changefreq' => 'monthly'],
        ],
        'demo' => [
            'title' => 'Book a demo for your school or trust · ImageSafe',
            'description' => 'See ImageSafe on your own school website: book a 30-minute demo with the team and see how it finds, checks and reviews the photos your school publishes.',
            'sitemap' => ['priority' => '0.7', 'changefreq' => 'monthly'],
        ],
        'contact' => [
            'title' => 'Contact the ImageSafe team',
            'description' => 'Get in touch with the ImageSafe team about image safeguarding for your school or trust.',
            'sitemap' => ['priority' => '0.6', 'changefreq' => 'yearly'],
        ],
        'privacy' => [
            'title' => 'Privacy policy · ImageSafe',
            'description' => 'How ImageSafe and Comordo Ltd handle personal data, including children’s data, free website previews and cookies.',
            'sitemap' => ['priority' => '0.3', 'changefreq' => 'yearly'],
        ],
        'terms' => [
            'title' => 'Terms of service · ImageSafe',
            'description' => 'The terms for using ImageSafe and the imagesafe.co.uk website.',
            'sitemap' => ['priority' => '0.3', 'changefreq' => 'yearly'],
        ],
        // Not part of the ImageSafe site yet (hidden from the navigation): kept out of search.
        'compli' => [
            'title' => 'Compli · Comordo',
            'robots' => 'noindex, follow',
        ],
        // A visitor's own scan result.
        'preview' => [
            'title' => 'Your ImageSafe preview',
            'robots' => 'noindex, nofollow',
        ],
    ],
];
