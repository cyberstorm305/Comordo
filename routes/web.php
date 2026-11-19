<?php

use App\Support\Seo;
use App\Support\Site;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// One app, two sites: SITE=imagesafe serves imagesafe.co.uk, SITE=comordo serves comordo.com.
Route::get('/', fn () => Inertia::render(Site::is('comordo') ? 'HomePage' : 'ImageSafePage'))->name('home');
Route::get('/imagesafe', fn () => Site::is('comordo')
    ? Inertia::render('ImageSafeOverviewPage')
    : redirect('/', 301))->name('imagesafe');
Route::get('/contact', fn () => Inertia::render('ContactPage'))->name('contact');
Route::get('/privacy', fn () => Inertia::render('PrivacyPage'))->name('privacy');
Route::get('/terms', fn () => Inertia::render('TermsPage'))->name('terms');

Route::middleware('site:imagesafe')->group(function () {
    Route::get('/pricing', fn () => Inertia::render('PricingPage'))->name('pricing');
    Route::get('/compli', fn () => Inertia::render('CompliPage'))->name('compli');
    Route::get('/demo', fn () => Inertia::render('DemoPage'))->name('demo');
    // A live website preview, shown as an ImageSafe dashboard. The id comes from ImageSafe's preview API.
    Route::get('/preview/{id}', fn (string $id) => Inertia::render('PreviewPage', ['id' => $id]))
        ->whereUuid('id')->name('preview');
});

// Search engines: only production is indexed; staging and local tell every crawler to stay out.
Route::get('/robots.txt', fn () => response(app()->isProduction()
    ? "User-agent: *\n".(Site::is('comordo') ? "Disallow:\n" : "Disallow: /preview/\n")."\nSitemap: ".rtrim((string) config('app.url'), '/')."/sitemap.xml\n"
    : "User-agent: *\nDisallow: /\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']))->name('robots');
Route::get('/sitemap.xml', fn () => response()->view('sitemap', ['pages' => Seo::sitemap()])
    ->header('Content-Type', 'application/xml; charset=UTF-8'))->name('sitemap');

Route::middleware('site:imagesafe')->group(function () {
    Route::redirect('/login', config('services.comordo.identity_url').'/login?product=imagesafe')->name('login');
    Route::redirect('/signup', config('services.comordo.identity_url').'/start?product=imagesafe')->name('signup');
    Route::redirect('/start', config('services.comordo.identity_url').'/start?product=imagesafe')->name('start');
    Route::redirect('/buy', config('services.comordo.identity_url').'/start?product=imagesafe')->name('buy');
    Route::redirect('/account', config('services.comordo.identity_url').'/dashboard')->name('account');
});
