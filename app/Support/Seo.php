<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * The SEO and link-preview details for the current page, from config/seo.php.
 * Used by app.blade.php (server-rendered tags) and shared with the front end
 * (the tab title on Inertia navigation), so both always agree.
 */
class Seo
{
    /**
     * @return array{site_name: string, title: string, description: string, canonical: string, image: string, image_alt: string, robots: string, theme_color: string, twitter: string}
     */
    public static function for(Request $request): array
    {
        $page = config('seo.pages.'.($request->route()?->getName() ?? ''), []);
        $base = rtrim((string) config('app.url'), '/');
        $path = '/'.ltrim($request->path(), '/');

        return [
            'site_name' => config('seo.site_name'),
            'title' => $page['title'] ?? config('seo.title'),
            'description' => $page['description'] ?? config('seo.description'),
            // Always the canonical host (no www, no query string).
            'canonical' => $base.($path === '/' ? '/' : $path),
            'image' => $base.config('seo.image'),
            'image_alt' => config('seo.image_alt'),
            // Only production is indexed: staging (marketing.comordo.com) and local never are.
            'robots' => app()->isProduction() ? ($page['robots'] ?? 'index, follow, max-image-preview:large') : 'noindex, nofollow',
            'theme_color' => config('seo.theme_color'),
            'twitter' => config('seo.twitter'),
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
        $organisation = $base.'/#organization';

        return [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => $organisation,
                    'name' => 'Comordo Ltd',
                    'url' => $base.'/',
                    'logo' => $base.'/icon-512.png',
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
                    'name' => config('seo.site_name'),
                    'inLanguage' => 'en-GB',
                    'publisher' => ['@id' => $organisation],
                ],
                [
                    '@type' => 'SoftwareApplication',
                    'name' => config('seo.site_name'),
                    'applicationCategory' => 'BusinessApplication',
                    'operatingSystem' => 'Web',
                    'url' => $base.'/',
                    'description' => config('seo.description'),
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

        return collect(config('seo.pages'))
            ->filter(fn (array $page) => isset($page['sitemap']))
            ->map(fn (array $page, string $name) => [
                'loc' => $base.route($name, [], false),
                'priority' => $page['sitemap']['priority'],
                'changefreq' => $page['sitemap']['changefreq'],
            ])
            ->values()
            ->all();
    }
}
