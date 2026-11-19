<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use RuntimeException;

/**
 * Renders the comordo.com site to a folder of static files for shared hosting (no Laravel there):
 * every page as HTML, robots.txt and sitemap.xml, the Vite build, the public files and the
 * plain-PHP contact/newsletter endpoints from resources/static-site.
 */
class ExportStaticSite extends Command
{
    protected $signature = 'site:export
        {out : The folder to write the site into}
        {--build= : The Vite build folder (manifest.json + assets/) to use; defaults to public/build}
        {--force : Empty the output folder first if it is not empty}';

    protected $description = 'Render the comordo.com site to static files';

    /** Path => file in the export, and the status each must render with. */
    private const PAGES = [
        '/' => ['index.html', 200],
        '/imagesafe' => ['imagesafe/index.html', 200],
        '/contact' => ['contact/index.html', 200],
        '/privacy' => ['privacy/index.html', 200],
        '/terms' => ['terms/index.html', 200],
        '/robots.txt' => ['robots.txt', 200],
        '/sitemap.xml' => ['sitemap.xml', 200],
        '/__static-export-not-found__' => ['404.html', 404],
    ];

    /** Top-level entries in public/ that are not part of the static site. */
    private const PUBLIC_EXCLUDED = ['index.php', 'build', 'hot', 'storage', 'robots.txt'];

    public function handle(): int
    {
        $out = $this->absolute((string) $this->argument('out'));
        $build = $this->absolute((string) ($this->option('build') ?: public_path('build')));

        if (! File::isFile($build.'/manifest.json')) {
            $this->error("No manifest.json in {$build}.");

            return self::FAILURE;
        }

        if (File::isDirectory($out) && ! File::isEmptyDirectory($out)) {
            if (! $this->option('force')) {
                $this->error("{$out} is not empty (use --force to empty it).");

                return self::FAILURE;
            }
            File::cleanDirectory($out);
        }
        File::ensureDirectoryExists($out);

        $this->renderPages($out, $build);

        File::copyDirectory($build, $out.'/build');
        $this->copyPublicFiles($out);
        foreach (File::allFiles(resource_path('static-site'), true) as $file) {
            File::copy($file->getPathname(), $out.'/'.$file->getRelativePathname());
        }

        $this->info("Exported comordo.com to {$out}");

        return self::SUCCESS;
    }

    /**
     * Renders each page through the HTTP kernel as the production comordo.com site, with Vite
     * reading the given build, then puts the app's environment, config, public path and Vite
     * asset URLs back (the app sets no asset path resolver of its own).
     */
    private function renderPages(string $out, string $build): void
    {
        $overrides = [
            'site.key' => 'comordo',
            'app.url' => 'https://comordo.com',
            'session.driver' => 'array',
            'cache.default' => 'array',
        ];
        $originalConfig = [];
        foreach (array_keys($overrides) as $key) {
            $originalConfig[$key] = config($key);
        }
        $originalEnv = $this->laravel['env'];
        $originalPublic = public_path();

        // Vite resolves the manifest at public_path('build/manifest.json'): a temporary public
        // folder whose build/ is the given build makes it emit /build/assets/... URLs.
        $publicDir = storage_path('framework/static-export-'.bin2hex(random_bytes(6)));
        File::ensureDirectoryExists($publicDir);
        File::link($build, $publicDir.'/build');

        try {
            config($overrides);
            $this->laravel['env'] = 'production';
            $this->laravel->usePublicPath($publicDir);
            // Root-relative asset URLs, so the files load from whichever host serves the export.
            Vite::createAssetPathsUsing(fn (string $path) => '/'.ltrim($path, '/'));

            $kernel = $this->laravel->make(Kernel::class);
            foreach (self::PAGES as $path => [$file, $status]) {
                $request = Request::create('https://comordo.com'.$path);
                $response = $kernel->handle($request);
                $kernel->terminate($request, $response);

                if ($response->getStatusCode() !== $status) {
                    throw new RuntimeException("{$path} returned {$response->getStatusCode()}, expected {$status}.");
                }

                $content = (string) $response->getContent();
                // Static pages have no session, so no CSRF token.
                $content = preg_replace('#<meta name="csrf-token" content="[^"]*">#', '<meta name="csrf-token" content="">', $content);

                File::ensureDirectoryExists(dirname($out.'/'.$file));
                File::put($out.'/'.$file, $content);
                $this->line("  {$path} → {$file}");
            }
        } finally {
            Vite::createAssetPathsUsing(null);
            $this->laravel->usePublicPath($originalPublic);
            $this->laravel['env'] = $originalEnv;
            config($originalConfig);
            unlink($publicDir.'/build');
            File::deleteDirectory($publicDir);
        }
    }

    /** Copies public/ minus Laravel's front controller, the build, dev files and dotfiles. */
    private function copyPublicFiles(string $out): void
    {
        foreach (File::files(public_path(), true) as $file) {
            $name = $file->getFilename();
            if (! str_starts_with($name, '.') && ! in_array($name, self::PUBLIC_EXCLUDED, true)) {
                File::copy($file->getPathname(), $out.'/'.$name);
            }
        }
        foreach (File::directories(public_path()) as $dir) {
            $name = basename($dir);
            if (! str_starts_with($name, '.') && ! in_array($name, self::PUBLIC_EXCLUDED, true)) {
                File::copyDirectory($dir, $out.'/'.$name);
            }
        }
    }

    private function absolute(string $path): string
    {
        $path = str_starts_with($path, '/') ? $path : base_path($path);

        return rtrim(realpath($path) ?: $path, '/');
    }
}
