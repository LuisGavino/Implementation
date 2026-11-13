
<?php

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\DestinationsController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\Packages\PostController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TagsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\WishlistController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [WelcomeController::class, 'index']);

Route::get('packages/destinations/{destination}', [PostController::class, 'show'])
    ->name('desti.show');

Auth::routes(['verify' => true]);

Route::get('/about', [WelcomeController::class, 'about'])->name('about');
Route::get('/packages', [WelcomeController::class, 'packages'])->name('packages');
Route::get('/news', [WelcomeController::class, 'blog'])->name('blog');
Route::get('/contact', [WelcomeController::class, 'contact'])->name('contact');
Route::get('/Bali', [WelcomeController::class, 'Bali'])->name('Bali');
Route::get('/cart', [WelcomeController::class, 'cart'])->name('cart');
Route::get('/checkout', [WelcomeController::class, 'checkout'])->name('checkout');
Route::get('/Checkout', [CheckoutController::class, 'checkout'])->name('checkout.store');

Route::post('/contact', [ContactUsController::class, 'ContactUs'])
    ->name('contact.store');

Route::get('/stripe', [WelcomeController::class, 'stripe'])->name('stripe');

Route::delete('/cart/{id}/remove', [CartController::class, 'removeItem'])
    ->name('cart.remove');

Route::get('/send-email', [MailController::class, 'sendEmail']);

Route::get('/sitemap.xml', [SitemapController::class, 'index'])
    ->name('sitemap');

/*
|--------------------------------------------------------------------------
| Authenticated User Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/home', [HomeController::class, 'index'])
        ->name('home');

    // Reviews
    Route::post('destinations/{destination}/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');

    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('reviews.destroy');

    // Wishlist
    Route::get('wishlist', [WishlistController::class, 'index'])
        ->name('wishlist.index');

    Route::post('wishlist/{destination}/toggle', [WishlistController::class, 'toggle'])
        ->name('wishlist.toggle');

    Route::post('wishlist/{destination}', [WishlistController::class, 'store'])
        ->name('wishlist.store');

    Route::delete('wishlist/{destination}', [WishlistController::class, 'destroy'])
        ->name('wishlist.destroy');
});

/*
|--------------------------------------------------------------------------
| Admin-Only Management Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->group(function () {

    // Categories management
    Route::resource('categories', CategoriesController::class);

    // Destinations management
    Route::resource('destinations', DestinationsController::class);

    Route::get('trashed-destinations', [DestinationsController::class, 'trashed'])
        ->name('trashed-destinations.index');

    Route::put('restore-destinations/{destinations}', [DestinationsController::class, 'restore'])
        ->name('restore-destinations');

    // Tags management
    Route::resource('tags', TagsController::class);

    // Blog management
    Route::resource('blog', BlogController::class);

    // User management
    Route::get('users/profile', [UsersController::class, 'edit'])
        ->name('users.edit-profile');

    Route::put('users/profile', [UsersController::class, 'update'])
        ->name('users.update-profile');

    Route::get('users', [UsersController::class, 'index'])
        ->name('users.index');

    Route::post('users/{user}/make-admin', [UsersController::class, 'makeAdmin'])
        ->name('users.make-admin');
});

/*
|--------------------------------------------------------------------------
| Email Verification Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['isVerified']], function () {

    Route::get('email-verification/error', [RegisterController::class, 'getVerificationError'])
        ->name('email-verification.error');

    Route::get('email-verification/check/{token}', [RegisterController::class, 'getVerification'])
        ->name('email-verification.check');
});
