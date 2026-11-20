<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * The SEO and link-preview details for the current page, from config/seo.php
 * (imagesafe.co.uk) or config/seo-comordo.php (comordo.com).
 * Used by app.blade.php (server-rendered tags) and shared with the front end
 * (the tab title on Inertia navigation), so both always agree.
 */
class Seo
{
    /** Reads a setting from the current site's SEO config (seo.* or seo-comordo.*). */
    public static function config(string $key, mixed $default = null): mixed
    {
        return config((Site::is('comordo') ? 'seo-comordo.' : 'seo.').$key, $default);
    }

    /**
     * @return array{site_name: string, title: string, description: string, canonical: string, image: string, image_alt: string, robots: string, theme_color: string, twitter: string}
     */
    public static function for(Request $request): array
    {
        $page = self::config('pages.'.($request->route()?->getName() ?? ''), []);
        $base = rtrim((string) config('app.url'), '/');
        $path = '/'.ltrim($request->path(), '/');

        return [
            'site_name' => self::config('site_name'),
            'title' => $page['title'] ?? self::config('title'),
            'description' => $page['description'] ?? self::config('description'),
            // Always the canonical host (no www, no query string).
            'canonical' => $base.($path === '/' ? '/' : $path),
            'image' => $base.self::config('image'),
            'image_alt' => self::config('image_alt'),
            // Only production is indexed: staging (marketing.comordo.com) and local never are.
            'robots' => app()->isProduction() ? ($page['robots'] ?? 'index, follow, max-image-preview:large') : 'noindex, nofollow',
            'theme_color' => self::config('theme_color'),
            'twitter' => self::config('twitter'),
        ];
    }

    /**
     * schema.org data for the homepage: who runs the site, the site, and the product.
     *
     * @return array<string, mixed>
     */
    public static function structuredData(): array
    {
        $base = rtrim((string) config('app.url'), '/');
        // Both sites describe the same company with the same @id, so search engines join
        // imagesafe.co.uk and comordo.com up as one organisation and its product.
        $company = rtrim((string) self::config('organisation_url', 'https://comordo.com'), '/');
        $organisation = $company.'/#organization';

        if (Site::is('comordo')) {
            return self::comordoStructuredData($base, $company, $organisation);
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => $organisation,
                    'name' => 'Comordo Ltd',
                    'url' => $company.'/',
                    'logo' => $company.'/icon-512.png',
                    'email' => 'hello@comordo.com',
                    'sameAs' => [
                        'https://www.linkedin.com/company/comordoapp',
                        'https://www.facebook.com/comordoapp',
                        'https://www.instagram.com/comordoapp',
                        'https://x.com/comordoapp',
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $base.'/#website',
                    'url' => $base.'/',
                    'name' => self::config('site_name'),
                    'inLanguage' => 'en-GB',
                    'publisher' => ['@id' => $organisation],
                ],
                [
                    '@type' => 'SoftwareApplication',
                    '@id' => $base.'/#software',
                    'name' => self::config('site_name'),
                    'applicationCategory' => 'BusinessApplication',
                    'operatingSystem' => 'Web',
                    'url' => $base.'/',
                    'description' => self::config('description'),
                    'publisher' => ['@id' => $organisation],
                ],
            ],
        ];
    }

    /**
     * Pages for sitemap.xml: absolute URL, priority and change frequency.
     *
     * @return list<array{loc: string, priority: string, changefreq: string}>
     */
    public static function sitemap(): array
    {
        $base = rtrim((string) config('app.url'), '/');

        return collect(self::config('pages'))
            ->filter(fn (array $page) => isset($page['sitemap']))
            ->map(fn (array $page, string $name) => [
                'loc' => $base.route($name, [], false),
                'priority' => $page['sitemap']['priority'],
                'changefreq' => $page['sitemap']['changefreq'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private static function comordoStructuredData(string $base, string $company, string $organisation): array
    {
        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => $organisation,
                    'name' => 'Comordo Ltd',
                    'url' => $company.'/',
                    'logo' => $company.'/icon-512.png',
                    'email' => 'hello@comordo.com',
                    'sameAs' => [
                        'https://www.linkedin.com/company/comordoapp',
                        'https://www.facebook.com/comordoapp',
                        'https://www.instagram.com/comordoapp',
                        'https://x.com/comordoapp',
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => $base.'/#website',
                    'url' => $base.'/',
                    'name' => self::config('site_name'),
                    'inLanguage' => 'en-GB',
                    'publisher' => ['@id' => $organisation],
                ],
                [
                    '@type' => 'SoftwareApplication',
                    'name' => 'ImageSafe',
                    'applicationCategory' => 'BusinessApplication',
                    'operatingSystem' => 'Web',
                    '@id' => 'https://imagesafe.co.uk/#software',
                    'url' => 'https://imagesafe.co.uk/',
                    'description' => config('seo.description'),
                    'publisher' => ['@id' => $organisation],
                ],
            ],
        ];
    }
}
