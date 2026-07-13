<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/donate', [PageController::class, 'donate'])->name('donate');
Route::get('/sponsorship', [PageController::class, 'sponsorship'])->name('sponsorship');
Route::get('/sponsor-checkout', [PageController::class, 'sponsorCheckout'])->name('sponsor-checkout');
Route::get('/blog', [PageController::class, 'blog'])->name('blog');
Route::get('/blog/{id}', [PageController::class, 'blogPost'])->name('blog.show');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/programs/{slug}', [PageController::class, 'program'])->name('programs.show');
Route::get('/programs/{slug}/participate', [PageController::class, 'programCheckout'])->name('programs.participate');

Route::post('/locale', [PageController::class, 'setLocale'])->name('locale.set');

// Page 404 rendue par Inertia pour toute route web inconnue (hors admin/api)
Route::fallback(function () {
    return Inertia::render('NotFound')->toResponse(request())->setStatusCode(404);
});
