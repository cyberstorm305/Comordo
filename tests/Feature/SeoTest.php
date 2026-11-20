<?php

namespace Tests\Feature;

use Tests\TestCase;

class SeoTest extends TestCase
{
    private function asProduction(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://imagesafe.co.uk']);
    }

    public function test_link_previews_say_imagesafe_not_comordo(): void
    {
        $this->asProduction();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<title>ImageSafe — Image safeguarding for schools and trusts</title>', $html);
        $this->assertStringContainsString('<meta property="og:site_name" content="ImageSafe">', $html);
        $this->assertStringContainsString('<meta property="og:title" content="ImageSafe — Image safeguarding for schools and trusts">', $html);
        $this->assertStringContainsString('<meta property="og:image" content="https://imagesafe.co.uk/images/og-image.png">', $html);
        $this->assertStringContainsString('<meta name="twitter:card" content="summary_large_image">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="https://imagesafe.co.uk/">', $html);
        $this->assertStringContainsString('<meta name="robots" content="index, follow, max-image-preview:large">', $html);

        preg_match_all('/<(?:title|meta (?:property|name)="(?:og|twitter):[^"]+")[^>]*>[^<]*/', $html, $tags);
        $this->assertNotEmpty($tags[0]);
        foreach ($tags[0] as $tag) {
            $this->assertStringNotContainsString('Comordo', $tag, "Preview tag mentions Comordo: {$tag}");
        }
    }

    public function test_each_page_has_its_own_title_description_and_canonical(): void
    {
        $this->asProduction();

        $html = $this->get('/pricing?utm_source=x')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Pricing for schools and trusts · ImageSafe</title>', $html);
        $this->assertStringContainsString('<meta name="description" content="ImageSafe pricing for schools and trusts', $html);
        $this->assertStringContainsString('<link rel="canonical" href="https://imagesafe.co.uk/pricing">', $html);
        $this->get('/pricing')->assertInertia(fn ($page) => $page->where('seo.title', 'Pricing for schools and trusts · ImageSafe'));
    }

    public function test_icons_manifest_and_structured_data_are_linked(): void
    {
        $this->asProduction();

        $html = $this->get('/')->getContent();

        foreach (['/favicon.ico', '/favicon.svg', '/apple-touch-icon.png', '/site.webmanifest'] as $file) {
            $this->assertStringContainsString("href=\"{$file}\"", $html);
            $this->assertFileExists(public_path(ltrim($file, '/')));
        }
        $this->assertFileExists(public_path('images/og-image.png'));
        $this->assertStringContainsString('"@type":"SoftwareApplication","@id":"https://imagesafe.co.uk/#software","name":"ImageSafe"', $html);
        // One company behind both sites: the Organization is comordo.com's, with the same @id there.
        $this->assertStringContainsString('"@type":"Organization","@id":"https://comordo.com/#organization","name":"Comordo Ltd","url":"https://comordo.com/"', $html);
        $this->assertStringNotContainsString('application/ld+json', $this->get('/pricing')->getContent());
    }

    public function test_tag_manager_loads_after_consent_defaults_to_denied(): void
    {
        $html = $this->get('/')->getContent();

        $consent = strpos($html, "gtag('consent', 'default'");
        $gtm = strpos($html, 'googletagmanager.com/gtm.js');

        $this->assertNotFalse($consent);
        $this->assertNotFalse($gtm);
        $this->assertLessThan($gtm, $consent);
        $this->assertStringContainsString("analytics_storage: 'denied'", $html);
        $this->assertStringContainsString("'GTM-NMW5V3FZ'", $html);
        $this->assertStringContainsString("'gtm.start'", $html);
        $this->assertStringContainsString('googletagmanager.com/ns.html?id=GTM-NMW5V3FZ', $html);
    }

    public function test_previews_and_non_production_sites_are_not_indexed(): void
    {
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $this->get('/')->getContent());

        $this->asProduction();
        $preview = $this->get('/preview/9b1deb4d-3b7d-4bad-9bdd-2b0d7b3dcb6d')->getContent();
        $this->assertStringContainsString('<meta name="robots" content="noindex, nofollow">', $preview);
    }

    public function test_robots_txt_depends_on_the_environment(): void
    {
        $this->get('/robots.txt')->assertOk()->assertSee("Disallow: /\n", false)->assertDontSee('Sitemap:');

        $this->asProduction();
        $this->get('/robots.txt')->assertOk()
            ->assertSee("Disallow: /preview/\n", false)
            ->assertSee('Sitemap: https://imagesafe.co.uk/sitemap.xml', false);
    }

    public function test_sitemap_lists_the_public_pages_only(): void
    {
        $this->asProduction();

        $xml = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8')->getContent();

        $this->assertNotFalse(simplexml_load_string($xml));
        foreach (['/', '/pricing', '/demo', '/contact', '/privacy', '/terms'] as $path) {
            $this->assertStringContainsString('<loc>https://imagesafe.co.uk'.$path.'</loc>', $xml);
        }
        $this->assertStringNotContainsString('/preview', $xml);
        $this->assertStringNotContainsString('/compli', $xml);
    }
}
