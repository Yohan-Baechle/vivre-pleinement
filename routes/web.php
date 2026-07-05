<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CourseCheckoutController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Student\AccountController as StudentAccountController;
use App\Http\Controllers\Student\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Student\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Student\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Student\Auth\NewPasswordController;
use App\Http\Controllers\Student\Auth\PasswordResetLinkController;
use App\Http\Controllers\Student\Auth\RegisteredStudentController;
use App\Http\Controllers\Student\Auth\VerifyEmailController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\YoutubeOAuthController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home.index')->name('home');

// Installation OAuth YouTube (sous-titres) — accessible uniquement si les
// identifiants OAuth sont configurés ; sert une seule fois à l'autorisation.
Route::get('/youtube/oauth/redirect', [YoutubeOAuthController::class, 'redirect'])->name('youtube.oauth.redirect');
Route::get('/youtube/oauth/callback', [YoutubeOAuthController::class, 'callback'])->name('youtube.oauth.callback');

Route::view('/a-propos', 'about.index')->name('about');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
Route::get('/contact/merci', [ContactController::class, 'thanks'])->name('contact.thanks');

Route::post('/newsletter', [NewsletterController::class, 'store'])->name('newsletter.store');
Route::view('/inscription-confirmee', 'newsletter.confirmed')->name('newsletter.confirmed');

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

/*
|--------------------------------------------------------------------------
| Espace formation (e-learning)
|--------------------------------------------------------------------------
*/

// Authentification élève (guard « student »)
Route::middleware('guest:student')->group(function () {
    Route::get('/espace-formation/inscription', [RegisteredStudentController::class, 'create'])->name('student.register');
    Route::post('/espace-formation/inscription', [RegisteredStudentController::class, 'store'])->name('student.register.store');
    Route::get('/espace-formation/connexion', [AuthenticatedSessionController::class, 'create'])->name('student.login');
    Route::post('/espace-formation/connexion', [AuthenticatedSessionController::class, 'store'])->name('student.login.store');
    Route::get('/espace-formation/mot-de-passe-oublie', [PasswordResetLinkController::class, 'create'])->name('student.password.request');
    Route::post('/espace-formation/mot-de-passe-oublie', [PasswordResetLinkController::class, 'store'])->name('student.password.email');
    Route::get('/espace-formation/reinitialiser/{token}', [NewPasswordController::class, 'create'])->name('student.password.reset');
    Route::post('/espace-formation/reinitialiser', [NewPasswordController::class, 'store'])->name('student.password.update');
});

Route::post('/espace-formation/deconnexion', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth:student')
    ->name('student.logout');

// Catalogue et page de vente (public)
Route::get('/formations', [CourseController::class, 'index'])->name('courses.index');

// Achat (élève connecté) — déclaré avant la route catch-all {course:slug}
Route::middleware('auth:student')->group(function () {
    Route::post('/formations/{course:slug}/acheter', [CourseCheckoutController::class, 'start'])->name('courses.checkout.start');
    Route::get('/formations/{course:slug}/paiement', [CourseCheckoutController::class, 'pay'])->name('courses.checkout.pay');
    Route::get('/formations/{course:slug}/merci', [CourseCheckoutController::class, 'success'])->name('courses.checkout.success');
});

Route::get('/formations/{course:slug}', [CourseController::class, 'show'])->name('courses.show');

// Espace élève (formations achetées)
Route::prefix('espace-formation')->name('student.')->middleware('auth:student')->group(function () {
    // Vérification d'e-mail (accessible aux comptes non encore vérifiés)
    Route::get('/verification-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/verification-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('/verification-email/renvoyer', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Gestion du compte (accessible sans vérification d'e-mail)
    Route::get('/compte', [StudentAccountController::class, 'edit'])->name('account.edit');
    Route::patch('/compte/profil', [StudentAccountController::class, 'updateProfile'])->name('account.profile');
    Route::put('/compte/mot-de-passe', [StudentAccountController::class, 'updatePassword'])->name('account.password');
    Route::delete('/compte', [StudentDashboardController::class, 'destroy'])->name('account.destroy');

    // Contenu réservé aux comptes vérifiés
    Route::middleware('verified:student.verification.notice')->group(function () {
        Route::get('/', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/{course:slug}', [StudentCourseController::class, 'show'])->middleware('enrolled')->name('course');
        Route::get('/{course:slug}/lecons/{lesson:slug}', [StudentCourseController::class, 'lesson'])->middleware('enrolled')->name('lesson');
    });
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
