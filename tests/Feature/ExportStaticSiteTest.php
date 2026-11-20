<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ExportStaticSiteTest extends TestCase
{
    private string $base;

    private string $out;

    private string $build;

    protected function setUp(): void
    {
        parent::setUp();

        // The command only writes inside storage/, so the test folders live there too.
        $this->base = storage_path('site-export-test-'.bin2hex(random_bytes(6)));
        $this->out = $this->base.'/out';
        $this->build = $this->base.'/build';

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
        File::deleteDirectory($this->base);

        parent::tearDown();
    }

    private function export(array $options = [], ?string $out = null, ?string $build = null): int
    {
        return $this->artisan('site:export', ['out' => $out ?? $this->out, '--build' => $build ?? $this->build, ...$options])->run();
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
        // A dotfile in a public subfolder (like .DS_Store) is not part of the site.
        $dotfile = public_path('images/.export-test-dotfile');
        File::put($dotfile, 'x');

        try {
            $this->assertSame(0, $this->export());
        } finally {
            File::delete($dotfile);
        }

        foreach (['.env', 'index.php', 'vendor', 'storage', 'artisan', 'hot', 'images/.export-test-dotfile'] as $absent) {
            $this->assertFileDoesNotExist($this->out.'/'.$absent);
        }
        foreach (['contact.php', 'subscribe.php', '_lib.php', 'comordo.svg', 'images/og-comordo.png'] as $present) {
            $this->assertFileExists($this->out.'/'.$present);
        }
        $this->assertStringContainsString('ErrorDocument 404 /404.html', File::get($this->out.'/.htaccess'));
        // A 404 must never be cached: a file missing mid-deploy would stay "missing" in browsers.
        $this->assertMatchesRegularExpression('/Header always set Cache-Control "no-store" "expr=%\{REQUEST_STATUS\} == 404/', File::get($this->out.'/.htaccess'));
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
        $sessionBefore = $this->app->make('session.store');
        $cacheBefore = $this->app->make('cache.store');
        $appUrl = config('app.url');

        $this->assertSame(0, $this->export());

        $this->assertSame('testing', $this->app->environment());
        $this->assertSame('imagesafe', config('site.key'));
        $this->assertSame($appUrl, config('app.url'));
        $this->assertSame('array', config('session.driver'));
        $this->assertSame('array', config('cache.default'));
        $this->assertSame(base_path('public'), public_path());
        // The session and cache the export used are dropped, not left for the next caller.
        $this->assertNotSame($sessionBefore, $this->app->make('session.store'));
        $this->assertNotSame($cacheBefore, $this->app->make('cache.store'));
    }

    /**
     * Folders --force must never empty, and a file in each that must survive.
     * Never run these against a command without the output guard.
     */
    public static function forbiddenOutputs(): array
    {
        return [
            'relative project root' => ['.', 'artisan'],
            'filesystem root' => ['/', 'artisan'],
            'project root' => ['@base', 'artisan'],
            'public' => ['@public', 'public/index.php'],
            'public via storage/..' => ['storage/../public', 'public/index.php'],
            'storage itself' => ['@storage', 'storage'],
        ];
    }

    #[DataProvider('forbiddenOutputs')]
    public function test_refuses_output_outside_storage(string $out, string $survivor): void
    {
        $out = match ($out) {
            '@base' => base_path(),
            '@public' => public_path(),
            '@storage' => storage_path(),
            default => $out,
        };
        File::put($this->base.'/sentinel.txt', 'keep');

        $this->assertNotSame(0, $this->export(['--force' => true], $out));

        $this->assertFileExists($this->base.'/sentinel.txt');
        $this->assertFileExists(base_path($survivor));
    }

    public function test_refuses_output_that_is_the_build(): void
    {
        $this->assertNotSame(0, $this->export(['--force' => true], $this->build));
        $this->assertFileExists($this->build.'/manifest.json');
    }

    public function test_refuses_output_that_contains_the_build(): void
    {
        $outer = $this->base.'/outer';
        File::copyDirectory($this->build, $outer.'/build');
        File::put($outer.'/sentinel.txt', 'keep');

        $this->assertNotSame(0, $this->export(['--force' => true], $outer, $outer.'/build'));
        $this->assertFileExists($outer.'/sentinel.txt');
        $this->assertFileExists($outer.'/build/manifest.json');
    }

    public function test_refuses_output_inside_the_build(): void
    {
        $this->assertNotSame(0, $this->export(['--force' => true], $this->build.'/out'));
        $this->assertFileDoesNotExist($this->build.'/out');
    }

    public function test_refuses_a_symlinked_output(): void
    {
        $target = $this->base.'/target';
        File::ensureDirectoryExists($target);
        File::put($target.'/sentinel.txt', 'keep');
        symlink($target, $this->base.'/link');

        $this->assertNotSame(0, $this->export(['--force' => true], $this->base.'/link'));
        $this->assertFileExists($target.'/sentinel.txt');
    }

    public function test_accepts_a_relative_folder_inside_storage(): void
    {
        $this->assertSame(0, $this->export([], 'storage/'.basename($this->base).'/relative-out'));
        $this->assertFileExists($this->base.'/relative-out/index.html');
    }
}
