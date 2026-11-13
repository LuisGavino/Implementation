<?php

use App\Http\Controllers\AdminBookingController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BookingController;
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

Route::get('/sitemap.xml', [SitemapController::class, 'index'])
    ->name('sitemap');

/*
|--------------------------------------------------------------------------
| Role-Based Access Control (RBAC)
|--------------------------------------------------------------------------
| Two roles only: 'admin' and 'customer'. Access is enforced on the backend
| with the dynamic RoleMiddleware (alias 'role'), e.g. ->middleware('role:admin').
| Unauthenticated visitors are redirected to /login by the 'auth' middleware;
| authenticated users with the wrong role receive 403 Forbidden.
*/

/*
|--------------------------------------------------------------------------
| Any Authenticated User (post-login dispatcher)
|--------------------------------------------------------------------------
| /home sends admins to the dashboard and customers to the public site.
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])
        ->name('home');
});

/*
|--------------------------------------------------------------------------
| Customer Routes  (role: customer; admins are also allowed)
|--------------------------------------------------------------------------
| Ownership of bookings is verified by BookingPolicy; wishlist actions are
| always scoped to the logged-in user (auth()->user()->wishlist()).
*/

Route::middleware(['auth', 'role:customer,admin'])->group(function () {

    // Reviews
    Route::post('destinations/{destination}/reviews', [ReviewController::class, 'store'])
        ->name('reviews.store');

    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])
        ->name('reviews.destroy');

    // Wishlist (own wishlist only)
    Route::get('wishlist', [WishlistController::class, 'index'])
        ->name('wishlist.index');

    Route::post('wishlist/{destination}/toggle', [WishlistController::class, 'toggle'])
        ->name('wishlist.toggle');

    Route::post('wishlist/{destination}', [WishlistController::class, 'store'])
        ->name('wishlist.store');

    Route::delete('wishlist/{destination}', [WishlistController::class, 'destroy'])
        ->name('wishlist.destroy');

    // Bookings (own bookings only; ownership enforced by BookingPolicy)
    Route::get('bookings', [BookingController::class, 'index'])
        ->name('bookings.index');

    Route::post('bookings', [BookingController::class, 'store'])
        ->name('bookings.store');

    Route::get('bookings/{booking}', [BookingController::class, 'show'])
        ->name('bookings.show');

    Route::patch('bookings/{booking}/cancel', [BookingController::class, 'cancel'])
        ->name('bookings.cancel');

    // Cart item removal (previously open to anonymous visitors)
    Route::delete('/cart/{id}/remove', [CartController::class, 'removeItem'])
        ->name('cart.remove');
});

/*
|--------------------------------------------------------------------------
| Admin-Only Management Routes  (role: admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {

    // Administrator dashboard
    Route::get('admin/dashboard', [HomeController::class, 'dashboard'])
        ->name('admin.dashboard');

    // Booking management (all customers' bookings)
    Route::get('admin/bookings', [AdminBookingController::class, 'index'])
        ->name('admin.bookings.index');

    Route::patch('admin/bookings/{booking}', [AdminBookingController::class, 'update'])
        ->name('admin.bookings.update');

    Route::delete('admin/bookings/{booking}', [AdminBookingController::class, 'destroy'])
        ->name('admin.bookings.destroy');

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

    Route::post('users/{user}/make-customer', [UsersController::class, 'makeCustomer'])
        ->name('users.make-customer');

    // Demo mail route (was public; sends mail so it is administrator-only)
    Route::get('/send-email', [MailController::class, 'sendEmail']);
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
