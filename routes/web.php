<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Marketing site (public)
|--------------------------------------------------------------------------
*/

Route::controller(HomeController::class)->group(function () {
    // Pages
    Route::get('/', 'index')->name('home');
    Route::get('/merchants', 'merchants')->name('merchants');
    Route::get('/personal', 'personal')->name('personal');
    Route::get('/trade', 'trade')->name('trade');
    Route::get('/developers', 'developers')->name('developers');
    Route::get('/company', 'company')->name('company');
    Route::get('/contact', 'contact')->name('contact');

    // Form submissions (public — do NOT require auth)
    Route::post('/personal/signup', 'storePersonalSignup')->name('personal.signup');
    Route::post('/merchants/signup', 'storeMerchantSignup')->name('merchants.signup');
    Route::post('/trade/apply', 'storeTradeApplication')->name('trade.apply');
    Route::post('/developers/apply', 'storeDeveloperApplication')->name('developers.apply');
    Route::post('/contact/send', 'storeContact')->name('contact.send');
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Single dashboard — RBAC handled inside via Spatie
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Waiting list (user-side)
    |--------------------------------------------------------------------------
    */

    Route::get('/waiting-list', [UserController::class, 'show'])->name('waiting_list.show');

    /*
    |--------------------------------------------------------------------------
    | Onboarding
    |--------------------------------------------------------------------------
    */

    Route::prefix('onboarding')->name('onboarding.')->group(function () {
        Route::get('/', [OnboardingController::class, 'welcome'])->name('welcome');
        Route::get('/choose-lane', [OnboardingController::class, 'chooseLane'])->name('choose_lane');
        Route::post('/choose-lane', [OnboardingController::class, 'storeLane'])->name('store_lane');

        Route::get('/merchant', [OnboardingController::class, 'merchant'])->name('merchant');
        Route::post('/merchant', [OnboardingController::class, 'storeMerchant'])->name('merchant.store');

        Route::get('/business', [OnboardingController::class, 'business'])->name('business');
        Route::post('/business', [OnboardingController::class, 'storeBusiness'])->name('business.store');

        Route::get('/developer', [OnboardingController::class, 'developer'])->name('developer');
        Route::post('/developer', [OnboardingController::class, 'storeDeveloper'])->name('developer.store');

        Route::get('/individual', [OnboardingController::class, 'individual'])->name('individual');
        Route::post('/individual', [OnboardingController::class, 'storeIndividual'])->name('individual.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/profile/avatar', [ProfileController::class, 'deleteAvatar'])->name('profile.delete-avatar');
    Route::put('/profile/address', [ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::get('/profile/data', [ProfileController::class, 'getUserData'])->name('profile.data');

    /*
    |--------------------------------------------------------------------------
    | Wallet
    |--------------------------------------------------------------------------
    */

    Route::resource('wallet', WalletController::class);

    /*
    |--------------------------------------------------------------------------
    | Users — admin-only
    |--------------------------------------------------------------------------
    */

    Route::resource('users', UserController::class);

    /*
    |--------------------------------------------------------------------------
    | Admin — waiting list management
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin|super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/waiting-list', [UserController::class, 'index'])->name('waiting_list');
        Route::post('/waiting-list/{user}/invite', [UserController::class, 'invite'])->name('waiting_list.invite');
    });
});

/*
|--------------------------------------------------------------------------
| Authentication (Breeze/Fortify)
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';              