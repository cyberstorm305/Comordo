<?php

namespace Tests\Feature;

use Tests\TestCase;

class ComordoSeoTest extends TestCase
{
    private function asComordoProduction(): void
    {
        $this->app['env'] = 'production';
        config(['site.key' => 'comordo', 'app.url' => 'https://comordo.com']);
    }

    public function test_comordo_home_tags(): void
    {
        $this->asComordoProduction();

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('<title>Comordo — Digital assurance for schools and trusts</title>', $html);
        $this->assertStringContainsString('<meta property="og:site_name" content="Comordo">', $html);
        $this->assertStringContainsString('<link rel="canonical" href="https://comordo.com/">', $html);
        $this->assertStringContainsString('<meta property="og:image" content="https://comordo.com/images/og-comordo.png">', $html);
        $this->assertStringContainsString('"name":"Comordo Ltd"', $html);
        $this->assertStringContainsString('"url":"https://imagesafe.co.uk/"', $html);
        $this->assertFileExists(public_path('images/og-comordo.png'));
    }

    public function test_comordo_imagesafe_page_title(): void
    {
        $this->asComordoProduction();

        $html = $this->get('/imagesafe')->assertOk()->getContent();

        $this->assertStringContainsString('<title>ImageSafe: image safeguarding for schools · Comordo</title>', $html);
        $this->assertStringContainsString('<link rel="canonical" href="https://comordo.com/imagesafe">', $html);
    }

    public function test_comordo_sitemap_lists_only_comordo_pages(): void
    {
        $this->asComordoProduction();

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        preg_match_all('#<loc>([^<]+)</loc>#', $xml, $locs);
        $this->assertSame([
            'https://comordo.com/',
            'https://comordo.com/imagesafe',
            'https://comordo.com/contact',
            'https://comordo.com/privacy',
            'https://comordo.com/terms',
        ], $locs[1]);
        $this->assertStringNotContainsString('/pricing', $xml);
        $this->assertStringNotContainsString('/demo', $xml);
    }

    public function test_comordo_robots_has_no_preview_rule(): void
    {
        $this->asComordoProduction();

        $this->get('/robots.txt')->assertOk()
            ->assertDontSee('/preview/', false)
            ->assertSee('Sitemap: https://comordo.com/sitemap.xml', false);
    }

    public function test_imagesafe_sitemap_is_unchanged(): void
    {
        $this->app['env'] = 'production';
        config(['app.url' => 'https://imagesafe.co.uk']);

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertStringContainsString('<loc>https://imagesafe.co.uk/pricing</loc>', $xml);
        $this->assertStringContainsString('<loc>https://imagesafe.co.uk/demo</loc>', $xml);
        $this->assertStringNotContainsString('/imagesafe<', $xml);
    }
}
