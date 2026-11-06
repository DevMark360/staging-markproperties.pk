<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LaunchAlertController;
use App\Http\Controllers\LuxeController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\SilentHoneypot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::post('/enquiries', EnquiryController::class)
    ->middleware(['throttle:5,1', SilentHoneypot::class])
    ->name('enquiries.store');

Route::post('/launch-alerts', LaunchAlertController::class)
    ->middleware(['throttle:5,1', SilentHoneypot::class])
    ->name('alerts.store');

// Pages below render a placeholder until their section is built.
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/luxury', LuxeController::class)->name('luxe');
// Renamed from /luxe: old links (and their ?collection=) move to the new address for good
Route::get('/luxe', fn (Request $request) => redirect()->route('luxe', $request->query(), 301));
Route::permanentRedirect('/services', '/about'); // services are no longer offered
Route::get('/developers', [DeveloperController::class, 'index'])->name('developers.index');
Route::get('/developers/{slug}', [DeveloperController::class, 'show'])->name('developers.show');
Route::get('/partner-with-us', [PartnerController::class, 'show'])->name('partner');
Route::post('/partner-with-us', [PartnerController::class, 'store'])->middleware(['throttle:5,1', SilentHoneypot::class])->name('partner.store');
Route::view('/buyers-guide', 'pages.buyers-guide')->name('guide');
Route::permanentRedirect('/news-media', '/blog'); // no press coverage yet; old links land on the blog
Route::get('/blog', [BlogController::class, 'index'])->name('blog');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::view('/faqs', 'pages.faqs')->name('faqs');
Route::view('/about', 'pages.about')->name('about');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware(['throttle:5,1', SilentHoneypot::class])->name('contact.store');
Route::view('/privacy', 'pages.privacy')->name('privacy');
Route::view('/terms', 'pages.terms')->name('terms');

// Search engines: every page (C5); robots.txt points to it with the site's real address
Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');
