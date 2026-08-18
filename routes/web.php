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

Route::redirect('/login', 'https://identity.comordo.com/login')->name('login');
Route::redirect('/signup', 'https://identity.comordo.com/register')->name('signup');
Route::redirect('/account', 'https://identity.comordo.com/dashboard')->name('account');
