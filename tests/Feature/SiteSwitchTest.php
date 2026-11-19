<?php

namespace Tests\Feature;

use App\Support\Site;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SiteSwitchTest extends TestCase
{
    public function test_imagesafe_is_the_default_site(): void
    {
        $this->assertSame('imagesafe', Site::key());
        $this->assertTrue(Site::is('imagesafe'));

        $this->get('/')->assertInertia(fn (Assert $page) => $page->component('ImageSafePage'));
        $this->get('/imagesafe')->assertRedirect('/')->assertStatus(301);
    }

    public function test_comordo_home_and_imagesafe_summary(): void
    {
        config(['site.key' => 'comordo']);

        $this->get('/')->assertInertia(fn (Assert $page) => $page->component('HomePage'));
        $this->get('/imagesafe')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('ImageSafeOverviewPage', false));
    }

    public function test_imagesafe_only_routes_404_on_comordo(): void
    {
        config(['site.key' => 'comordo']);

        foreach (['/pricing', '/demo', '/compli', '/login', '/signup', '/start', '/buy', '/account',
            '/preview/9b2f3c1e-7a4d-4e8b-9c0d-1a2b3c4d5e6f'] as $path) {
            $this->get($path)->assertNotFound();
        }
    }

    public function test_shared_pages_work_on_both_sites(): void
    {
        foreach (['imagesafe', 'comordo'] as $site) {
            config(['site.key' => $site]);

            foreach (['/contact', '/privacy', '/terms'] as $path) {
                $this->get($path)->assertOk();
            }
        }
    }

    public function test_site_is_shared_with_the_front_end(): void
    {
        config(['site.key' => 'comordo']);
        $this->get('/contact')->assertInertia(fn (Assert $page) => $page->where('site', 'comordo'));

        config(['site.key' => 'imagesafe']);
        $this->get('/contact')->assertInertia(fn (Assert $page) => $page->where('site', 'imagesafe'));
    }

    public function test_unknown_site_value_falls_back_to_imagesafe(): void
    {
        config(['site.key' => 'nope']);

        $this->assertSame('imagesafe', Site::key());
    }
}
