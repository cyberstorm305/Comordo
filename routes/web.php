<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ImageSafe is the homepage for now. The joint Comordo homepage (HomePage.vue) is kept
// for the multi-product marketing site; see BACKLOG.md.
// Route::get('/', fn () => Inertia::render('HomePage'))->name('home');
Route::get('/', fn () => Inertia::render('ImageSafePage'))->name('home');
Route::redirect('/imagesafe', '/', 301)->name('imagesafe');
Route::get('/compli', fn () => Inertia::render('CompliPage'))->name('compli');
Route::get('/contact', fn () => Inertia::render('ContactPage'))->name('contact');
Route::get('/demo', fn () => Inertia::render('DemoPage'))->name('demo');
Route::get('/privacy', fn () => Inertia::render('PrivacyPage'))->name('privacy');
Route::get('/terms', fn () => Inertia::render('TermsPage'))->name('terms');
// A live website preview, shown as an ImageSafe dashboard. The id comes from ImageSafe's preview API.
Route::get('/preview/{id}', fn (string $id) => Inertia::render('PreviewPage', ['id' => $id]))
    ->whereUuid('id')->name('preview');

Route::redirect('/login', config('services.comordo.identity_url').'/login')->name('login');
Route::redirect('/signup', config('services.comordo.identity_url').'/register')->name('signup');
Route::redirect('/account', config('services.comordo.identity_url').'/dashboard')->name('account');
