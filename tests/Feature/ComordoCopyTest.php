<?php

namespace Tests\Feature;

use Tests\TestCase;

class ComordoCopyTest extends TestCase
{
    private const FILES = [
        'Pages/HomePage.vue',
        'Pages/ImageSafeOverviewPage.vue',
        'Pages/ContactPage.vue',
        'Pages/PrivacyPage.vue',
        'Pages/TermsPage.vue',
        'Components/SiteNav.vue',
        'Components/SiteFooter.vue',
        'Components/PageHeader.vue',
    ];

    public function test_no_compli_in_comordo_page_sources(): void
    {
        foreach (self::FILES as $file) {
            $path = resource_path('js/'.$file);
            $this->assertFileExists($path, $file);

            $source = file_get_contents($path);
            $source = preg_replace('/<!--.*?-->/s', '', $source);
            $source = preg_replace('#/\*.*?\*/#s', '', $source);
            $source = preg_replace('#(?<!:)//[^\n]*#', '', $source);

            $this->assertSame(0, preg_match('/\bCompli\b/', $source), "{$file} mentions Compli");
        }
    }

    public function test_no_partner_logos_or_quotes_on_comordo_home(): void
    {
        $source = file_get_contents(resource_path('js/Pages/HomePage.vue'));

        $this->assertStringNotContainsString('oat.svg', $source);
        $this->assertStringNotContainsString('hsat.svg', $source);
        $this->assertStringNotContainsString('<blockquote', $source);
    }

    public function test_overview_page_links_to_imagesafe_and_start(): void
    {
        $path = resource_path('js/Pages/ImageSafeOverviewPage.vue');
        $this->assertFileExists($path);

        $source = file_get_contents($path);
        // Links go to ImageSafe's own site: imagesafe.co.uk unless the build sets VITE_IMAGESAFE_SITE_URL.
        $this->assertMatchesRegularExpression('/import\s*\{[^}]*\bimagesafeSiteUrl\b[^}]*\}\s*from\s*[\'"]@\/site\.js[\'"]/', $source);
        $this->assertStringContainsString("'https://imagesafe.co.uk'", file_get_contents(resource_path('js/site.js')));
        $this->assertMatchesRegularExpression('/import\s*\{[^}]*\bidentityStartHref\b[^}]*\}\s*from\s*[\'"]@\/identity\.js[\'"]/', $source);
    }
}
