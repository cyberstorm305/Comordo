<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ExportStaticSiteTest extends TestCase
{
    private string $out;

    private string $build;

    protected function setUp(): void
    {
        parent::setUp();

        $base = sys_get_temp_dir().'/site-export-test-'.bin2hex(random_bytes(6));
        $this->out = $base.'/out';
        $this->build = $base.'/build';

        // A minimal Vite build: a manifest for the two entry points and their asset files.
        File::ensureDirectoryExists($this->build.'/assets');
        File::put($this->build.'/manifest.json', json_encode([
            'resources/css/app.css' => ['file' => 'assets/app-TEST.css', 'src' => 'resources/css/app.css', 'isEntry' => true],
            'resources/js/app.js' => ['file' => 'assets/app-TEST.js', 'src' => 'resources/js/app.js', 'isEntry' => true],
        ]));
        File::put($this->build.'/assets/app-TEST.css', 'body{}');
        File::put($this->build.'/assets/app-TEST.js', 'console.log(1)');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory(dirname($this->out));

        parent::tearDown();
    }

    private function export(array $options = []): int
    {
        return $this->artisan('site:export', ['out' => $this->out, '--build' => $this->build, ...$options])->run();
    }

    public function test_export_writes_every_comordo_page(): void
    {
        $this->assertSame(0, $this->export());

        foreach (['index.html', 'imagesafe/index.html', 'contact/index.html', 'privacy/index.html', 'terms/index.html', '404.html'] as $file) {
            $this->assertFileExists($this->out.'/'.$file);
        }

        $home = File::get($this->out.'/index.html');
        $this->assertStringContainsString('<title>Comordo — Digital assurance for schools and trusts</title>', $home);
        $this->assertStringContainsString('<link rel="canonical" href="https://comordo.com/">', $home);
        // Root-relative, so the files load from whichever host serves the export.
        $this->assertStringContainsString('<link rel="stylesheet" href="/build/assets/app-TEST.css"', $home);
        $this->assertStringContainsString('<script type="module" src="/build/assets/app-TEST.js"', $home);
        $this->assertStringContainsString('<meta name="csrf-token" content="">', $home);
        $this->assertStringContainsString('<meta name="robots" content="index, follow', $home);

        $this->assertStringContainsString('<link rel="canonical" href="https://comordo.com/contact">', File::get($this->out.'/contact/index.html'));

        $this->assertFileExists($this->out.'/build/manifest.json');
        $this->assertFileExists($this->out.'/build/assets/app-TEST.js');
    }

    public function test_export_is_production_robots_and_sitemap(): void
    {
        $this->assertSame(0, $this->export());

        $robots = File::get($this->out.'/robots.txt');
        $this->assertStringContainsString('Sitemap: https://comordo.com/sitemap.xml', $robots);
        $this->assertStringNotContainsString('Disallow: /', $robots);

        preg_match_all('#<loc>([^<]+)</loc>#', File::get($this->out.'/sitemap.xml'), $locs);
        $this->assertSame([
            'https://comordo.com/',
            'https://comordo.com/imagesafe',
            'https://comordo.com/contact',
            'https://comordo.com/privacy',
            'https://comordo.com/terms',
        ], $locs[1]);
    }

    public function test_export_contains_only_public_files(): void
    {
        $this->assertSame(0, $this->export());

        foreach (['.env', 'index.php', 'vendor', 'storage', 'artisan', 'hot'] as $absent) {
            $this->assertFileDoesNotExist($this->out.'/'.$absent);
        }
        foreach (['contact.php', 'subscribe.php', '_lib.php', 'comordo.svg', 'images/og-comordo.png'] as $present) {
            $this->assertFileExists($this->out.'/'.$present);
        }
        $this->assertStringContainsString('ErrorDocument 404 /404.html', File::get($this->out.'/.htaccess'));
        $this->assertSame(File::get(resource_path('static-site/contact.php')), File::get($this->out.'/contact.php'));
    }

    public function test_404_page_exists_and_is_not_imagesafe_branded(): void
    {
        $this->assertSame(0, $this->export());

        $this->assertFileExists($this->out.'/404.html');
        $this->assertStringNotContainsString('imagesafe.svg', File::get($this->out.'/404.html'));
    }

    public function test_refuses_non_empty_output_without_force(): void
    {
        File::ensureDirectoryExists($this->out);
        File::put($this->out.'/stale.txt', 'old');

        $this->assertNotSame(0, $this->export());
        $this->assertFileExists($this->out.'/stale.txt');
        $this->assertFileDoesNotExist($this->out.'/index.html');

        $this->assertSame(0, $this->export(['--force' => true]));
        $this->assertFileDoesNotExist($this->out.'/stale.txt');
        $this->assertFileExists($this->out.'/index.html');
    }

    public function test_export_leaves_the_app_as_it_was(): void
    {
        $this->assertSame(0, $this->export());

        $this->assertSame('testing', $this->app->environment());
        $this->assertSame('imagesafe', config('site.key'));
        $this->assertSame(base_path('public'), public_path());
    }
}
