<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Server-side rendering (resources/js/ssr.js, run by comordo-marketing-ssr): the page HTML and
 * its <title> come from the SSR server when it answers, and from Blade alone when it doesn't.
 */
class SsrTest extends TestCase
{
    private function enableSsr(): void
    {
        // Any existing file stands in for the bundle; the SSR server itself is faked.
        config(['inertia.ssr.enabled' => true, 'inertia.ssr.bundle' => base_path('package.json')]);
    }

    public function test_server_rendered_html_and_its_title_are_used_with_one_title(): void
    {
        $this->enableSsr();
        Http::fake(['127.0.0.1:13714/render' => Http::response([
            'head' => ['<title data-inertia="">Pricing for schools and trusts · ImageSafe</title>'],
            'body' => '<div id="app" data-server-rendered><h1>Rendered on the server</h1></div>',
        ])]);

        $html = $this->get('/pricing')->assertOk()->getContent();

        $this->assertStringContainsString('<h1>Rendered on the server</h1>', $html);
        $this->assertSame(1, substr_count($html, '<title'));
        $this->assertStringContainsString('<title data-inertia="">Pricing for schools and trusts · ImageSafe</title>', $html);
    }

    public function test_without_the_ssr_server_the_page_still_has_its_own_title(): void
    {
        $this->enableSsr();
        Http::fake(['127.0.0.1:13714/render' => Http::response('down', 500)]);

        $html = $this->get('/pricing')->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<title'));
        $this->assertStringContainsString('<title>Pricing for schools and trusts · ImageSafe</title>', $html);
    }

    public function test_the_ssr_server_is_asked_once_per_page(): void
    {
        $this->enableSsr();
        Http::fake(['127.0.0.1:13714/render' => Http::response(['head' => [], 'body' => '<div id="app"></div>'])]);

        $this->get('/pricing')->assertOk();

        Http::assertSentCount(1);
    }
}
