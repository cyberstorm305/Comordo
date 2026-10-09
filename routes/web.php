<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('HomePage'))->name('home');
Route::get('/imagesafe', fn () => Inertia::render('ImageSafePage'))->name('imagesafe');
Route::get('/compli', fn () => Inertia::render('CompliPage'))->name('compli');
Route::get('/contact', fn () => Inertia::render('ContactPage'))->name('contact');
Route::get('/demo', fn () => Inertia::render('DemoPage'))->name('demo');
Route::get('/privacy', fn () => Inertia::render('PrivacyPage'))->name('privacy');
Route::get('/terms', fn () => Inertia::render('TermsPage'))->name('terms');

Route::redirect('/login', config('services.comordo.identity_url').'/login')->name('login');
Route::redirect('/signup', config('services.comordo.identity_url').'/register')->name('signup');
Route::redirect('/account', config('services.comordo.identity_url').'/dashboard')->name('account');
