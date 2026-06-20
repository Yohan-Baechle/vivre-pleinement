<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\VideoController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home.index')->name('home');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
Route::get('/contact/merci', [ContactController::class, 'thanks'])->name('contact.thanks');

Route::prefix('reservation')->name('booking.')->controller(BookingController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('confirmation/{appointment:reference}', 'confirmation')->name('confirmation');
    Route::get('confirmation/{appointment:reference}/agenda.ics', 'ics')->name('ics');
    Route::get('paiement-annule/{appointment:reference}', 'paymentCancelled')->name('paymentCancelled');
    Route::get('payer/{appointment:token}', 'pay')->name('pay');
    Route::get('gerer/{appointment:token}', 'manage')->name('manage');
    Route::post('gerer/{appointment:token}/annuler', 'cancel')->name('cancel');
    Route::get('gerer/{appointment:token}/reprogrammer', 'reschedule')->name('reschedule');
    Route::get('{service:slug}', 'show')->name('show');
});

Route::get('/livre', [BookController::class, 'show'])->name('book.show');
Route::get('/livre/commande/{offer}', [BookController::class, 'checkout'])
    ->name('book.checkout')
    ->where('offer', 'livre|livre-coaching');

Route::prefix('videos')->name('videos.')->controller(VideoController::class)->group(function () {
    Route::get('/', 'index')->name('index');
    Route::get('{slug}', 'show')->name('show')->where('slug', '[A-Za-z0-9_-]+');
});

Route::view('/mentions-legales', 'legal.mentions')->name('legal.mentions');
Route::view('/politique-de-confidentialite', 'legal.privacy')->name('legal.privacy');
Route::view('/politique-cookies', 'legal.cookies')->name('legal.cookies');
Route::view('/conditions-generales-de-vente', 'legal.cgv')->name('legal.cgv');

Route::prefix('blog')->name('blog.')->group(function () {
    Route::controller(PostController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('rss', 'rss')->name('rss');
        Route::get('categorie/{slug}', 'byCategory')->name('category');
        Route::get('tag/{slug}', 'byTag')->name('tag');
        Route::get('{slug}', 'show')->name('show')->where('slug', '(?!rss|categorie|tag$).+');
    });

    Route::post('{slug}/commentaire', [CommentController::class, 'store'])
        ->name('comments.store')
        ->where('slug', '(?!rss|categorie|tag$).+');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/sitemap-videos.xml', [SitemapController::class, 'videos'])->name('sitemap.videos');
