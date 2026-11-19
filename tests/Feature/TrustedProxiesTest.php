<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TrustedProxiesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::get('/_test/client', fn () => [
            'ip' => request()->ip(),
            'secure' => request()->isSecure(),
        ]);
    }

    /** Cloudflare (173.245.48.5) → nginx (10.0.0.1) → this app, as on the comordo.com host. */
    private function throughProxies()
    {
        return $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->withHeaders(['X-Forwarded-For' => '203.0.113.9, 173.245.48.5', 'X-Forwarded-Proto' => 'https'])
            ->getJson('http://localhost/_test/client');
    }

    public function test_forwarded_headers_are_ignored_by_default(): void
    {
        config(['trustedproxy.proxies' => null]);

        $this->throughProxies()->assertExactJson(['ip' => '10.0.0.1', 'secure' => false]);
    }

    public function test_trusted_proxies_give_the_visitor_ip_and_https(): void
    {
        config(['trustedproxy.proxies' => '10.0.0.1, 173.245.48.0/20']);

        $this->throughProxies()->assertExactJson(['ip' => '203.0.113.9', 'secure' => true]);
    }

    public function test_the_setting_comes_from_the_environment(): void
    {
        $config = require config_path('trustedproxy.php');

        $this->assertArrayHasKey('proxies', $config);
    }
}
