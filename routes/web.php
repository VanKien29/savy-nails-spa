<?php

use App\Http\Controllers\LeadController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SalonInquiryController;
use App\Http\Controllers\SeoController;
use App\Models\Page;
use App\Services\PageRenderer;
use Illuminate\Support\Facades\Route;

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots']);
Route::post('/contact', [LeadController::class, 'store'])->middleware('throttle:leads')->name('leads.store');
Route::post('/inquiries/contact', [SalonInquiryController::class, 'contact'])->middleware('throttle:leads')->name('salon.contact');
Route::post('/inquiries/booking', [SalonInquiryController::class, 'booking'])->middleware('throttle:leads')->name('salon.booking');
Route::get('/booking', fn () => abort(404));
Route::get('/starter/preview/{page}', [PageController::class, 'preview'])->middleware(['auth', 'signed'])->name('pages.preview');
Route::get('/starter/showcase', function () {
    abort_unless(app()->environment('local') && config('starter.showcase'), 404);
    $page = Page::published()->where('is_home', true)->firstOrFail();

    return app(PageRenderer::class)->render($page, true)->with('showcase', true);
});
Route::get('/{slug?}', [PageController::class, 'show'])->where('slug', '(?!admin(?:/|$)|livewire(?:/|$)|starter(?:/|$)).*')->name('page');
