<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Vite;
use Inertia\Ssr\SsrState;
use RuntimeException;
use Symfony\Component\Process\Process;

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
        {--force : Empty the output folder first if it is not empty}
        {--require-ssr : Fail unless every page was server-rendered (the SSR server must be running)}
        {--ssr-bundle= : An SSR bundle (vite build --ssr) to run for the export; implies --require-ssr}';

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

        // The output folder is emptied with --force, so it may only ever be a folder of its own
        // inside storage/: never the project, public/, storage/ itself or the build.
        if ($problem = $this->unsafeOutput((string) $this->argument('out'), $out, $build)) {
            $this->error("Refusing to export to {$out}: {$problem}");

            return self::FAILURE;
        }

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

        $ssr = null;
        if ($bundle = $this->option('ssr-bundle')) {
            if (! File::isFile($bundle = $this->absolute((string) $bundle))) {
                $this->error("No SSR bundle at {$bundle}.");

                return self::FAILURE;
            }
            $ssr = $this->startSsrServer($bundle);
        }

        try {
            $this->renderPages($out, $build, $ssr !== null);
        } finally {
            $ssr?->stop();
        }

        File::copyDirectory($build, $out.'/build');
        $this->copyPublicFiles($out);
        foreach (File::allFiles(resource_path('static-site'), true) as $file) {
            File::ensureDirectoryExists(dirname($out.'/'.$file->getRelativePathname()));
            File::copy($file->getPathname(), $out.'/'.$file->getRelativePathname());
        }

        $this->info("Exported comordo.com to {$out}");

        return self::SUCCESS;
    }

    /**
     * Renders each page through the HTTP kernel as the production comordo.com site, with Vite
     * reading the given build, then puts the app's environment, config, public path and Vite
     * asset URLs back (the app sets no asset path resolver of its own) and drops the array
     * session and cache stores it used.
     */
    private function renderPages(string $out, string $build, bool $ownSsr = false): void
    {
        $overrides = $ownSsr ? [
            'inertia.ssr.enabled' => true,
            'inertia.ssr.url' => 'http://127.0.0.1:'.$this->ssrPort,
            'inertia.ssr.bundle' => $this->ssrBundle,
        ] : [];
        $overrides += [
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

        try {
            File::ensureDirectoryExists($publicDir);
            File::link($build, $publicDir.'/build');

            config($overrides);
            $this->laravel['env'] = 'production';
            $this->laravel->usePublicPath($publicDir);
            // Root-relative asset URLs, so the files load from whichever host serves the export.
            Vite::createAssetPathsUsing(fn (string $path) => '/'.ltrim($path, '/'));

            $kernel = $this->laravel->make(Kernel::class);
            foreach (self::PAGES as $path => [$file, $status]) {
                // Inertia keeps one SSR result per request in SsrState; every page here is a new
                // request in the same process, so start each with a fresh one.
                $this->laravel->forgetInstance(SsrState::class);

                $request = Request::create('https://comordo.com'.$path);
                $response = $kernel->handle($request);
                $kernel->terminate($request, $response);

                // The Inertia pages (the 404 page is plain Blade, already complete HTML).
                $isPage = $status === 200 && str_contains((string) $response->headers->get('Content-Type'), 'text/html');
                if ($isPage && ($ownSsr || $this->option('require-ssr')) && $this->laravel->make(SsrState::class)->response === null) {
                    throw new RuntimeException("{$path} was not server-rendered: is the SSR server running (node bootstrap/ssr/ssr.js)?");
                }

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
            foreach (['session', 'session.store', 'cache', 'cache.store'] as $service) {
                $this->laravel->forgetInstance($service);
            }
            Facade::clearResolvedInstance('session');
            Facade::clearResolvedInstance('cache');

            if (is_link($publicDir.'/build')) {
                unlink($publicDir.'/build');
            }
            if (File::isDirectory($publicDir)) {
                File::deleteDirectory($publicDir);
            }
        }
    }

    private int $ssrPort = 0;

    private ?string $ssrBundle = null;

    /** Runs the SSR bundle on a free local port until the export is done, or throws. */
    private function startSsrServer(string $bundle): Process
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        $this->ssrPort = (int) substr(strrchr(stream_socket_get_name($socket, false), ':'), 1);
        fclose($socket);
        $this->ssrBundle = $bundle;

        $process = new Process(['node', $bundle], null, ['INERTIA_SSR_PORT' => (string) $this->ssrPort]);
        $process->setTimeout(null);
        $process->start();

        for ($i = 0; $i < 50; $i++) {
            if (! $process->isRunning()) {
                throw new RuntimeException('The SSR server stopped: '.trim($process->getErrorOutput() ?: $process->getOutput()));
            }
            if (@file_get_contents('http://127.0.0.1:'.$this->ssrPort.'/health') !== false) {
                $this->line("  SSR server on port {$this->ssrPort}");

                return $process;
            }
            usleep(100_000);
        }
        $process->stop();

        throw new RuntimeException('The SSR server did not start within 5 seconds.');
    }

    /** Copies public/ minus Laravel's front controller, the build, dev files and dotfiles (at any depth). */
    private function copyPublicFiles(string $out): void
    {
        foreach (File::allFiles(public_path()) as $file) {
            $relative = $file->getRelativePathname();
            if (in_array(explode('/', $relative)[0], self::PUBLIC_EXCLUDED, true)) {
                continue;
            }
            File::ensureDirectoryExists(dirname($out.'/'.$relative));
            File::copy($file->getPathname(), $out.'/'.$relative);
        }
    }

    /** Why the output folder is unsafe to write (and empty), or null when it is a folder inside storage/. */
    private function unsafeOutput(string $given, string $out, string $build): ?string
    {
        $storage = $this->absolute(storage_path());

        if (! str_starts_with($out, $storage.'/')) {
            return "the output folder must be a folder inside {$storage} (not storage itself).";
        }
        if (is_link($this->lexical($given)) || is_link($out)) {
            return 'the output folder must not be a symlink.';
        }
        if (file_exists($out) && ! is_dir($out)) {
            return 'the output path is a file.';
        }
        if ($out === $build || str_starts_with($build, $out.'/') || str_starts_with($out, $build.'/')) {
            return 'the output folder must not be, contain or sit inside the build folder.';
        }

        return null;
    }

    /** The absolute path with "." and ".." collapsed, without resolving symlinks. */
    private function lexical(string $path): string
    {
        $path = str_starts_with($path, '/') ? $path : base_path($path);
        $parts = [];
        foreach (explode('/', $path) as $segment) {
            if ($segment === '..') {
                array_pop($parts);
            } elseif ($segment !== '' && $segment !== '.') {
                $parts[] = $segment;
            }
        }

        return '/'.implode('/', $parts);
    }

    /**
     * The real absolute path: symlinks resolved for the part that exists, the rest
     * (folders still to be created) appended.
     */
    private function absolute(string $path): string
    {
        $existing = $this->lexical($path);
        $rest = [];
        while ($existing !== '/' && ! file_exists($existing)) {
            array_unshift($rest, basename($existing));
            $existing = dirname($existing);
        }
        $real = rtrim(realpath($existing) ?: $existing, '/');

        return ($real.($rest ? '/'.implode('/', $rest) : '')) ?: '/';
    }
}
